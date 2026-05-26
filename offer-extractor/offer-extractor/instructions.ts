import { By, until, WebElement } from "selenium-webdriver";
import { Browser } from "./browser.ts";
import { Offer, OfferBuilder } from "./models/offer.ts";
import { ProductBuilder } from "./models/product.ts";
import { Category } from "./models/category.ts";
import { Provider } from "./models/provider.ts";

const DEFAULT_TIMEOUT = 10000;

export interface Instruction<T> {
    accept(browser: Browser): Promise<T>;
}


export class GotoURL implements Instruction<void> {
    url: string;

    constructor(url: string) {
        this.url = url;
    }

    async accept(browser: Browser): Promise<void> {
        await browser.driver.get(this.url);
    }
}

export class OpenTab implements Instruction<string> {
    async accept(browser: Browser): Promise<string> {
        const original = await browser.driver.getWindowHandle();
        await browser.driver.switchTo().newWindow('tab');
        return original;
    }
}

export class CloseTab implements Instruction<void> {
    previous: string;

    constructor(previous: string) {
        this.previous = previous;
    }

    async accept(browser: Browser): Promise<void> {
        await browser.driver.close()
        await browser.driver.switchTo().window(this.previous)
    }
}

type Processing<T> = (str: string) => T;

export function identity(str: string): string {
    return str;
}


export class Scrape<T = string> implements Instruction<T[]> {
    selector: string;
    attribute: string;
    processing: Processing<T>;

    constructor(
        selector: string,
        attribute: string,
        processing: Processing<T>,
    ) {
        this.selector = selector;
        this.attribute = attribute;
        this.processing = processing;
    }


    async accept(browser: Browser): Promise<T[]> {
        let elements: WebElement[];
        try {
            elements = await browser.driver.wait(
                until.elementsLocated(By.css(this.selector)),
                DEFAULT_TIMEOUT,
            );
        } catch (e) {
            console.log("selector didnt match:", this.selector)
            elements = [];
        }


        let data = elements
            .map(async (element) => await element.getAttribute(this.attribute))
            .filter(data => data != null)
            .map(async datum => this.processing(await datum));

        return Promise.all(data);
    }
}


export class ScrapeProductInfo implements Instruction<Map<string, string>> {
    scrapeKeys: Scrape<string>;
    scrapeValues: Scrape<string>;

    constructor(scrapeKeys: Scrape<string>, scrapeValues: Scrape<string>) {
        this.scrapeKeys = scrapeKeys;
        this.scrapeValues = scrapeValues;
    }

    async accept(browser: Browser): Promise<Map<string, string>> {
        const keys = await this.scrapeKeys.accept(browser);
        const values = await this.scrapeValues.accept(browser);
        const productInfo = new Map();

        keys.forEach((key, i) => productInfo.set(key, values[i]));
        return productInfo;
    }
}


export class ScrapeOffers {
    scrapePrices: Scrape<number>;
    scrapeNames: Scrape<string>;
    scrapeLinks: Scrape<string>;
    scrapeImages: Scrape<string>;
    scrapeReferences: Scrape<string>;
    scrapeProductInfo: ScrapeProductInfo;

    constructor(
        scrapePrices: Scrape<number>,
        scrapeNames: Scrape<string>,
        scrapeLinks: Scrape<string>,
        scrapeImages: Scrape<string>,
        scrapeReferences: Scrape<string>,
        scrapeProductInfo: ScrapeProductInfo,
    ) {
        this.scrapePrices = scrapePrices;
        this.scrapeNames = scrapeNames;
        this.scrapeLinks = scrapeLinks;
        this.scrapeImages = scrapeImages;
        this.scrapeReferences = scrapeReferences;
        this.scrapeProductInfo = scrapeProductInfo;
    }

    async accept(browser: Browser): Promise<OfferBuilder[]> {
        const prices = await browser.execute(this.scrapePrices)
        const names = await browser.execute(this.scrapeNames)
        const links = await browser.execute(this.scrapeLinks)
        const images = await browser.execute(this.scrapeImages)
        const references = await browser.execute(this.scrapeReferences)

        const offers: OfferBuilder[] = [];
        for (let i = 0; i < names.length; i++) {
            const productInfo = await browser.execute(
                new GetProductInfo(links[i]!, this.scrapeProductInfo)
            );

            const product = new ProductBuilder(references[i]!, names[i]!);
            product.setImage(images[i]!);
            product.setInfo(productInfo);

            const offer = new OfferBuilder(product, prices[i]!, links[i]!)

            offers.push(offer);

        }

        return offers
    }
}

export class ScrapeCategory implements Instruction<OfferBuilder[]> {
    category: Category;
    scrapeOffers: ScrapeOffers;

    constructor(category: Category, scrapeOffers: ScrapeOffers) {
        this.category = category
        this.scrapeOffers = scrapeOffers
    }

    async accept(browser: Browser): Promise<OfferBuilder[]> {
        const offers = await browser.execute(this.scrapeOffers);
        offers.forEach(offer => offer.product.setCategory(this.category));

        return offers
    }
}


export class GetCategory implements Instruction<OfferBuilder[]> {
    url: string;
    scrapeCategory: ScrapeCategory;


    constructor(url: string, scrapeCategory: ScrapeCategory) {
        this.url = url
        this.scrapeCategory = scrapeCategory
    }

    async accept(browser: Browser): Promise<OfferBuilder[]> {
        await browser.execute(new GotoURL(this.url))
        const offers = await browser.execute(this.scrapeCategory)
        return offers
    }
}


export class ScrapeProvider implements Instruction<Offer[]> {
    provider: Provider;
    getCategories: GetCategory[];


    constructor(provider: Provider, ...getCategories: GetCategory[]) {
        this.provider = provider
        this.getCategories = getCategories
    }

    async accept(browser: Browser): Promise<Offer[]> {
        const offers: Offer[] = [];


        for (const getCategory of this.getCategories) {
            const categoryOffers = await browser.execute(getCategory);
            for (const categoryOffer of categoryOffers) {
                categoryOffer.setProvider(this.provider);
                offers.push(categoryOffer.build());
            }
        }

        return offers;
    }


}

export class GetOffers implements Instruction<OfferBuilder[]> {
    url: string;
    scrapeInstruction: ScrapeOffers;

    constructor(url: string, scrapeInstruction: ScrapeOffers) {
        this.url = url
        this.scrapeInstruction = scrapeInstruction
    }

    async accept(browser: Browser): Promise<OfferBuilder[]> {
        await browser.execute(new GotoURL(this.url))
        return await browser.execute(this.scrapeInstruction)
    }
}

export class GetProductInfo implements Instruction<Map<string, string>> {
    url: string;
    scrapeProductInfo: ScrapeProductInfo;

    constructor(url: string, scrapeProductInfo: ScrapeProductInfo) {
        this.url = url
        this.scrapeProductInfo = scrapeProductInfo
    }

    async accept(browser: Browser): Promise<Map<string, string>> {
        const original = await browser.execute(new OpenTab())
        await browser.execute(new GotoURL(this.url))
        const info = await browser.execute(this.scrapeProductInfo)
        await browser.execute(new CloseTab(original))
        return info;
    }
}

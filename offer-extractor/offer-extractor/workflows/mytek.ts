import { GetCategory, identity, Scrape, ScrapeCategory, ScrapeOffers, ScrapeProductInfo, ScrapeProvider } from "../instructions.ts"
import { Category } from "../models/category.ts"
import { Provider } from "../models/provider.ts"

const scrape_product_info = new ScrapeProductInfo(
    new Scrape(".data.table th.col.label", "innerHTML", identity),
    new Scrape(".data.table td.col.data", "innerHTML", identity),
)


function processReference(price: string): string {
    return price.slice("[".length).slice(0, -"]".length)
}


function processPrice(price: string): number {
    price = price.trim().slice(0, -" DT".length).replace(",", ".")
    price = price.replace("\u202f", "")

    return Number.parseFloat(price)
}


const scrapeOffers = new ScrapeOffers(
    new Scrape(
        ".product-container span.final-price", "innerHTML", processPrice
    ),
    new Scrape(
        ".product-container a.product-item-link", "innerHTML", (str: string) => str.trim()
    ),
    new Scrape(
        ".product-container a.product-item-link",
        "href",
        identity
    ),
    new Scrape(".product-container .product-item-photo img", "src", identity),
    new Scrape(".product-container .sku", "innerHTML", processReference),
    scrape_product_info,
)

const provider = new Provider(
    "mytek",
    "https://mk-media.mytek.tn/media/logo/stores/1/LOGO-MYTEK-176PX-INVERSE.png",
    "https://www.mytek.tn/",
)

const categories = new Map([
    ["Memory", "https://www.mytek.tn/informatique/composants-informatique/barrettes-memoire.html"],
    ["GPU", "https://www.mytek.tn/informatique/composants-informatique/carte-graphique.html"]
])



const getCategories: GetCategory[] = []


categories.forEach((url, categoryName) => {
    const category = new Category(categoryName);
    getCategories.push(new GetCategory(url, new ScrapeCategory(category, scrapeOffers)));
})


export const scrapeProvider = new ScrapeProvider(provider, ...getCategories);

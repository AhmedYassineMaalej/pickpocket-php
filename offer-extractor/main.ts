import { Builder, WebDriver } from "selenium-webdriver";
import { Browser } from "./browser.ts";
import { scrapeProvider } from "./workflows/mytek.ts";
import { OfferRepository } from "./repository/offer.ts";
import { pool } from "./repository/utils.ts";


const browserName = process.argv[2] || 'firefox';

let driver: WebDriver = await new Builder().forBrowser(browserName).build();
let browser = new Browser(driver);

const offers = await browser.execute(scrapeProvider);

const offerRepository = new OfferRepository();

for (const offer of offers) {
    await offerRepository.add(offer);
}

pool.end()

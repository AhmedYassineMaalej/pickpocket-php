from selenium.webdriver.chrome.webdriver import WebDriver as Chrome
from selenium.webdriver.firefox.webdriver import WebDriver as Firefox

from repository.product_offer_repository import ProductOfferRepository
from selenium.webdriver.firefox.service import Service
from browser import Browser

from workflows.spacenet import scrape_provider

import argparse
import os


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "-b", "--browser", choices=["firefox", "chrome"], default="firefox"
    )

    args = parser.parse_args()
    service = Service(os.getenv("GECKODRIVER_PATH"))
    match args.browser:
        case "chrome":
            driver = Chrome()
        case "firefox":
            driver = Firefox(service=service)
        case _:
            print("unsupported browser:", args.browser)
            return

    browser = Browser(driver)
    offers = browser.execute(scrape_provider)
    browser.quit()

    for offer in offers:
        ProductOfferRepository.add(offer)


if __name__ == "__main__":
    main()

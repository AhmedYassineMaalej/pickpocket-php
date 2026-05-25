from models.category import Category
from abc import ABC, abstractmethod
from typing import Callable

from selenium.common import TimeoutException
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.window import WindowTypes
from models.provider import Provider

from models.product import ProductBuilder
from models.offer import Offer, OfferBuilder

from browser import Browser

DEFAULT_TIMEOUT = 10


class BrowserInstruction[T](ABC):
    @abstractmethod
    def accept(self, browser: Browser) -> T:
        pass


class GotoURL(BrowserInstruction[None]):
    def __init__(self, url: str):
        self.url = url

    def accept(self, browser: Browser) -> None:
        browser.driver.get(self.url)


class OpenTab(BrowserInstruction[str]):
    def accept(self, browser: Browser) -> str:
        original = browser.driver.current_window_handle
        browser.driver.switch_to.new_window(WindowTypes.TAB)
        return original


class CloseTab(BrowserInstruction[None]):
    def __init__(self, previous: str) -> None:
        self.prev = previous

    def accept(self, browser: Browser) -> None:
        browser.driver.close()
        browser.driver.switch_to.window(self.prev)


type Processing[T] = Callable[[str], T]


def identity(T):
    return T


class Scrape[T = str](BrowserInstruction[list[T]]):
    def __init__(
        self,
        selector: str,
        attribute_name: str,
        processing: Processing[T] = identity,
    ):
        self.selector = selector
        self.attribute_name = attribute_name
        self.processing = processing

    def accept(self, browser: Browser) -> list[T]:
        try:
            elements = WebDriverWait(browser.driver, DEFAULT_TIMEOUT).until(
                EC.presence_of_all_elements_located((By.CSS_SELECTOR, self.selector))
            )
        except TimeoutException:
            print("selector didnt match:", self.selector)
            return []

        data: list[str] = []

        for element in elements:
            attr = element.get_attribute(self.attribute_name)
            if attr is None:
                continue
            data.append(attr)

        return list(map(self.processing, data))


class ScrapeProductInfo(BrowserInstruction):
    def __init__(
        self,
        scrape_keys: Scrape[str],
        scrape_values: Scrape[str],
    ) -> None:
        self.scrape_keys = scrape_keys
        self.scrape_values = scrape_values

    def accept(self, browser: Browser) -> dict[str, str]:
        keys = self.scrape_keys.accept(browser)
        values = self.scrape_values.accept(browser)

        return dict(zip(keys, values))


class ScrapeOffers(Scrape[OfferBuilder]):
    def __init__(
        self,
        scrape_prices: Scrape[float],
        scrape_names: Scrape[str],
        scrape_links: Scrape[str],
        scrape_images: Scrape[str],
        scrape_references: Scrape[str],
        scrape_product_info: ScrapeProductInfo,
    ) -> None:
        self.scrape_prices = scrape_prices
        self.scrape_names = scrape_names
        self.scrape_links = scrape_links
        self.scrape_images = scrape_images
        self.scrape_references = scrape_references
        self.scrape_product_info = scrape_product_info

    def accept(self, browser: Browser) -> list[OfferBuilder]:
        prices = browser.execute(self.scrape_prices)
        names = browser.execute(self.scrape_names)
        links = browser.execute(self.scrape_links)
        images = browser.execute(self.scrape_images)
        references = browser.execute(self.scrape_references)

        offers = []
        for price, name, link, image, ref in zip(
            prices, names, links, images, references
        ):
            product_info = browser.execute(
                GetProductInfo(link, self.scrape_product_info)
            )
            product = ProductBuilder(ref, name)
            product.set_image(image)
            product.set_info(product_info)

            offer = OfferBuilder(product, price, link)

            offers.append(offer)

        return offers


class ScrapeCategory(Scrape[OfferBuilder]):
    def __init__(self, category: Category, scrape_offers: ScrapeOffers) -> None:
        self.category = category
        self.scrape_offers = scrape_offers

    def accept(self, browser: Browser) -> list[OfferBuilder]:
        offers = browser.execute(self.scrape_offers)
        for offer in offers:
            offer.product.set_category(self.category)

        return offers


class GetCategory(BrowserInstruction[list[OfferBuilder]]):
    def __init__(self, url: str, scrape_category: ScrapeCategory) -> None:
        self.url = url
        self.scrape_category = scrape_category

    def accept(self, browser: Browser) -> list[OfferBuilder]:
        browser.execute(GotoURL(self.url))
        offers = browser.execute(self.scrape_category)
        return offers


class ScrapeProvider(Scrape[Offer]):
    def __init__(self, provider: Provider, *get_categories: GetCategory) -> None:
        self.provider = provider
        self.get_categories = get_categories

    def accept(self, browser: Browser) -> list[Offer]:
        offers = []
        for get_category in self.get_categories:
            category_offers = browser.execute(get_category)

            for offer in category_offers:
                offer.set_provider(self.provider)
                offers.append(offer.build())

        return offers


class GetOffers(BrowserInstruction):
    def __init__(self, url: str, scrape_instruction: ScrapeOffers) -> None:
        self.url = url
        self.scrape_instruction = scrape_instruction

    def accept(self, browser: Browser) -> list[OfferBuilder]:
        browser.execute(GotoURL(self.url))
        return browser.execute(self.scrape_instruction)


class GetProductInfo(BrowserInstruction):
    def __init__(
        self, product_url: str, scrape_instructions: ScrapeProductInfo
    ) -> None:
        self.url = product_url
        self.scrape_instruction = scrape_instructions

    def accept(self, browser: Browser) -> dict[str, str]:
        origibal = browser.execute(OpenTab())
        browser.execute(GotoURL(self.url))
        info = browser.execute(self.scrape_instruction)
        browser.execute(CloseTab(origibal))
        return info

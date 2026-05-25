from models.category import Category
from instructions import (
    GetCategory,
    ScrapeCategory,
    ScrapeOffers,
    ScrapeProductInfo,
    Scrape,
    ScrapeProvider,
)
from models.provider import Provider

scrape_product_info = ScrapeProductInfo(
    scrape_keys=Scrape(".data.table th.col.label", "innerHTML"),
    scrape_values=Scrape(".data.table td.col.data", "innerHTML"),
)


def process_reference(price: str) -> str:
    return price.removeprefix("[").removesuffix("]")


def process_price(price: str) -> float:
    price = price.strip().removesuffix(" DT").replace(",", ".")
    price = price.replace("\u202f", "")
    return float(price)


scrape_offers = ScrapeOffers(
    scrape_prices=Scrape(
        ".product-container span.final-price", "innerHTML", process_price
    ),
    scrape_names=Scrape(
        ".product-container a.product-item-link", "innerHTML", str.strip
    ),
    scrape_images=Scrape(".product-container .product-item-photo img", "src"),
    scrape_links=Scrape(
        ".product-container a.product-item-link",
        "href",
    ),
    scrape_references=Scrape(".product-container .sku", "innerHTML", process_reference),
    scrape_product_info=scrape_product_info,
)

provider = Provider(
    "mytek",
    "https://mk-media.mytek.tn/media/logo/stores/1/LOGO-MYTEK-176PX-INVERSE.png",
    "https://www.mytek.tn/",
)

URLS = {
    "Memory": "https://www.mytek.tn/informatique/composants-informatique/barrettes-memoire.html",
    "GPU": "https://www.mytek.tn/informatique/composants-informatique/carte-graphique.html",
}

get_categories = []
for category, url in URLS.items():
    category = Category(category)
    get_categories.append(GetCategory(url, ScrapeCategory(category, scrape_offers)))

scrape_provider = ScrapeProvider(
    provider,
    *get_categories
)

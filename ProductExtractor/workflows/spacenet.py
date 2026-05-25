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
    scrape_keys=Scrape("dt.name", "innerHTML"),
    scrape_values=Scrape("dd.value", "innerHTML"),
)

def process_reference(price: str) -> str:
    return price.strip()


def process_price(price: str) -> float:
    price = price.replace("&nbsp;", "").replace("\u202f", "")
    price = price.removesuffix("DT").replace(',', '.')
    return float(price)


scrape_offers = ScrapeOffers(
    scrape_prices=Scrape(
        ".products #box-product-grid span.price", "innerHTML", process_price
    ),
    scrape_names=Scrape(
        ".products #box-product-grid .product_name a", "innerHTML", str.strip
    ),
    scrape_images=Scrape(".products #box-product-grid .cover_image img", "src"),
    scrape_links=Scrape(
        ".products #box-product-grid .product_name a",
        "href",
    ),
    scrape_references=Scrape(".products #box-product-grid .product-reference span", "innerHTML", process_reference),
    scrape_product_info=scrape_product_info,
)

provider = Provider(
    "Spacenet",
    "https://spacenet.tn/52249-large_default/-abonnement-iptv-spacenet.jpg",
    "https://www.spacenet.tn/",
)

URLS = {
    "Memory": "https://spacenet.tn/25-barrette-memoire",
    "GPU": "https://spacenet.tn/397-cartes-graphiques",
}

get_categories = []
for category, url in URLS.items():
    category = Category(category)
    get_categories.append(GetCategory(url, ScrapeCategory(category, scrape_offers)))

scrape_provider = ScrapeProvider(
    provider,
    *get_categories
)

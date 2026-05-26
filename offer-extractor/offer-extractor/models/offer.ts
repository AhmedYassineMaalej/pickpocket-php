import { ProductBuilder, Product } from "./product.ts";
import { Provider } from "./provider.ts";


export class Offer {
    product: Product;
    provider: Provider;
    price: number;
    link: string;


    constructor(
        product: Product,
        provider: Provider,
        price: number,
        link: string,
    ) {
        this.product = product
        this.provider = provider
        this.price = price
        this.link = link
    }

    toString(): string {
        let res = "Offer(\n"
        Object.entries(this).forEach((key, val) => {
            res += `\t${key} = ${val}\n`;
        })
        res += ")"
        return res
    }
}

export class OfferBuilder {
    product: ProductBuilder;
    price: number;
    url: string;
    provider?: Provider;

    constructor(product: ProductBuilder, price: number, url: string) {
        this.product = product;
        this.price = price
        this.url = url
    }

    setProvider(provider: Provider) {
        this.provider = provider
    }

    build(): Offer {
        if (this.provider == null) {
            throw Error(
                "Tried to contruct Offer without setting provider attribute"
            )
        }


        const product = this.product.build()

        return new Offer(product, this.provider, this.price, this.url)
    }
}

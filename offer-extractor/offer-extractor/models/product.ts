import { Category } from "./category.ts";

export class Product {
    reference: string;
    name: string;
    image: string;
    info: Map<string, string>;
    category: Category;

    constructor(
        reference: string,
        name: string,
        image: string,
        info: Map<string, string>,
        category: Category,
    ) {
        this.reference = reference;
        this.name = name;
        this.image = image;
        this.category = category;
        this.info = info;
    }

    toString(): string {
        let res = "Product(\n"
        Object.entries(this).forEach((key, val) => {
            res += `\t${key} = ${val}\n`;
        })
        res += ")"
        return res
    }
}

export class ProductBuilder {
    reference: string;
    name: string;
    image: string | null;
    info: Map<string, string> | null;
    category: Category | null;

    constructor(reference: string, name: string) {
        this.reference = reference;
        this.name = name;
        this.image = null;
        this.category = null;
        this.info = null;
    }


    setImage(image: string) {
        this.image = image;
    }

    setCategory(category: Category) {
        this.category = category
    }

    setInfo(info: Map<string, string>) {
        this.info = info;
    }

    build(): Product {
        if (this.image === null) {
            throw Error(
                "Attempted to construct Product without setting image attribute"
            );
        }

        if (this.info === null) {
            throw Error(
                "Attempted to construct Product without setting info attribute"
            );
        }

        if (this.category === null) {
            throw Error(
                "Attempted to construct Product without setting category attribute"
            );
        }

        return new Product(
            this.reference,
            this.name,
            this.image,
            this.info,
            this.category
        );
    }
}

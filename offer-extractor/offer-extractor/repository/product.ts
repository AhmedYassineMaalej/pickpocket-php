import type { Product } from "../models/product.ts"
import { CategoryRepository } from "./category.ts";
import { ProductInfoRepository } from "./productInfo.ts";
import type { Repository } from "./repository.ts"
import { insert, select } from "./utils.ts";

export class ProductRepository implements Repository<Product> {
    tablename = "product";

    async add(item: Product): Promise<number> {
        const rows = await select(this.tablename, ["id"], { "reference": item.reference });

        if (rows.length > 0 && rows[0]) {
            return rows[0].id;
        }

        const categoryRepository = new CategoryRepository();
        const categoryId = await categoryRepository.add(item.category);
        const productId = await insert(
            this.tablename,
            {
                "reference": item.reference,
                "name": item.name,
                "image": item.image,
                "category_id": categoryId,
            },
        )

        const productInfoRepository = new ProductInfoRepository();

        for (const [key, value] of item.info) {
            await productInfoRepository.add({
                "productId": productId,
                "key": key,
                "value": value,
            });
        }

        return productId;
    }
}

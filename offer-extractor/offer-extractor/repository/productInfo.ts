import type { Repository } from "./repository.ts";
import { insert, select } from "./utils.ts";


interface ProductInfo {
    productId: number;
    key: string;
    value: string;
}

export class ProductInfoRepository implements Repository<ProductInfo> {
    tablename = "product_info";

    async add(item: ProductInfo): Promise<number> {
        const rows = await select(
            this.tablename,
            ["`key`"],
            { "product_id": item.productId, "`key`": item.key }
        );

        if (rows.length > 0 && rows[0]) {
            return rows[0].id;
        }

        return insert(
            this.tablename,
            {
                "product_id": item.productId,
                "`key`": item.key,
                "value": item.value,
            },
        )

    }
}


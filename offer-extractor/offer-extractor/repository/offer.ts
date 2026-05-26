
import type { Offer } from "../models/offer.ts";
import { ProductRepository } from "./product.ts";
import { ProviderRepository } from "./provider.ts";
import type { Repository } from "./repository.ts";
import { insert, select } from "./utils.ts";

export class OfferRepository implements Repository<Offer> {
    tablename = "offer";

    async add(item: Offer): Promise<number> {
        const providerRepository = new ProviderRepository();
        const productRepository = new ProductRepository();

        const providerId = await providerRepository.add(item.provider);
        const productId = await productRepository.add(item.product);

        const rows = await select(
            this.tablename,
            ["id"],
            {
                "product_id": productId,
                "provider_id": providerId,
            }
        )


        if (rows.length > 0 && rows[0]) {
            return rows[0].id;
        }

        return insert(
            this.tablename,
            {

                "product_id": productId,
                "link": item.link,
                "price": item.price,
                "provider_id": providerId,
            }
        )

    }

}

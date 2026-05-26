import type { Provider } from "../models/provider.ts";
import type { Repository } from "./repository.ts";
import { insert, select } from "./utils.ts";

export class ProviderRepository implements Repository<Provider> {
    tablename = "provider";

    async add(item: Provider): Promise<number> {
        const rows = await select(this.tablename, ["id"], { "name": item.name });

        if (rows.length > 0 && rows[0]) {
            return rows[0].id;
        }

        return insert(
            this.tablename,
            {
                "name": item.name,
                "icon": item.icon,
                "link": item.link,
            },
        )
    }
}

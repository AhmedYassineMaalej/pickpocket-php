import type { Category } from "../models/category.ts";
import type { Repository } from "./repository.ts";
import { insert, select } from "./utils.ts";

export class CategoryRepository implements Repository<Category> {
    tablename = "category";

    async add(item: Category): Promise<number> {
        const rows = await select(this.tablename, ["id"], { "name": item.name });

        if (rows.length > 0 && rows[0]) {
            return rows[0].id;
        }

        return insert(
            this.tablename,
            { "name": item.name }
        )

    }

}

export interface Repository<T> {
    tablename: string;
    add(item: T): Promise<number | void>;
}

import mysql from 'mysql2/promise';
import type { ResultSetHeader, RowDataPacket } from 'mysql2/promise';
import dotenv from 'dotenv';

dotenv.config({ path: "../.env", override: true });

export const pool = mysql.createPool({
    host: 'localhost',
    user: process.env.DB_USER!,
    password: process.env.DB_PASS!,
    database: process.env.DB_NAME!,
    connectionLimit: 15,
    waitForConnections: true,
    queueLimit: 0
});

async function executeQuery(sql: string, params: Array<number | string>) {
    const [rows] = await pool.query<RowDataPacket[]>(sql, params);

    return rows;
}



async function executeStatement(sql: string, params: Array<number | string>): Promise<number> {
    const [rows] = await pool.execute<ResultSetHeader>(sql, params);

    return rows.insertId;
}


export async function insert(tablename: string, values: Record<string, string | number>) {

    const paramString = Object.keys(values).join(", ");
    const placeholderString = Array(Object.keys(values).length).fill("?").join(", ");
    const sql = `INSERT INTO ${tablename} (${paramString}) VALUES (${placeholderString})`
    const params = Object.values(values);


    return await executeStatement(sql, params);
}



export async function select(tablename: string, fields: Array<string>, where: Record<string, string | number>) {
    const selected = fields.join(", ");
    const whereString = Object.keys(where).map(key => `${key} = ?`).join(" AND ");
    const sql = `SELECT ${selected} FROM ${tablename} WHERE ${whereString}`
    const params = Object.values(where);

    return await executeQuery(sql, params);
}

from mysql.connector.pooling import PooledMySQLConnection
from mysql.connector.abstracts import MySQLConnectionAbstract
from dotenv import load_dotenv
import mysql.connector
import os

load_dotenv("../.env", override=True)


def get_connection() -> PooledMySQLConnection | MySQLConnectionAbstract:
    connection = mysql.connector.connect(
        host="localhost",
        port=int(os.getenv("PORT", 3307)),
        user=os.getenv("USER"),
        password=os.getenv("PWD"),
        database="website_db",
    )

    return connection


def execute_query(sql: str, params: tuple) -> list:
    connection = get_connection()
    cursor = connection.cursor()

    cursor.execute(sql, params)
    rows = cursor.fetchall()

    cursor.close()
    connection.close()

    assert type(rows) is list

    return rows


def execute_statement(sql: str, params: tuple) -> int:
    connection = get_connection()
    cursor = connection.cursor()

    cursor.execute(sql, params)
    id = cursor.lastrowid

    connection.commit()

    cursor.close()
    connection.close()

    assert type(id) is int

    return id


def insert(tablename: str, values: dict[str, str | float | int]) -> int:
    params_string = ", ".join(values.keys())
    placeholder_string = ", ".join(["%s"] * len(values))
    sql = f"INSERT INTO {tablename} ({params_string}) VALUES ({placeholder_string})"
    params = tuple(values.values())

    return execute_statement(sql, params)


def select(tablename: str, fields: list[str], where: dict[str, str | int]):
    selected = ", ".join(fields)
    where_string = " AND ".join(map(lambda key: f"{key} = %s", where.keys()))

    sql = f"SELECT {selected} FROM {tablename} WHERE {where_string}"
    params = tuple(where.values())

    return execute_query(sql, params)

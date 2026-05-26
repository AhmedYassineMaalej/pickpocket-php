import type { WebDriver } from "selenium-webdriver"
import type { Instruction } from "./instructions.ts";

export class Browser {
    driver: WebDriver;

    constructor(driver: WebDriver) {
        this.driver = driver;
    }

    async execute<T>(instruction: Instruction<T>): Promise<T> {
        return await instruction.accept(this);
    }

    async quit() {
        await this.driver.quit()
    }
}

import { defineConfig, devices } from '@playwright/test';

const PORT = 8123;
const BASE_URL = `http://127.0.0.1:${PORT}`;

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
    use: {
        baseURL: BASE_URL,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    webServer: {
        // Build the portal assets, reset the DB, then serve the workbench app.
        command:
            'npm run build && ' +
            'touch workbench/database/database.sqlite && ' +
            'vendor/bin/testbench migrate:fresh --force && ' +
            `vendor/bin/testbench serve --port=${PORT}`,
        url: BASE_URL,
        timeout: 120_000,
        reuseExistingServer: !process.env.CI,
    },
});

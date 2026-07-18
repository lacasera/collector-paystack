import { test, expect } from '@playwright/test';
import { MANAGE_URL } from './support/urls';

test.beforeEach(async ({ page }) => {
    await page.goto('/e2e/reset');
    await page.goto('/e2e/login');

    // Seed an active subscription and land on the management portal, where
    // cancelling now lives.
    await page.goto('/e2e/subscribe');
    await expect(page).toHaveURL(MANAGE_URL);
    await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
});

test('cancelling an active subscription returns the plan to a subscribable state', async ({ page }) => {
    await page.getByRole('button', { name: /^cancel subscription$/i }).click();

    // Scoped to the dialog: the summary strip's trigger and the modal's confirm
    // share an accessible name.
    const confirm = page.getByRole('dialog').getByRole('button', { name: /^cancel subscription$/i });
    await expect(confirm).toBeVisible();
    await confirm.click();

    // The success toast triggers a reload of the management portal, which now
    // reports the subscription as cancelled and offers no further cancel action.
    await expect(page.getByRole('button', { name: /^cancel subscription$/i })).toHaveCount(0, {
        timeout: 15_000,
    });

    // The subscription must not disappear: access runs to the end of the period.
    await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
    await expect(page.getByText('Cancelled').first()).toBeVisible();
    await expect(page.getByText('Access until', { exact: true })).toBeVisible();
});

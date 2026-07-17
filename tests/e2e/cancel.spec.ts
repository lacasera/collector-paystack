import { test, expect } from '@playwright/test';

test.beforeEach(async ({ page }) => {
    await page.goto('/e2e/reset');
    await page.goto('/e2e/login');

    // Seed an active subscription and land on the portal with a clean URL
    // (no ?reference), so the post-cancel reload does not re-run verification.
    await page.goto('/e2e/subscribe');
    await expect(page).toHaveURL(/\/collector\/billing$/);
    await expect(page.getByRole('button', { name: /current plan.*cancel/i })).toBeVisible();
});

test('cancelling an active subscription returns the plan to a subscribable state', async ({ page }) => {
    await page.getByRole('button', { name: /current plan.*cancel/i }).click();

    // Confirmation modal.
    const confirm = page.getByRole('button', { name: /^cancel subscription$/i });
    await expect(confirm).toBeVisible();
    await confirm.click();

    // The success toast triggers a reload; the plan is no longer the current one.
    await expect(page.getByRole('button', { name: /current plan.*cancel/i })).toHaveCount(0, {
        timeout: 15_000,
    });
    await expect(page.getByRole('button', { name: 'Subscribe' }).first()).toBeVisible();
});

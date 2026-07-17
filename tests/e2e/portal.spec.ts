import { test, expect } from '@playwright/test';

test.beforeEach(async ({ page }) => {
    await page.goto('/e2e/reset');
    await page.goto('/e2e/login');
    await expect(page).toHaveURL(/\/collector\/billing/);
});

test('renders the billing portal with the configured plans', async ({ page }) => {
    await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Standard' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Subscribe' }).first()).toBeVisible();
});

test('toggles between monthly and yearly plans', async ({ page }) => {
    // Premium is a monthly-only plan.
    await expect(page.getByRole('heading', { name: 'Premium' })).toBeVisible();

    // The checkbox is visually hidden (sr-only) under the styled toggle, so the
    // real click target is the toggle; force past the intercepting overlay.
    await page.getByRole('checkbox').check({ force: true });

    // After switching to yearly, monthly-only plans disappear.
    await expect(page.getByRole('heading', { name: 'Premium' })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Standard' })).toBeVisible();
});

test('subscribing to a plan drives the checkout flow and marks it active', async ({ page }) => {
    await page.getByRole('button', { name: 'Subscribe' }).first().click();

    // Bounces through the (stubbed) PayStack checkout, which processes the
    // reference and redirects back to the clean portal URL.
    await page.waitForURL(/\/collector\/billing$/);

    await expect(page.getByRole('button', { name: /current plan.*cancel/i })).toBeVisible();
});

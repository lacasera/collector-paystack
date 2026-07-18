import { test, expect } from '@playwright/test';
import { CHANGE_PLAN_PATH, MANAGE_URL, PORTAL_PATH, PORTAL_URL } from './support/urls';

test.beforeEach(async ({ page }) => {
    await page.goto('/e2e/reset');
    await page.goto('/e2e/login');
    await expect(page).toHaveURL(PORTAL_URL);
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
    // reference, strips it, and — now that the customer is subscribed — hands
    // them on to the management portal.
    await page.waitForURL(MANAGE_URL);

    await expect(page.getByRole('button', { name: /change plan/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /^cancel subscription$/i })).toBeVisible();
});

test('forwards a subscriber from the plans page to the management portal', async ({ page }) => {
    await page.goto('/e2e/subscribe');

    await page.goto(PORTAL_PATH);

    await expect(page).toHaveURL(MANAGE_URL);
});

test('lets a subscriber back to the plans page to switch plans', async ({ page }) => {
    await page.goto('/e2e/subscribe');

    await page.getByRole('button', { name: /change plan/i }).click();

    // Must NOT bounce back to the management portal.
    await expect(page).toHaveURL(new RegExp(`${PORTAL_PATH.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\?change=1$`));
    await expect(page.getByRole('button', { name: 'Subscribe' }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /manage plan/i })).toBeVisible();
});

test('the change-plan link is reachable directly', async ({ page }) => {
    await page.goto('/e2e/subscribe');

    await page.goto(CHANGE_PLAN_PATH);

    await expect(page.getByRole('heading', { name: 'Standard' })).toBeVisible();
});

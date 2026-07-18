import { test, expect } from '@playwright/test';
import { MANAGE_PATH } from './support/urls';

test.beforeEach(async ({ page }) => {
    await page.goto('/e2e/reset');
    await page.goto('/e2e/login');
    await page.goto('/e2e/subscribe');
});

test('deep-links straight to a section', async ({ page }) => {
    await page.goto(`${MANAGE_PATH}?section=subscriptions`);

    await expect(page.getByRole('tab', { name: /subscriptions/i })).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByText('SUB_e2e')).toBeVisible();
});

test('puts the section in the url and keeps it across a reload', async ({ page }) => {
    await page.goto(MANAGE_PATH);

    await page.getByRole('tab', { name: /payment methods/i }).click();

    await expect(page).toHaveURL(/section=methods$/);

    // The whole point: a reload lands back on the same section.
    await page.reload();

    await expect(page.getByRole('tab', { name: /payment methods/i })).toHaveAttribute('aria-selected', 'true');
});

test('the back button returns to the previous section', async ({ page }) => {
    await page.goto(MANAGE_PATH);

    await page.getByRole('tab', { name: /payment history/i }).click();
    await expect(page.getByRole('tab', { name: /payment history/i })).toHaveAttribute('aria-selected', 'true');

    await page.goBack();

    await expect(page.getByRole('tab', { name: /overview/i })).toHaveAttribute('aria-selected', 'true');
});

test('falls back to the overview for an unknown section', async ({ page }) => {
    await page.goto(`${MANAGE_PATH}?section=nonsense`);

    await expect(page.getByRole('tab', { name: /overview/i })).toHaveAttribute('aria-selected', 'true');
});

import { test, expect } from '@playwright/test';

test('redirects unauthenticated visitors away from the billing portal', async ({ page }) => {
    await page.goto('/e2e/reset'); // clears the session
    await page.goto('/collector/billing');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByText('Please log in')).toBeVisible();
});

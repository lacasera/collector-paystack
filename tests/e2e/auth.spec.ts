import { test, expect } from '@playwright/test';
import { PORTAL_PATH } from './support/urls';

test('redirects unauthenticated visitors away from the billing portal', async ({ page }) => {
    await page.goto('/e2e/reset'); // clears the session
    await page.goto(PORTAL_PATH);

    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByText('Please log in')).toBeVisible();
});

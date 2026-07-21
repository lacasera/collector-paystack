import { test, expect } from '@playwright/test';
import { MANAGE_PATH, PORTAL_PATH } from './support/urls';

/**
 * Captures the README screenshots from the running portal.
 *
 * Skipped in the normal run because it writes files rather than asserting
 * behaviour. Refresh the images with:
 *
 *   CAPTURE_SCREENSHOTS=1 npx playwright test screenshots
 */
test.describe('screenshots', () => {
    test.skip(! process.env.CAPTURE_SCREENSHOTS, 'set CAPTURE_SCREENSHOTS=1 to refresh the README images');
    test.use({ viewport: { width: 1100, height: 900 }, deviceScaleFactor: 2 });

    test('capture the billing portal and management portal', async ({ page }) => {
        await page.goto('/e2e/reset');
        await page.goto('/e2e/login');

        // The plan grid, as an unsubscribed customer sees it.
        await page.goto(PORTAL_PATH);
        await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
        await page.screenshot({ path: 'art/billing-portal.png', fullPage: true });

        // Heights are tuned per section so each image ends just below its
        // content instead of trailing a screenful of empty background.
        await page.goto('/e2e/subscribe');
        await expect(page).toHaveURL(/\/manage$/);
        await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
        await page.setViewportSize({ width: 1100, height: 570 });
        await page.screenshot({ path: 'art/manage-overview.png' });

        await page.goto(`${MANAGE_PATH}?section=history`);
        await expect(page.getByRole('tab', { name: /payment history/i })).toHaveAttribute('aria-selected', 'true');
        await page.setViewportSize({ width: 1100, height: 660 });
        await page.screenshot({ path: 'art/manage-payment-history.png' });

        await page.goto(`${MANAGE_PATH}?section=methods`);
        await expect(page.getByRole('button', { name: /update payment method/i })).toBeVisible();
        await page.setViewportSize({ width: 1100, height: 530 });
        await page.screenshot({ path: 'art/manage-payment-methods.png' });
    });
});

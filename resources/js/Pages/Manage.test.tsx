import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { router } from '@inertiajs/react';
import Manage from './Manage';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children }: { children: React.ReactNode }) => <>{children}</>,
    router: { visit: vi.fn() },
    useForm: () => ({ data: { reason: '' }, setData: vi.fn(), reset: vi.fn(), errors: {} }),
    usePage: () => ({
        props: {
            collector: {
                appName: 'Acme Inc',
                urls: {
                    home: 'http://localhost',
                    subscribe: '/account/subscription',
                    cancel: '/account/subscription/cancel',
                    portal: '/account/billing',
                    manage: '/account/billing/manage',
                    changePlan: '/account/billing?change=1',
                    updatePaymentMethod: '/account/payment-method',
                },
            },
        },
    }),
}));
vi.mock('react-hot-toast', () => ({ default: { success: vi.fn() } }));

const currentPlan = {
    code: 'SUB_abc',
    planCode: 'PLN_abc',
    name: 'Standard',
    description: 'The standard plan',
    features: ['Feature A'],
    interval: 'monthly',
    amount: 'GHS 20',
    card: { brand: 'visa', last4: '4081' },
    nextPaymentDate: 'Aug 18, 2026',
    onTrial: false,
    trialEndsAt: null,
    onGracePeriod: false,
    endsAt: null,
};

const transactions = {
    data: [
        {
            reference: 'zzpjhmod4o',
            amount: 'GHS 20',
            status: 'success',
            channel: 'card',
            paidAt: 'Jul 18, 2026',
            brand: 'visa',
            last4: '4081',
        },
    ],
    meta: { total: 1, page: 1, perPage: 20, pageCount: 1 },
};

function renderManage(overrides: Record<string, unknown> = {}) {
    const props = {
        section: 'overview',
        collectable: { email: 'ada@example.com' },
        currentPlan,
        paymentMethods: [
            { brand: 'visa', last4: '4081', expiry: '12/2030', bank: 'Test Bank', channel: 'card', reusable: true },
        ],
        transactions,
        subscriptions: [
            {
                code: 'SUB_abc',
                name: 'Standard',
                status: 'active',
                active: true,
                startedAt: 'Jul 18, 2026',
                endsAt: null,
                cancelationReason: null,
            },
        ],
        cancelation: { heading: 'Sure?', subText: 'This cancels it', reasonLabel: 'Reason' },
        ...overrides,
    };

    return render(<Manage {...(props as React.ComponentProps<typeof Manage>)} />);
}

describe('Manage page', () => {
    // jsdom refuses real navigation, so capture assignments to location.href.
    let navigatedTo: string;
    let pushedUrl: string;
    let search: string;

    beforeEach(() => {
        vi.clearAllMocks();
        (window as any).axios = { post: vi.fn() };

        navigatedTo = '';
        search = '';
        Object.defineProperty(window, 'location', {
            configurable: true,
            value: {
                get href() { return navigatedTo; },
                set href(value: string) { navigatedTo = value; },
                get search() { return search; },
            },
        });

        window.history.pushState = vi.fn((_state, _title, url) => {
            pushedUrl = String(url);
            search = pushedUrl.includes('?') ? `?${pushedUrl.split('?')[1]}` : '';
        }) as never;

        pushedUrl = '';
    });

    it('summarises the plan, next charge and card without changing section', () => {
        renderManage();

        // All three live in the summary strip, so they are visible on arrival.
        expect(screen.getByRole('heading', { name: 'Standard' })).toBeInTheDocument();
        expect(screen.getByText(/GHS 20/)).toBeInTheDocument();
        expect(screen.getByText('Aug 18, 2026')).toBeInTheDocument();
        expect(screen.getByText(/visa •••• 4081/i)).toBeInTheDocument();
    });

    it('opens on the overview section', () => {
        renderManage();

        expect(screen.getByRole('tab', { name: /overview/i })).toHaveAttribute('aria-selected', 'true');
        expect(screen.getByText(/what.s included/i)).toBeInTheDocument();
        expect(screen.getByText('The standard plan')).toBeInTheDocument();
    });

    it('opens on the section the url asked for', () => {
        // What makes a reload keep its place, and lets an application deep-link
        // straight to a section.
        renderManage({ section: 'subscriptions' });

        expect(screen.getByRole('tab', { name: /subscriptions/i })).toHaveAttribute('aria-selected', 'true');
        expect(screen.getByText('SUB_abc')).toBeInTheDocument();
    });

    it('puts the section in the url when it changes', async () => {
        renderManage();

        await userEvent.click(screen.getByRole('tab', { name: /payment methods/i }));

        expect(pushedUrl).toBe('/account/billing/manage?section=methods');
    });

    it('keeps the url clean on the default section', async () => {
        renderManage({ section: 'methods' });

        await userEvent.click(screen.getByRole('tab', { name: /overview/i }));

        expect(pushedUrl).toBe('/account/billing/manage');
    });

    it('follows the back button between sections', async () => {
        renderManage();

        await userEvent.click(screen.getByRole('tab', { name: /payment history/i }));
        expect(screen.getByRole('tab', { name: /payment history/i })).toHaveAttribute('aria-selected', 'true');

        // Simulate the browser going back to the section-less URL.
        search = '';
        act(() => {
            window.dispatchEvent(new PopStateEvent('popstate'));
        });

        expect(screen.getByRole('tab', { name: /overview/i })).toHaveAttribute('aria-selected', 'true');
    });

    it('prompts to choose a plan when there is no active subscription', () => {
        renderManage({ currentPlan: null });

        expect(screen.getByText(/no active subscription/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /choose a plan/i })).toBeInTheDocument();
    });

    it('lists payment history in the history section', async () => {
        renderManage();

        await userEvent.click(screen.getByRole('tab', { name: /payment history/i }));

        expect(screen.getByText('zzpjhmod4o')).toBeInTheDocument();
    });

    it('keeps the section when paging through payment history', async () => {
        // Paging is a real visit, so without the section in the URL the next
        // page would come back on the Overview.
        renderManage({
            section: 'history',
            transactions: { ...transactions, meta: { total: 40, page: 1, perPage: 20, pageCount: 2 } },
        });

        await userEvent.click(screen.getByRole('button', { name: /next/i }));

        expect(router.visit).toHaveBeenCalledWith(
            '/account/billing/manage?section=history&page=2',
            expect.anything(),
        );
    });

    it('sends the customer to paystacks hosted page to update a card', async () => {
        const post = vi.fn().mockResolvedValue({ data: { redirect: 'https://paystack.com/manage/subscriptions/abc' } });
        (window as any).axios = { post };

        renderManage();

        await userEvent.click(screen.getByRole('tab', { name: /payment methods/i }));
        await userEvent.click(screen.getByRole('button', { name: /update payment method/i }));

        expect(post).toHaveBeenCalledWith('/account/payment-method');
    });

    it('disables the card update when there is no active subscription', async () => {
        renderManage({ currentPlan: null });

        await userEvent.click(screen.getByRole('tab', { name: /payment methods/i }));

        expect(screen.getByRole('button', { name: /update payment method/i })).toBeDisabled();
    });

    it('shows subscription history', async () => {
        renderManage();

        await userEvent.click(screen.getByRole('tab', { name: /subscriptions/i }));

        expect(screen.getByText('SUB_abc')).toBeInTheDocument();
    });

    it('opens the cancel modal from the summary strip', async () => {
        renderManage();

        expect(screen.queryByText('Sure?')).not.toBeInTheDocument();

        await userEvent.click(screen.getByRole('button', { name: /^cancel subscription$/i }));

        expect(screen.getByText('Sure?')).toBeInTheDocument();
    });

    it('hides cancellation once the subscription is already cancelled', () => {
        renderManage({ currentPlan: { ...currentPlan, onGracePeriod: true, endsAt: 'Aug 18, 2026' } });

        expect(screen.queryByRole('button', { name: /^cancel subscription$/i })).not.toBeInTheDocument();
    });

    it('sends Change plan through the escape hatch so the portal does not bounce back', async () => {
        // Without ?change=1 the portal redirects subscribers straight back
        // here, which would make this button look dead.
        renderManage();

        await userEvent.click(screen.getByRole('button', { name: /change plan/i }));

        expect(navigatedTo).toBe('/account/billing?change=1');
    });

    it('sends Choose a plan through the escape hatch too', async () => {
        renderManage({ currentPlan: null });

        await userEvent.click(screen.getByRole('button', { name: /choose a plan/i }));

        expect(navigatedTo).toBe('/account/billing?change=1');
    });
});

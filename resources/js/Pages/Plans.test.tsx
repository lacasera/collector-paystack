import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import Plans from './Plans';

// Stub Inertia primitives used by the page and layout.
vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children }: { children: React.ReactNode }) => <>{children}</>,
    // The URLs are shared from the server so they follow the configured route
    // prefix — non-default ones here prove they are read.
    usePage: () => ({
        props: {
            collector: {
                appName: 'Acme Inc',
                urls: {
                    home: 'http://localhost',
                    subscribe: '/account/subscription',
                    portal: '/account/billing',
                    manage: '/account/billing/manage',
                    changePlan: '/account/billing?change=1',
                },
            },
        },
    }),
}));
vi.mock('axios', () => ({ default: { post: vi.fn() } }));

const monthlyPlans = [
    { id: 'm1', name: 'Monthly Basic', price: '₦5,000', interval: 'monthly', features: [] },
];
const yearlyPlans = [
    { id: 'y1', name: 'Yearly Basic', price: '₦54,000', interval: 'yearly', features: [] },
];

function renderPlans(subscribed: string | null = null) {
    return render(
        <Plans
            collectable={{ email: 'ada@example.com' }}
            monthlyPlans={monthlyPlans as never}
            yearlyPlans={yearlyPlans as never}
            subscribed={subscribed}
        />,
    );
}

describe('Plans page', () => {
    it('shows the host application name in the header', () => {
        renderPlans();

        expect(screen.getByRole('heading', { name: 'Acme Inc' })).toBeInTheDocument();
    });

    it('offers a way back to the subscription only once subscribed', () => {
        renderPlans();

        expect(screen.queryByRole('link', { name: /back to your subscription/i })).not.toBeInTheDocument();

        renderPlans('m1');

        expect(screen.getByRole('link', { name: /back to your subscription/i }))
            .toHaveAttribute('href', '/account/billing/manage');
    });

    it('reads as a plan switcher rather than an onboarding page when subscribed', () => {
        renderPlans('m1');

        expect(screen.getByText(/choose the plan you would like to switch to/i)).toBeInTheDocument();
        expect(screen.queryByText(/to get started/i)).not.toBeInTheDocument();
    });

    it('shows monthly plans by default', () => {
        renderPlans();

        expect(screen.getByText('Monthly Basic')).toBeInTheDocument();
        expect(screen.queryByText('Yearly Basic')).not.toBeInTheDocument();
    });

    it('switches to yearly plans when the frequency toggle is flipped', async () => {
        renderPlans();

        await userEvent.click(screen.getByRole('checkbox'));

        expect(screen.getByText('Yearly Basic')).toBeInTheDocument();
        expect(screen.queryByText('Monthly Basic')).not.toBeInTheDocument();
    });
});

import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import axios from 'axios';
import Plan from './Plan';

vi.mock('axios', () => ({
    default: { post: vi.fn() },
}));
vi.mock('@inertiajs/react', () => ({
    // The URLs are shared from the server so they follow the configured route
    // prefix — non-default ones here prove the component reads the prop rather
    // than a baked-in path.
    usePage: () => ({
        props: {
            collector: {
                urls: {
                    subscribe: '/account/subscription',
                    manage: '/account/billing/manage',
                },
            },
        },
    }),
}));

const plan = {
    id: 1,
    name: 'Basic',
    price: '₦5,000',
    interval: 'monthly',
    description: 'A basic plan',
    features: ['Feature A', 'Feature B'],
    incentive: {},
};

describe('Plan', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders the plan name, price, description and features', () => {
        render(<Plan plan={plan} currentPlan={null} />);

        expect(screen.getByText('Basic')).toBeInTheDocument();
        expect(screen.getByText(/5,000/)).toBeInTheDocument();
        expect(screen.getByText('A basic plan')).toBeInTheDocument();
        expect(screen.getByText('Feature A')).toBeInTheDocument();
        expect(screen.getByText('Feature B')).toBeInTheDocument();
    });

    it('shows a Subscribe button and posts to the subscription endpoint on click', async () => {
        (axios.post as ReturnType<typeof vi.fn>).mockResolvedValue({ data: {} });

        render(<Plan plan={plan} currentPlan={null} />);

        await userEvent.click(screen.getByRole('button', { name: /subscribe/i }));

        expect(axios.post).toHaveBeenCalledWith('/account/subscription', { plan: 1 });
    });

    it('links the current plan to the management page instead of offering Subscribe', () => {
        render(<Plan plan={plan} currentPlan={1} />);

        expect(screen.getByRole('link', { name: /manage plan/i }))
            .toHaveAttribute('href', '/account/billing/manage');
        expect(screen.queryByRole('button', { name: /^subscribe$/i })).not.toBeInTheDocument();
    });

    it('does not offer cancellation from the plan card', () => {
        // Cancelling belongs on the management page; the card must not mount
        // a cancel modal (it previously did so on every card).
        render(<Plan plan={plan} currentPlan={1} />);

        expect(screen.queryByRole('button', { name: /cancel/i })).not.toBeInTheDocument();
    });
});

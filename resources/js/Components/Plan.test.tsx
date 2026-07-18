import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import axios from 'axios';
import Plan from './Plan';

// Plan always mounts the CancelSubscription modal, which uses Inertia's useForm
// and react-hot-toast even while hidden — stub those out.
vi.mock('axios', () => ({
    default: { post: vi.fn() },
}));
vi.mock('@inertiajs/react', () => ({
    useForm: () => ({ data: { reason: '' }, setData: vi.fn(), reset: vi.fn(), errors: {} }),
    // The subscribe/cancel URLs are shared from the server so they follow the
    // configured route prefix — use a non-default one here to prove the
    // component reads the prop rather than a baked-in path.
    usePage: () => ({
        props: {
            collector: {
                urls: {
                    subscribe: '/account/subscription',
                    cancel: '/account/subscription/cancel',
                },
            },
        },
    }),
}));
vi.mock('react-hot-toast', () => ({ default: { success: vi.fn() } }));

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
        render(<Plan plan={plan} currentPlan={null} cancelation={{}} />);

        expect(screen.getByText('Basic')).toBeInTheDocument();
        expect(screen.getByText(/5,000/)).toBeInTheDocument();
        expect(screen.getByText('A basic plan')).toBeInTheDocument();
        expect(screen.getByText('Feature A')).toBeInTheDocument();
        expect(screen.getByText('Feature B')).toBeInTheDocument();
    });

    it('shows a Subscribe button and posts to the subscription endpoint on click', async () => {
        (axios.post as ReturnType<typeof vi.fn>).mockResolvedValue({ data: {} });

        render(<Plan plan={plan} currentPlan={null} cancelation={{}} />);

        await userEvent.click(screen.getByRole('button', { name: /subscribe/i }));

        expect(axios.post).toHaveBeenCalledWith('/account/subscription', { plan: 1 });
    });

    it('shows the current-plan cancel action instead of Subscribe when subscribed', () => {
        render(<Plan plan={plan} currentPlan={1} cancelation={{}} />);

        expect(screen.getByRole('button', { name: /current plan.*cancel/i })).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: /^subscribe$/i })).not.toBeInTheDocument();
    });
});

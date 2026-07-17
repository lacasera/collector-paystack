import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import Plans from './Plans';

// Stub Inertia primitives used by the page/layout and the nested modal.
vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children }: { children: React.ReactNode }) => <>{children}</>,
    useForm: () => ({ data: { reason: '' }, setData: vi.fn(), reset: vi.fn(), errors: {} }),
}));
vi.mock('axios', () => ({ default: { post: vi.fn() } }));
vi.mock('react-hot-toast', () => ({ default: { success: vi.fn() } }));

const monthlyPlans = [
    { id: 'm1', name: 'Monthly Basic', price: '₦5,000', interval: 'monthly', features: [] },
];
const yearlyPlans = [
    { id: 'y1', name: 'Yearly Basic', price: '₦54,000', interval: 'yearly', features: [] },
];

function renderPlans() {
    return render(
        <Plans
            collectable={{ email: 'ada@example.com' }}
            monthlyPlans={monthlyPlans as never}
            yearlyPlans={yearlyPlans as never}
            subscribed={null}
            cancelation={false}
        />,
    );
}

describe('Plans page', () => {
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

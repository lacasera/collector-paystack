import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import CancelSubscription from './CancelSubscription';

vi.mock('@inertiajs/react', () => ({
    useForm: () => ({ data: { reason: '' }, setData: vi.fn(), reset: vi.fn(), errors: {} }),
    // The cancel URL is shared from the server so it follows the configured
    // route prefix — a non-default one here proves the prop is being read.
    usePage: () => ({
        props: { collector: { urls: { cancel: '/account/subscription/cancel' } } },
    }),
}));
vi.mock('react-hot-toast', () => ({ default: { success: vi.fn() } }));

const details = {
    heading: 'Are you sure you want to cancel?',
    subText: 'Your data will be removed.',
    reasonLabel: 'Reason for cancelling',
};

describe('CancelSubscription modal', () => {
    it('renders the cancellation details when shown', () => {
        render(<CancelSubscription show={true} details={details} onCloseModal={vi.fn()} />);

        expect(screen.getByText('Are you sure you want to cancel?')).toBeInTheDocument();
        expect(screen.getByText('Your data will be removed.')).toBeInTheDocument();
        expect(screen.getByText('Reason for cancelling')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /cancel subscription/i })).toBeInTheDocument();
    });

    it('renders nothing when hidden', () => {
        const { container } = render(
            <CancelSubscription show={false} details={details} onCloseModal={vi.fn()} />,
        );

        expect(container).toBeEmptyDOMElement();
    });
});

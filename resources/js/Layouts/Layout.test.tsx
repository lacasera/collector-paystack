import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import toast from 'react-hot-toast';
import Layout from './Layout';

const inertiaLink = vi.fn();
let flash: Record<string, string | null> = {};

vi.mock('react-hot-toast', () => ({
    default: { success: vi.fn(), error: vi.fn() },
}));

vi.mock('@inertiajs/react', () => ({
    // If the layout ever reaches for Inertia's <Link> again, this records it.
    Link: (props: any) => {
        inertiaLink(props);

        return <a href={props.href}>{props.children}</a>;
    },
    usePage: () => ({
        props: {
            collector: {
                appName: 'Acme Inc',
                urls: { home: 'https://example.test/shop' },
                flash,
            },
        },
    }),
}));

describe('Layout', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        flash = {};
    });

    it('surfaces the server flash as a toast', () => {
        flash = { success: 'Your subscription is now active.', error: null };

        render(<Layout><p>content</p></Layout>);

        expect(toast.success).toHaveBeenCalledWith('Your subscription is now active.');
        expect(toast.error).not.toHaveBeenCalled();
    });

    it('surfaces a flashed error', () => {
        flash = { success: null, error: 'We could not confirm that payment.' };

        render(<Layout><p>content</p></Layout>);

        expect(toast.error).toHaveBeenCalledWith('We could not confirm that payment.');
    });

    it('shows nothing when there is no flash', () => {
        render(<Layout><p>content</p></Layout>);

        expect(toast.success).not.toHaveBeenCalled();
        expect(toast.error).not.toHaveBeenCalled();
    });

    it('shows the host application name', () => {
        render(<Layout><p>content</p></Layout>);

        expect(screen.getByRole('heading', { name: 'Acme Inc' })).toBeInTheDocument();
        expect(screen.getByText('content')).toBeInTheDocument();
    });

    it('leaves the portal with a real navigation rather than an inertia visit', () => {
        // The home page belongs to the host application and is not part of this
        // Inertia app. An Inertia visit would XHR it and, on receiving a
        // non-Inertia response, render it in an overlay instead of navigating.
        render(<Layout><p>content</p></Layout>);

        expect(screen.getByRole('link', { name: 'Acme Inc' }))
            .toHaveAttribute('href', 'https://example.test/shop');
        expect(inertiaLink).not.toHaveBeenCalled();
    });
});

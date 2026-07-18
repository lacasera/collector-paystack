import React from 'react';

const TONES: Record<string, string> = {
    success: 'bg-[#E9F9E6] text-[#3BBA4A]',
    neutral: 'bg-[#CACED0]/50 text-gray-600',
    warning: 'bg-amber-100 text-amber-700',
    danger: 'bg-red-100 text-red-600',
};

/**
 * Map a PayStack transaction or subscription status onto a tone.
 */
function toneFor(status: string): string {
    switch (status.toLowerCase()) {
        case 'success':
        case 'active':
            return 'success';
        case 'failed':
        case 'reversed':
            return 'danger';
        case 'abandoned':
        case 'pending':
            return 'warning';
        default:
            return 'neutral';
    }
}

export default function StatusBadge({ status, tone }: { status: string; tone?: string }): React.JSX.Element {
    return (
        <span
            className={`rounded-md px-[7px] py-[1.5px] inline-flex text-[13px] font-bold capitalize
                ${TONES[tone ?? toneFor(status)]}`}
        >
            {status}
        </span>
    );
}

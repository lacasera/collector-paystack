import React from 'react';
import PrimaryButton from '../Button/PrimaryButton';
import StatusBadge from './StatusBadge';
import type { CurrentPlanData } from './CurrentPlan';

interface SubscriptionSummaryProps {
    plan: CurrentPlanData | null;
    card: { brand: string | null; last4: string | null } | null;
    changePlanUrl: string;
    onCancel: () => void;
}

function Field({ label, children }: { label: string; children: React.ReactNode }): React.JSX.Element {
    return (
        <div className='min-w-0'>
            <dt className='text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-1'>{label}</dt>
            <dd className='text-[15px] text-gray-900 truncate'>{children}</dd>
        </div>
    );
}

/**
 * The at-a-glance strip: what the customer is on, what they will be charged
 * next, and the card it will be charged to — without needing to change section.
 */
export default function SubscriptionSummary({
    plan,
    card,
    changePlanUrl,
    onCancel,
}: SubscriptionSummaryProps): React.JSX.Element {
    if (! plan) {
        return (
            <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-5
                flex flex-row items-center justify-between flex-wrap gap-3'>
                <div>
                    <h2 className='font-semibold text-gray-900'>No active subscription</h2>
                    <p className='text-[15px] text-gray-600 mt-0.5'>
                        Choose a plan to start your subscription.
                    </p>
                </div>
                <PrimaryButton onClick={() => (window.location.href = changePlanUrl)}>
                    Choose a plan
                </PrimaryButton>
            </div>
        );
    }

    return (
        <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm'>
            <div className='px-5 py-5'>
                <div className='flex flex-row items-center gap-2 mb-4'>
                    <h2 className='font-semibold text-lg text-gray-900'>{plan.name}</h2>
                    {plan.onGracePeriod
                        ? <StatusBadge status='Cancelled' tone='warning' />
                        : plan.onTrial
                            ? <StatusBadge status='Trialing' tone='warning' />
                            : <StatusBadge status='Active' />}
                </div>

                <dl className='grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4'>
                    <Field label='Amount'>
                        {plan.amount
                            ? <>{plan.amount}{plan.interval && <span className='text-gray-500'> / {plan.interval}</span>}</>
                            : '—'}
                    </Field>

                    <Field label={plan.onGracePeriod ? 'Access until' : 'Next charge'}>
                        {plan.onGracePeriod
                            ? (plan.endsAt ?? '—')
                            : (plan.nextPaymentDate ?? '—')}
                    </Field>

                    <Field label='Payment method'>
                        {card?.last4
                            ? <span className='capitalize'>{card.brand || 'Card'} •••• {card.last4}</span>
                            : '—'}
                    </Field>
                </dl>

                {plan.onTrial && plan.trialEndsAt && (
                    <p className='text-[15px] text-gray-600 mt-4'>Trial ends {plan.trialEndsAt}</p>
                )}
            </div>

            <div className='px-5 py-3 border-t border-gray-200 bg-[#E7E9EA]/30 rounded-b-lg
                flex flex-row items-center justify-between flex-wrap gap-2'>
                <PrimaryButton onClick={() => (window.location.href = changePlanUrl)}>
                    Change plan
                </PrimaryButton>

                {! plan.onGracePeriod && (
                    <button
                        type='button'
                        onClick={onCancel}
                        className='text-sm font-semibold text-gray-600 hover:text-red-600 transition
                            focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 rounded'
                    >
                        Cancel subscription
                    </button>
                )}
            </div>
        </div>
    );
}

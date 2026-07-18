import React from 'react';
import Feature from '../Feature';

export interface CurrentPlanData {
    code: string;
    planCode: string;
    name: string;
    description: string | null;
    features: string[];
    interval: string | null;
    amount: string | null;
    /** The card bound to this subscription, not merely a stored one. */
    card: { brand: string | null; last4: string | null } | null;
    nextPaymentDate: string | null;
    onTrial: boolean;
    trialEndsAt: string | null;
    onGracePeriod: boolean;
    endsAt: string | null;
}

/**
 * The Overview section's detail panel. The plan's headline figures and the
 * change/cancel actions live in the summary strip above, so this covers what
 * the plan actually includes.
 */
export default function CurrentPlan({ plan }: { plan: CurrentPlanData | null }): React.JSX.Element {
    if (! plan) {
        return (
            <p className='text-gray-600 text-[15px] py-6'>
                Once you subscribe, your plan details will appear here.
            </p>
        );
    }

    return (
        <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-5'>
            <h3 className='font-semibold text-gray-900 mb-1'>What&rsquo;s included</h3>

            {plan.description && (
                <p className='text-[15px] text-gray-600 mb-4'>{plan.description}</p>
            )}

            {plan.features.length > 0 ? (
                <ul className='space-y-1.5 flex flex-col text-[15px] text-gray-700'>
                    {plan.features.map((feature, index) => <Feature title={feature} key={index} />)}
                </ul>
            ) : (
                <p className='text-[15px] text-gray-500'>No features listed for this plan.</p>
            )}

            {plan.onGracePeriod && (
                <p className='text-[15px] text-amber-700 mt-4 pt-4 border-t border-gray-200'>
                    This subscription is cancelled. You keep access until {plan.endsAt ?? 'the end of the period'}.
                </p>
            )}
        </div>
    );
}

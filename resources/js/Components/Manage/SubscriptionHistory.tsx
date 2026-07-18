import React from 'react';
import StatusBadge from './StatusBadge';

export interface SubscriptionRow {
    code: string;
    name: string;
    status: string;
    active: boolean;
    startedAt: string | null;
    endsAt: string | null;
    cancelationReason: string | null;
}

export default function SubscriptionHistory({ subscriptions }: { subscriptions: SubscriptionRow[] }): React.JSX.Element {
    if (subscriptions.length === 0) {
        return (
            <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-8'>
                <p className='text-gray-600 text-[15px] text-center'>You have not subscribed to a plan yet.</p>
            </div>
        );
    }

    return (
        <div className='flex flex-col space-y-3'>
            {subscriptions.map(subscription => (
                <div key={subscription.code} className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-4'>
                    <div className='flex flex-row items-start justify-between flex-wrap gap-2'>
                        <div>
                            <div className='flex flex-row items-center gap-2 mb-1'>
                                <h3 className='font-semibold text-gray-800'>{subscription.name}</h3>
                                <StatusBadge status={subscription.status} />
                            </div>
                            <p className='text-sm text-gray-500'>
                                Started {subscription.startedAt ?? '—'}
                                {subscription.endsAt && ` · ${subscription.active ? 'renews' : 'ended'} ${subscription.endsAt}`}
                            </p>
                            {subscription.cancelationReason && (
                                <p className='text-sm text-gray-500 mt-1 italic'>
                                    Reason: {subscription.cancelationReason}
                                </p>
                            )}
                        </div>
                        <span className='text-[13px] text-gray-400 font-mono'>{subscription.code}</span>
                    </div>
                </div>
            ))}
        </div>
    );
}

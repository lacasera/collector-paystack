import React from 'react';
import PrimaryButton from '../Button/PrimaryButton';

export interface PaymentMethodRow {
    brand: string | null;
    last4: string | null;
    expiry: string | null;
    bank: string | null;
    channel: string | null;
    reusable: boolean;
}

interface PaymentMethodsProps {
    methods: PaymentMethodRow[];
    canUpdate: boolean;
    updating: boolean;
    onUpdate: () => void;
}

export default function PaymentMethods({ methods, canUpdate, updating, onUpdate }: PaymentMethodsProps): React.JSX.Element {
    return (
        <div className='flex flex-col space-y-4'>
            {methods.length === 0 ? (
                <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-8'>
                    <p className='text-gray-600 text-[15px] text-center'>No saved payment methods yet.</p>
                </div>
            ) : (
                methods.map((method, index) => (
                    <div key={index} className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-4'>
                        <div className='flex flex-row items-center justify-between flex-wrap gap-2'>
                            <div className='flex flex-row items-center'>
                                <span className='w-[42px] h-[28px] border border-gray-300 rounded-sm mr-3
                                    flex items-center justify-center text-[11px] uppercase font-bold text-gray-600'>
                                    {method.brand || method.channel || 'card'}
                                </span>
                                <div>
                                    <p className='text-gray-800 font-medium'>
                                        •••• •••• •••• {method.last4 ?? '____'}
                                    </p>
                                    {method.expiry && method.expiry !== '/' && (
                                        <p className='text-gray-500 text-sm'>Expires {method.expiry}</p>
                                    )}
                                </div>
                            </div>
                            {method.bank && <span className='text-sm text-gray-500'>{method.bank}</span>}
                        </div>
                    </div>
                ))
            )}

            <div className='pt-2'>
                <PrimaryButton onClick={onUpdate} disabled={! canUpdate || updating}>
                    {updating ? 'Opening PayStack…' : 'Update payment method'}
                </PrimaryButton>
                <p className='text-gray-500 text-sm mt-2'>
                    {canUpdate
                        ? 'Card changes are handled on PayStack’s secure page, so your card details never reach this site.'
                        : 'An active subscription is required before a card can be updated.'}
                </p>
            </div>
        </div>
    );
}

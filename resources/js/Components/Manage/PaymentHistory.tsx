import React from 'react';
import { router } from '@inertiajs/react';
import StatusBadge from './StatusBadge';
import PrimaryButton from '../Button/PrimaryButton';

export interface TransactionRow {
    reference: string;
    amount: string | null;
    status: string;
    channel: string | null;
    paidAt: string | null;
    brand: string | null;
    last4: string | null;
}

export interface PaginationMeta {
    total: number;
    page: number;
    perPage: number;
    pageCount: number;
}

interface PaymentHistoryProps {
    transactions: TransactionRow[];
    meta: PaginationMeta;
    /** Builds the URL for a page, so the caller keeps ownership of the query. */
    pageUrl: (page: number) => string;
}

export default function PaymentHistory({ transactions, meta, pageUrl }: PaymentHistoryProps): React.JSX.Element {
    if (transactions.length === 0) {
        return (
            <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm px-5 py-8'>
                <p className='text-gray-600 text-[15px] text-center'>
                    No payments yet. Charges will appear here once your first payment is processed.
                </p>
            </div>
        );
    }

    const goToPage = (page: number) => router.visit(pageUrl(page), { preserveState: false });

    return (
        <>
            <div className='bg-white rounded-lg border border-gray-300/60 shadow-sm overflow-hidden'>
                <table className='w-full table-fixed text-[15px] text-left'>
                    <thead className='text-gray-600 text-sm border-b border-gray-200 bg-[#E7E9EA]/30'>
                        <tr>
                            <th scope='col' className='font-medium px-4 py-3 w-[30%] sm:w-[22%]'>Date</th>
                            <th scope='col' className='font-medium px-4 py-3 w-[28%] sm:w-[18%]'>Amount</th>
                            <th scope='col' className='font-medium px-4 py-3 w-[24%] sm:w-[16%]'>Status</th>
                            {/* Lower-priority columns drop out before the table
                                can overflow rather than hiding behind a scrollbar. */}
                            <th scope='col' className='font-medium px-4 py-3 w-[20%] hidden sm:table-cell'>Method</th>
                            <th scope='col' className='font-medium px-4 py-3 w-[24%] hidden md:table-cell'>Reference</th>
                        </tr>
                    </thead>
                    <tbody className='divide-y divide-gray-100'>
                        {transactions.map(transaction => (
                            <tr key={transaction.reference} className='hover:bg-[#E7E9EA]/20'>
                                <td className='px-4 py-3 text-gray-800 truncate'>{transaction.paidAt ?? '—'}</td>
                                <td className='px-4 py-3 text-gray-900 font-medium truncate'>{transaction.amount ?? '—'}</td>
                                <td className='px-4 py-3'><StatusBadge status={transaction.status} /></td>
                                <td className='px-4 py-3 text-gray-600 capitalize truncate hidden sm:table-cell'>
                                    {transaction.last4
                                        ? `${transaction.brand || 'card'} •••• ${transaction.last4}`
                                        : transaction.channel ?? '—'}
                                </td>
                                <td
                                    className='px-4 py-3 text-gray-500 font-mono text-[13px] truncate hidden md:table-cell'
                                    title={transaction.reference}
                                >
                                    {transaction.reference}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {meta.pageCount > 1 && (
                <div className='flex flex-row items-center justify-between flex-wrap gap-2 mt-4'>
                    <span className='text-sm text-gray-600'>
                        Page {meta.page} of {meta.pageCount} · {meta.total} payments
                    </span>
                    <div className='flex flex-row space-x-2'>
                        <PrimaryButton
                            variant='secondary'
                            size='sm'
                            disabled={meta.page <= 1}
                            onClick={() => goToPage(meta.page - 1)}
                        >
                            Previous
                        </PrimaryButton>
                        <PrimaryButton
                            variant='secondary'
                            size='sm'
                            disabled={meta.page >= meta.pageCount}
                            onClick={() => goToPage(meta.page + 1)}
                        >
                            Next
                        </PrimaryButton>
                    </div>
                </div>
            )}
        </>
    );
}

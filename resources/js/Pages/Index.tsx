import React from 'react';
import { Head } from '@inertiajs/react';
import PrimaryButton from '../Components/Button/PrimaryButton';
import Layout from '../Layouts/Layout';
import IconVisa from '../Components/Icon/IconVisa';
import IconCheckmark from '../Components/Icon/IconCheckmark';
import IconViewMore from '../Components/Icon/IconViewMore';
import CancelSubscription from '../Components/Modal/CancelSubscription';

export default function Index(): React.JSX.Element {
    const [confirmCancelSubscription, setConfirmCancelSubscription] = React.useState<boolean>(false);
    const receiptViewRef = React.useRef<HTMLDivElement>(null);

    const handleViewReceipt = () => {
        receiptViewRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    return (
        <Layout onViewReceipt={handleViewReceipt}>
            <Head title="Dashboard" />

            <div className='max-w-5xl mx-auto flex flex-col items-center px-5'>
                <div className=''>
                    <div className="mb-8 text-center">
                        <span className='rounded-full bg-[#CACED0]/60 w-[80px] h-[80px] 
                        inline-flex justify-center items-center text-3xl mb-3'>
                            C
                        </span>

                        <p className="text-gray-600 leading-tight mb-4">contactmantey@gmail.com</p>

                        <p className="w-full block text-lg text-gray-900">
                            Get to manage your current subscription plan and more here.
                        </p>
                    </div>

                    <div className='flex flex-col space-y-8'>
                        <div className='bg-[#E7E9EA]/50 rounded-md shadow-sm min-w-[600px]'>
                            <div className='bg-white rounded-t-md rounded-b-sm px-4 py-3 border-x border-t 
                            border-x-gray-300/40 border-t-gray-300/20'>
                                <h2 className="font-bold text-xl text-gray-800 leading-tight mb-3">Hobby</h2>
                                <span className="font-bold text-base text-gray-800 leading-tight mb-3 block">$39.99 / monthly</span>

                                <div className='text-base text-gray-600'>
                                    <p className='mb-2'>Take your application to the next level with our growth plan.</p>
                                    <ul className='space-y-1 flex flex-col text-[15px]'>
                                        <li className='inline-flex items-center'>
                                            <span className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                                                <IconCheckmark className='w-4 h-4 fill-current text-white' />
                                            </span>
                                            Unlimited Servers
                                        </li>
                                        <li className='inline-flex items-center'>
                                            <span className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                                                <IconCheckmark className='w-4 h-4 fill-current text-white' />
                                            </span>
                                            500 Deployments
                                        </li>
                                        <li className='inline-flex items-center'>
                                            <span className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                                                <IconCheckmark className='w-4 h-4 fill-current text-white' />
                                            </span>
                                            Priority Support
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div className='px-4 py-3 flex flex-row justify-between border-x border-b border-t 
                            border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md'>
                                <PrimaryButton>Change Subscription Plan</PrimaryButton>

                                <button
                                    type='button'
                                    onClick={() => setConfirmCancelSubscription(true)}
                                    className='inline-flex items-center px-4 py-2 bg-transparent border border-red-500 
                                rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest hover:bg-red-500/40
                                focus:bg-red-500/40 active:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-red-500 
                                focus:ring-offset-2 transition ease-in-out duration-150'>
                                    CANCEL
                                </button>
                            </div>
                        </div>

                        <div className='min-w-[600px]'>
                            <h1 className="font-semibold text-[21px] text-gray-800 leading-tight mb-2">Payment Info</h1>
                            <div className='bg-white rounded-md shadow-sm'>
                                <div className='bg-white rounded-t-md rounded-b-sm px-4 py-5 border-x border-t
                                border-x-gray-300/40 border-t-gray-300/20 space-y-2'>
                                    <div className='text-base text-gray-600 items-center inline-flex'>
                                        Your current payment info is
                                        <span className='font-bold text-gray-800 inline-flex ml-2 items-center'>
                                            <span className='w-[32px] h-[22px] border flex items-center justify-center rounded-sm mr-2'>
                                                {/* <IconMasterCard className="w-8 h-6"/> */}
                                                {/* <IconAmExp className="w-7 h-4"/> */}
                                                <IconVisa className="w-6 h-6" />
                                            </span> •••• •••• •••• 5964
                                        </span>
                                    </div>
                                    <div className='text-base text-gray-600 items-center'>
                                        Expires <span className='font-bold text-gray-800 inline-flex'> 12/2034</span>
                                    </div>
                                </div>
                                <div className='px-4 py-3 flex flex-row justify-start border-x border-b border-t
                                border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md'>
                                    <PrimaryButton>Update Payment Method</PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <div className='' ref={receiptViewRef} >
                            <h1 className="font-semibold text-[21px] text-gray-800 leading-tight mb-4">Receipts</h1>

                            <p className="text-gray-600 leading-tight mb-1 text-sm uppercase font-medium">FOR CURRENT PLAN</p>

                            <div className="bg-[#E7E9EA]/50 border border-gray-400 py-2 px-4 w-full block 
                                text-[15px] rounded-md mb-4 text-gray-700 space-y-4 shadow-inner">
                                <div>
                                    <h3 className="font-semibold text-gray-800 leading-tight">Plan</h3>
                                    <p className='text-base'>Hobby</p>
                                    <p className='text-base'>$39.99 / monthly</p>
                                </div>

                                <div>
                                    <h3 className="font-semibold text-gray-800 leading-tight">Next billing date</h3>
                                    <p className='text-base'>June 16, 2023</p>
                                </div>
                            </div>

                            <div className='bg-white rounded-md shadow-sm min-w-[600px] px-4 py-0 border-x border-t
                                border-x-gray-300/40 border-t-gray-300/20 divide-y flex flex-col mb-2'>
                                {
                                    new Array(10).fill(null).map((_, index) => (
                                        <div className='flex flex-row space-x-8 items-center py-4 text-[15px]' key={index}>
                                            <div className='font-semibold text-gray-800 flex items-center cursor-pointer hover:underline'>
                                                Feb {10 + (10 - index)}th, 2023
                                                <IconViewMore className="w-[13px] h-[13px] inline-flex fill-current ml-2" />
                                            </div>
                                            <div className='text-gray-800'>Paystack</div>
                                            <div>
                                                {
                                                    index < 8 ? (
                                                        <span className='bg-[#E9F9E6] text-[#3BBA4A] rounded-md px-[7px] py-[1.5px] inline-flex text-[13px] font-bold'>
                                                            Paid
                                                        </span>
                                                    ) : (
                                                        <span className='bg-[#CACED0]/50 text-gray-600 rounded-md px-[7px] py-[1.5px] inline-flex text-[13px] font-bold'>
                                                            Overdue
                                                        </span>
                                                    )
                                                }
                                            </div>
                                            <div className='text-gray-800 inline-flex'>
                                                <span className='inline-flex items-center'>
                                                    <span className='w-[32px] h-[22px] border flex items-center justify-center rounded-sm mr-2'>
                                                        <IconVisa className="w-6 h-6" />
                                                    </span> <span className="text-[12px] mr-1">•••• •••• ••••</span> 5964
                                                </span>
                                            </div>
                                            <div className='text-gray-800'>$27</div>
                                        </div>
                                    ))
                                }
                            </div>
                            <p className="text-gray-600 leading-tight ml-1 text-sm font-medium">NOTE: We only show up to 1 year of receipts history</p>
                        </div>
                    </div>
                </div>
            </div>

            <CancelSubscription
                show={confirmCancelSubscription}
                onCloseModal={() => setConfirmCancelSubscription(false)}
                details={{} as any}
            />
        </Layout>
    );
}

import React, { useState} from 'react';
import { Head } from '@inertiajs/react';
import PrimaryButton from '../Components/Button/PrimaryButton';
import Layout from '../Layouts/Layout';
import IconVisa from '../Components/Icon/IconVisa';
import IconViewMore from '../Components/Icon/IconViewMore';
import CancelSubscription from '../Components/Modal/CancelSubscription';
import Feature from "../Components/Feature";
import Card from "../Components/Card";
import Header from "../Components/Header";
import IconMasterCard from "../Components/Icon/IconMasterCard";
import IconCard from "../Components/Icon/IconCard";

export default function Index(props) {
    const [showCancelModel, setShowCancelModel] = useState(false)

   const receiptViewRef = React.useRef(null);

    console.log(props.history);

   const handleViewReceipt = () => {
      receiptViewRef.current?.scrollIntoView({ behavior: 'smooth' });
   };

   return (
        <Layout onViewReceipt={handleViewReceipt}>
            <Head title="Dashboard" />

            <div className='max-w-5xl mx-auto flex flex-col items-center px-5'>
                <div className=''>

                    <Header email={props.collectable.email} subtext="Get to manage your current subscription plan and more here." />

                    <div className='flex flex-col space-y-8'>
                        <h1 className="font-semibold text-[21px] text-gray-800 leading-tight">
                            Current Subscription Plan
                        </h1>
                        <div className='bg-[#E7E9EA]/50 rounded-md shadow-sm min-w-[600px]'>

                            <div className='bg-white rounded-t-md rounded-b-sm px-4 py-3 border-x border-t 
                            border-x-gray-300/40 border-t-gray-300/20'>
                                <h2 className="font-bold text-xl text-gray-800 leading-tight mb-3">
                                    {props.currentPlan.name}
                                </h2>
                                <span className="font-bold text-base text-gray-800 leading-tight mb-3 block">{props.currentPlan.price} / {props.currentPlan.interval}</span>

                                <div className='text-base text-gray-600'>
                                    <p className='mb-2'>{props.currentPlan.description}</p>

                                    <ul className='space-y-1 flex flex-col text-[15px]'>
                                        {props.currentPlan.features.map((feature, index) => <Feature title={feature} key={index} />)}
                                    </ul>
                                    <li className='text-gray-50'>
                                        Your payment method will be charged automatically for each billing period.
                                    </li>
                                </div>
                            </div>
                            <div className='px-4 py-3 flex flex-row justify-between border-x border-b border-t 
                            border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md'>
                                <PrimaryButton onClick={() => {
                                    window.location.href = `${props.portalUrl}?change=true`
                                }}>
                                    Change Subscription Plan
                                </PrimaryButton>
                                
                                <button 
                                    type='button'
                                    onClick={() => setShowCancelModel(true)}
                                    className='inline-flex items-center px-4 py-2 bg-transparent border border-red-500 
                                rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest hover:bg-red-500/40
                                focus:bg-red-500/40 active:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-red-500 
                                focus:ring-offset-2 transition ease-in-out duration-150'>
                                    CANCEL
                                </button>
                            </div>
                        </div>

                        <div className='min-w-[600px]'>
                            <h1 className="font-semibold text-[21px] text-gray-800 leading-tight mb-2">Payment Methods</h1>
                            <div className='bg-white rounded-md shadow-sm'>
                                {props.paystackCustomer.authorizations.map((paymentMethod, index) => <Card card={paymentMethod} key={index} />)}
                                {/*<div className='px-4 py-3 flex flex-row justify-start border-x border-b border-t*/}
                                {/*border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md'>*/}
                                {/*    <PrimaryButton>Add Payment Method</PrimaryButton>*/}
                                {/*</div>*/}
                            </div>
                        </div>

                        <div className='bg-[#E7E9EA]/50 rounded-md shadow-sm min-w-[600px]'>
                            <div className='bg-white rounded-t-md rounded-b-sm px-4 py-3 border-x border-t
                            border-x-gray-300/40 border-t-gray-300/20'>
                                <h2 className="font-bold text-xl text-gray-800 leading-tight mb-3">Payments</h2>

                                <div>
                                    <h3 className="font-semibold text-gray-800 leading-tight">Next payment on {props.nextBillingDate}</h3>
                                    <h1 className="font-bold text-base text-gray-800 leading-tight mb-3 block">{props.currentPlan.price} </h1>
                                </div>

                            </div>
                        </div>

                        <div className='' ref={receiptViewRef} >
                            <h1 className="font-semibold text-[21px] text-gray-800 leading-tight mb-4">Receipts</h1>
                            
                            <div className='bg-white rounded-md shadow-sm min-w-[600px] px-4 py-0 border-x border-t
                                border-x-gray-300/40 border-t-gray-300/20 divide-y flex flex-col mb-2'> 
                                {
                                   props.history.map((transaction, index) => (
                                    <div className='flex flex-row space-x-8 items-center py-4 text-[15px]' key={index}>
                                        <div className='font-semibold min-w-[100px] text-gray-800 flex items-center cursor-pointer hover:underline'>
                                            {transaction.created_at}
                                            <IconViewMore className="w-[13px] h-[13px] inline-flex fill-current ml-2"/>
                                        </div>
                                        <div>
                                            {
                                               transaction.status === 'Paid' ? (
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
                                                <span className='w-[32px] h-[22px] flex items-center justify-center rounded-sm mr-2'>
                                                    {transaction.payment.brand === 'master' && <IconMasterCard className="w-8 h-6"/>}
                                                    {transaction.payment.brand === 'visa' && <IconVisa className="w-6 h-6"/>}
                                                    {transaction.payment.brand === null && <IconCard className="w-6 h-6 items-center"/>}
                                                </span> <span className="text-[12px] mr-1">•••• •••• ••••</span> {transaction.payment.last4}
                                            </span>
                                        </div>
                                        <div className='text-gray-800'>
                                            {transaction.formatted_price}
                                        </div>
                                    </div>
                                    ))
                                }
                            </div>
                            <p className="text-gray-600 leading-tight ml-1 text-sm font-medium">NOTE: We only show up to 1 year of receipts successful transactions history</p>
                        </div>
                    </div>
                </div> 
            </div>
            <CancelSubscription
                show={showCancelModel}
                details={props.cancelation}
                onCloseModal={() => setShowCancelModel(false)}
                cancelSubscription={() => setShowCancelModel(false)}
            />
        </Layout>
   )
}
  
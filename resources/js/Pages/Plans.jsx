import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import Plan from "../Components/Plan";
import Header from "../Components/Header";

function displayPlans (plans, currentPlan, cancelation){
    return plans.map((plan, index) => <Plan key={index} plan={plan} currentPlan={currentPlan} cancelation={cancelation} />)
}

export default function Plans(props) {
    const [frequency, setFrequency] = React.useState('monthly')
   return (
        <Layout>
            <Head title="Plans" />
            <div className='max-w-5xl mx-auto flex flex-col items-center px-5'>
                <div className='max-w-[600px]'>
                    <Header email={props.collectable?.email} subtext="You may choose one of the subscription plans below to get started."/>
                    <div className='text-center'>
                        <div className='flex flex-row items-center justify-center space-x-2 mb-3'>
                            <span className="text-sm font-bold text-gray-900 uppercase">Monthly</span>
                            <label className="relative inline-flex items-center cursor-pointer">
                                <input 
                                    id="frequency"
                                    name='frequency'
                                    type="checkbox" 
                                    value={frequency} 
                                    className="sr-only peer" 
                                    checked={frequency !== 'monthly'}
                                    onChange={event => setFrequency(event.target.checked ? 'yearly' : 'monthly')}
                                />
                                <div className="w-11 h-[23px] bg-gray-200 peer-focus:outline-none peer-focus:ring-4
                                peer-focus:ring-blue-300 dark:peer-focus:ring-0 rounded-full peer
                                dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[19px] after:w-[19px]
                                after:transition-all dark:border-gray-600 peer-checked:bg-gray-700"></div>
                            </label>
                            <span className="text-sm font-bold text-gray-900 uppercase">Yearly</span>
                        </div>
                    </div>

                    <div className='flex flex-col space-y-5'>
                        {
                            frequency === 'monthly'
                                ? (displayPlans(props.monthlyPlans, props.subscribed, props.cancelation))
                                : (displayPlans(props.yearlyPlans, props.subscribed, props.cancelation))
                        }
                    </div>
                </div>
                {props.subscribed &&
                <div className="px-6 sm:px-0">
                    <button className="flex items-center mt-4" onClick={() =>   window.location.href = `${props.portalUrl}`}>
                        <svg viewBox="0 0 20 20" fill="currentColor" className="text-gray-400 w-4 h-4">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <div className="ml-2 text-sm text-gray-600 underline">
                            Nevermind, I'll keep my old plan
                        </div>
                    </button>
                </div>
                }
            </div>

        </Layout>
   )
}
  
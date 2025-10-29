import React from 'react';
import { Head } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import Plan from "../Components/Plan";

interface PlanData {
    id: string;
    name: string;
    price: number;
    interval: string;
    features: string[];
}

interface CollectableData {
    email: string;
}

interface PlansProps {
    collectable: CollectableData;
    monthlyPlans: PlanData[];
    yearlyPlans: PlanData[];
    subscribed: string | null;
    cancelation: boolean;
}

function displayPlans(plans: PlanData[], currentPlan: string | null, cancelation: boolean): React.JSX.Element[] {
    return plans.map((plan, index) => <Plan key={index} plan={plan} currentPlan={currentPlan} cancelation={cancelation} />)
}

export default function Plans(props: PlansProps): React.JSX.Element {
    const [frequency, setFrequency] = React.useState<'monthly' | 'yearly'>('monthly')
    return (
        <Layout>
            <Head title="Plans" />
            <div className='max-w-5xl mx-auto flex flex-col items-center px-5'>
                <div className='max-w-[600px]'>

                    <div className="mb-6 text-center">
                        <span className='rounded-full bg-[#CACED0]/60 w-[80px] h-[80px] 
                        inline-flex justify-center items-center text-3xl font-medium mb-4'>
                            {props.collectable?.email?.charAt(0).toUpperCase()}
                        </span>

                        <p className="text-gray-800 leading-tight mb-4">
                            {props.collectable?.email}
                        </p>

                        <p className="w-full block text-lg text-gray-900">
                            You may choose one of the subscription plans below to get started.
                        </p>
                    </div>

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
            </div>
        </Layout>
    )
}

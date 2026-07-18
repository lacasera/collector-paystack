import axios from 'axios';
import { usePage } from '@inertiajs/react';
import PrimaryButton from './Button/PrimaryButton';
import Feature from './Feature';

const subscribeToPlan = async (url: string, planId: number) => {
    try {
        const { data } = await axios.post<{ redirect?: string }>(url, {
            plan: planId
        });

        if (data?.redirect) {
            window.location.href = data.redirect;
        }
    } catch (error) {
        console.error('Subscription error:', error);
    }
};

export default function Plan(props: { plan?: any; currentPlan?: any; }) {
    const { plan, currentPlan } = props;
    const { collector } = usePage().props as any;

    const hasPlan = () => plan.id === currentPlan;

    return (
        <div className="bg-[#E7E9EA]/50 rounded-md shadow-sm w-full">
            <div className="bg-white rounded-t-md rounded-b-sm px-4 py-3 border-x border-t
                            border-x-gray-300/40 border-t-gray-300/20 relative">
                <h2 className="font-bold text-xl text-gray-800 leading-tight mb-3">{plan.name}</h2>
                <span className="font-bold text-base text-gray-800 leading-tight mb-3 block">
                    {plan.price} / {plan.interval}
                </span>
                {plan.incentive?.[plan.interval] && (
                    <span className="absolute top-0 right-0 rounded-bl-md rounded-tr-md text-sm
                        bg-[#CACED0]/50 px-2 py-1 text-gray-600">
                        {plan.incentive[plan.interval]}
                    </span>
                )}

                <div className="text-base text-gray-600">
                    <p className="mb-2">{plan.description}</p>
                    <ul className="space-y-1 flex flex-col">
                        {plan.features?.map((feature: any, index: number) => (
                            <Feature title={feature} key={index} />
                        ))}
                    </ul>
                </div>
            </div>
            <div className="px-4 py-3 flex flex-row justify-end items-center border-x border-b border-t
                 border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md h-[60px]">
                {hasPlan() ? (
                    // Cancelling lives on the management page, so the current
                    // plan links there rather than acting in place.
                    <a
                        href={collector.urls.manage}
                        className="inline-flex items-center px-4 py-2 bg-white border border-gray-300
                            rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest
                            hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500
                            focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Manage Plan
                    </a>
                ) : (
                    <PrimaryButton onClick={() => subscribeToPlan(collector.urls.subscribe, plan.id)}>
                        Subscribe
                    </PrimaryButton>
                )}
            </div>
        </div>
    );
}

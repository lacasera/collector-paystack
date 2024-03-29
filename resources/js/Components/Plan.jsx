import {useState} from "react"
import PrimaryButton from "./Button/PrimaryButton";
import Feature from "./Feature";
import DangerButton from "./Button/DangerButton";
import CancelSubscription from "./Modal/CancelSubscription";
import SecondaryButton from "./Button/SecondaryButton";
import TealButton from "./Button/TealButton";

const  subscribeToPlan = async (planId) => {
    const response = await axios.post(`/collector/subscription`, {
        plan: planId
    })

    if (response.data) {
        response.data.redirect
        location.href = response.data.redirect
    }
}

export default function Plan(props) {
    const [showCancelModel, setShowCancelModel] = useState(false)
    const {plan, currentPlan} = props;

    const hasPlan = () => plan.id === currentPlan;

    console.log(plan)
    return (
        <>
            <CancelSubscription
                show={showCancelModel}
                details={props.cancelation}
                onCloseModal={() => setShowCancelModel(false)}
                cancelSubscription={() => setShowCancelModel(false)}
            />
            <div className='bg-[#E7E9EA]/50 rounded-md shadow-sm min-w-[600px]'>
                <div className='bg-white rounded-t-md rounded-b-sm px-4 py-3 border-x border-t
                                        border-x-gray-300/40 border-t-gray-300/20 relative'>
                    <h2 className="font-bold text-xl text-gray-800 leading-tight mb-3">{plan.name}</h2>
                    <span
                        className="font-bold text-base text-gray-800 leading-tight mb-3 block">{plan.price} / {plan.interval}</span>
                    {plan.incentive[plan.interval] && (
                            <span className='absolute top-0 right-0 rounded-bl-md rounded-tr-md text-sm
                                bg-[#CACED0]/50 px-2 py-1 text-gray-600'>
                                   {plan.incentive[plan.interval]}
                            </span>
                        )
                    }

                    <div className='text-base text-gray-600'>
                        <p className='mb-2'>{plan.description}</p>
                        <ul className='space-y-1 flex flex-col'>
                            {plan.features.map((feature, index) => <Feature title={feature} key={index} />)}
                        </ul>
                    </div>
                </div>
                <div className='px-4 py-3 flex flex-row justify-end border-x border-b border-t
                     border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md h-[60px]'>
                    {hasPlan() ?
                            <TealButton onClick={() => setShowCancelModel(true)}>
                                (Current Plan) Cancel
                            </TealButton>
                        :
                        <PrimaryButton onClick={() => subscribeToPlan(plan.id)}>
                            Subscribe
                        </PrimaryButton>
                    }
                </div>
            </div>
        </>
    )
}

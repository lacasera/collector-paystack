import React from 'react';
import Modal from '../Modal';
import SecondaryButton from '../Button/SecondaryButton'
import DangerButton from '../Button/DangerButton'
import TextInput from '../Input/TextInput';
import InputLabel from '../Input/InputLabel';
import InputError from '../Input/InputError';
import { useForm } from '@inertiajs/react';
import { useToasts } from 'react-toast-notifications'

export default function ChangeSubscription({show, onCloseModal, plan}) {
    const reasonInput = React.useRef();
    const { addToast } = useToasts()
    const {
        data,
        setData,
        delete: destroy,
        reset,
        errors,
    } = useForm({
        reason: '',
    });

    const [processing, setProcessing] = React.useState(false)
    const  swapSubscription = async (e) => {
        e.preventDefault();
        setProcessing(true)

        const response = await axios.post('/collector/subscription/change', {plan})

        console.log(response.data)
        if (response.data) {
            setProcessing(false);
            closeModal();
            addToast(  'Subscription changed successfully.', {
                appearance: 'success',
                id: 'subscription-changed',
                autoDismiss: true,
                onDismiss: id => location.reload()
            })
        }
    };

    const closeModal = () => {
        onCloseModal();
        reset();
    };
 
    return (
        <Modal show={show}>
            <div className="rounded-md bg-white w-[600px] mt-10">
                <form onSubmit={swapSubscription} className="p-6">
                    <h2 className="font-semibold text-[21px] text-gray-800 leading-tight">
                        Change Plan
                    </h2>
                    <p className="mt-1 text-gray-600">
                        you are changing your plan
                    </p>

                    {/*<div className="mt-6">*/}
                    {/*    <InputLabel htmlFor="reason" value={details.reasonLabel}/>*/}

                    {/*    <TextInput*/}
                    {/*        id="reason"*/}
                    {/*        name="reason"*/}
                    {/*        ref={reasonInput}*/}
                    {/*        value={data.reason}*/}
                    {/*        onChange={(e) => setData('reason', e.target.value)}*/}
                    {/*        className="mt-1 block w-3/4"*/}
                    {/*        isFocused*/}
                    {/*        placeholder="Service not meeting my expectation"*/}
                    {/*    />*/}
                    {/*    <InputError message={errors.plan} className="mt-2" />*/}
                    {/*</div>*/}
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={swapSubscription} disabled={processing}>
                            {processing ? "Please Wait...." : "Change Plan"}
                        </SecondaryButton>
                        <DangerButton className="ml-3" disabled={processing} onClick={closeModal}>
                             Never Mind
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>
  );
}

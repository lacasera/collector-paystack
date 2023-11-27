import React from 'react';
import Modal from '../Modal';
import SecondaryButton from '../Button/SecondaryButton'
import DangerButton from '../Button/DangerButton'
import TextInput from '../Input/TextInput';
import InputLabel from '../Input/InputLabel';
import InputError from '../Input/InputError';
import { useForm } from '@inertiajs/react';
import { useToasts } from 'react-toast-notifications'

export default function CancelSubscription({show, onCloseModal, details, afterCancelSuccess}) {
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
    const  cancelSubscription = async (e) => {
        e.preventDefault();

        setProcessing(true)
        const response = await axios.post('/collector/subscription/cancel', {
            reason: data.reason
        })

        if (response.data) {
            setProcessing(false);
            closeModal();
            addToast(  'Subscription canceled successfully.', {
                appearance: 'success',
                id: 'subscription-canceled',
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
                <form onSubmit={cancelSubscription} className="p-6">
                    <h2 className="font-semibold text-[21px] text-gray-800 leading-tight">{details.heading}</h2>
                    <p className="mt-1 text-gray-600">
                        {details.subText}
                    </p>

                    <div className="mt-6">
                        <InputLabel htmlFor="reason" value={details.reasonLabel}/>

                        <TextInput
                            id="reason"
                            name="reason"
                            ref={reasonInput}
                            value={data.reason}
                            onChange={(e) => setData('reason', e.target.value)}
                            className="mt-1 block w-3/4"
                            isFocused
                            placeholder="Service not meeting my expectation"
                        />
                        <InputError message={errors.plan} className="mt-2" />
                    </div>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal} disabled={processing}>
                            Never Mind
                        </SecondaryButton>
                        <DangerButton className="ml-3" disabled={processing || !data.reason}>
                            {processing ? "Please Wait...." : "Delete Account"}
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>
  );
}

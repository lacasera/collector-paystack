import React from 'react';
import Modal from '../Modal';
import SecondaryButton from '../Button/SecondaryButton'
import DangerButton from '../Button/DangerButton'
import TextInput from '../Input/TextInput';
import InputLabel from '../Input/InputLabel';
import InputError from '../Input/InputError';
import { useForm } from '@inertiajs/react';
import toast from 'react-hot-toast'

interface CancelSubscriptionDetails {
    heading: string;
    subText: string;
    reasonLabel: string;
}

interface CancelSubscriptionProps {
    show: boolean;
    onCloseModal: () => void;
    details: CancelSubscriptionDetails;
    afterCancelSuccess?: () => void;
}

export default function CancelSubscription({show, onCloseModal, details, afterCancelSuccess}: CancelSubscriptionProps): React.JSX.Element {
    const reasonInput = React.useRef<HTMLInputElement>(null);
    const {
        data,
        setData,
        delete: destroy,
        reset,
        errors,
    } = useForm({
        reason: '',
    });

    const [processing, setProcessing] = React.useState<boolean>(false)
    const cancelSubscription = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setProcessing(true)
        const response = await window.axios.post('/collector/subscription/cancel', {
            reason: data.reason
        })

        if (response.data) {
            setProcessing(false);
            closeModal();
            toast.success('Subscription canceled successfully.', {
                duration: 3000,
            });
            setTimeout(() => location.reload(), 1000);
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
                        <InputLabel htmlFor="reason" value={details.reasonLabel}>
                        <TextInput
                            id="reason"
                            name="reason"
                            ref={reasonInput}
                            value={data.reason}
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setData('reason', e.target.value)}
                            className="mt-1 block w-3/4"
                            isFocused
                            placeholder="Service not meeting my expectation"
                        />
                        <InputError message={errors.reason} className="mt-2" />
                        </InputLabel>
                    </div>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal} disabled={processing}>
                            Never Mind
                        </SecondaryButton>
                        <DangerButton className="ml-3" disabled={processing}>
                            {processing ? "Please Wait...." : "Cancel Subscription"}
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>
  );
}

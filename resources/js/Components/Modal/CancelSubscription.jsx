import React from 'react';
import Modal from '../Modal';
import SecondaryButton from '../Button/SecondaryButton'
import DangerButton from '../Button/DangerButton'
import TextInput from '../Input/TextInput';
import TextArea from '../Input/TextArea';
import InputLabel from '../Input/InputLabel';
import InputError from '../Input/InputError';
import { useForm } from '@inertiajs/react';

export default function CancelSubscription({show, onCloseModal}) {
    const planInput = React.useRef();

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
    } = useForm({
        plan: '',
        reason: ''
    });

    const cancelSubscription = (e) => {
        e.preventDefault();
        console.log('cancelSubscription', data)
        // destroy(route('profile.destroy'), {
        //     preserveScroll: true,
        //     onSuccess: () => closeModal(),
        //     onError: () => plan.current.focus(),
        //     onFinish: () => reset(),
        // });
    };

    const closeModal = () => {
        onCloseModal();
        reset();
    };
 
    return (
        <Modal show={show}>
            <div className="rounded-md bg-white w-full sm:w-[600px] mt-10">
                <form onSubmit={cancelSubscription} className="p-6">
                    <h2 className="font-semibold text-[21px] text-gray-800 leading-tight">
                        Are you sure you want to cancel your subscription?
                    </h2>
                    <p className="mt-1 text-gray-600">
                    You may cancel your subscription at any time. Once cancelled, you will still have access 
                    to your subscription until the end of your current billing cycle. 
                    To confirm your cancellation, please provide the name of your current plan.
                    </p>
                    <div className="mt-6">
                        <InputLabel htmlFor="password" value="Password" className="sr-only" />
                        <TextInput
                            id="plan"
                            name="plan"
                            ref={planInput}
                            value={data.plan}
                            onChange={(e) => setData('plan', e.target.value)}
                            className="mt-1 block w-full sm:w-3/4"
                            isFocused
                            placeholder="Plan Name"
                        />
                        <InputError message={errors.plan} className="mt-2" />
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="reason" value="Please let us know why you're cancelling" />
                        <TextArea
                            id="reason"
                            className="mt-1 block w-full"
                            value={data.reason}
                            rows={3}
                            onChange={e => setData('reason', e.target.value)}
                        />
                        <InputError message={errors.reason} className="mt-2" />
                    </div>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal}>
                            Never Mind
                        </SecondaryButton>
                        <DangerButton className="ml-3" disabled={processing}>
                            Delete Account
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>
  );
}

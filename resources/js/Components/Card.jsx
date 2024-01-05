import React from 'react';
import IconMasterCard from "./Icon/IconMasterCard";
import IconVisa from "./Icon/IconVisa";
export default function Card({card}) {
    return (
        <div className='bg-white rounded-t-md rounded-b-sm px-4 py-5 border border-gray-300 space-y-2'>
            <div className='text-base text-gray-600 items-center inline-flex'>
                <span className='font-bold text-gray-800 inline-flex ml-2 items-center'>
                    <span className='w-[32px] h-[22px] border flex items-center justify-center rounded-sm mr-2'>
                        {card.brand === 'master' && <IconMasterCard className="w-8 h-6"/>}
                        {card.brand === 'visa' && <IconVisa className="w-6 h-6"/>}
                    </span> •••• •••• {card.last4}
                </span>
            </div>
            <div className='text-base text-gray-600 items-center'>
                Expires <span className='font-bold text-gray-800 inline-flex'> {card.exp_month}/{card.exp_year}</span>
            </div>
        </div>
    );
}

import React from 'react';
import IconMasterCard from "./Icon/IconMasterCard";
import IconVisa from "./Icon/IconVisa";
export default function Header({email, subtext}) {
    return (
        <div className="mb-6 text-center">
            <span className='rounded-full bg-[#CACED0]/60 w-[80px] h-[80px]
                inline-flex justify-center items-center text-3xl font-medium mb-4'>
                {email?.charAt(0).toUpperCase()}
            </span>

            <p className="text-gray-800 leading-tight mb-4">
                {email}
            </p>

            <p className="w-full block text-lg text-gray-900">
                {subtext}
            </p>
        </div>
    );
}
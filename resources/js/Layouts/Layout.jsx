import React from 'react';
import { Link } from '@inertiajs/react';
import IconReceiptsV2 from '../Components/Icon/IconReceiptsV2';
import IconList from '../Components/Icon/IconList';
import IconGrid from '../Components/Icon/IconGrid';

export default function Layout({children, onViewReceipt, onSwitchList, isListView}) {

    return (
        <div className="flex w-full flex-col bg-site">
            <header className="bg-transparent">
                <div className="w-full sm:max-w-5xl mx-auto px-5">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/">
                                    <h2 className="font-bold text-xl text-gray-800 leading-tight">PayLink</h2>
                                </Link>
                            </div>
                        </div>
                        {
                            onSwitchList ? (
                                <div className="flex items-center ml-6 space-x-5">
                                    <button type='button' className="h-6 w-6 relative" onClick={onSwitchList}>
                                        {
                                            isListView ? (
                                                <IconList className="w-[19px] h-[19px] fill-current text-[#1F1D1B]"/>
                                            ) : (
                                                <IconGrid className="w-[19px] h-[19px] fill-current text-[#1F1D1B]"/>
                                            )
                                        }     
                                    </button>
                                </div>
                            ) : null
                        }
                        {
                            onViewReceipt ? (
                                <div className="flex items-center ml-6 space-x-5">
                                    <button type='button' className="h-6 w-6 relative" onClick={onViewReceipt}>
                                        <IconReceiptsV2 className="w-[22px] h-[22px] fill-current text-[#1F1D1B]"/>
                                    </button>
                                </div>
                            ) : null
                        }
                    </div>
                </div>
            </header>
            <main className="flex-1 py-6 pb-10">{children}</main>
        </div>
    );
}
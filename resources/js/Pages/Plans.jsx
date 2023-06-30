import React from 'react';
import { Head } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import PrimaryButton from '../Components/Button/PrimaryButton';
import IconCheckmark from '../Components/Icon/IconCheckmark';

export default function Plans(props) {
   const [frequency, setFrequency] = React.useState('monthly')
   const [isListView, setIsListView] = React.useState(true) //list or grid
  
   return (
        <Layout onSwitchList={() => setIsListView((fndIsListView) => !fndIsListView)} isListView={isListView}>
            <Head title="Plans" />

            <div className='w-full sm:max-w-5xl mx-auto flex flex-col items-center px-5'>
                <div className='w-full sm:max-w-[600px]'>
                    {/* <div className='alert mb-8'>
                        <span className="bg-[#CACED0] border border-gray-400 py-2 px-4 w-full block 
                        text-[15px] rounded-md mb-4 text-gray-700">
                            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
                            mollit anim id est laborum
                        </span>
                    </div> */}

                    <div className="mb-6 text-center">
                        <span className='rounded-full bg-[#CACED0]/60 w-[80px] h-[80px] 
                        inline-flex justify-center items-center text-3xl font-medium mb-4'>
                            C
                        </span>
                        
                        <p className="text-gray-800 leading-tight mb-4">contactmantey@gmail.com</p>

                        <p className="w-full block text-lg text-gray-900">
                            You may choose one of the subscription plans below to get started.
                        </p>
                    </div>

                    <div className='text-center'>
                        <div className='flex flex-row items-center justify-center space-x-2 mb-5'>
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

                    <div className={`grid ${isListView ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2' } gap-5`}>
                        {
                            ['Hobby', 'Growth', 'Advanced'].map((plan, index) => (
                                <div className='bg-[#E7E9EA]/50 rounded-md shadow-sm w-full' key={index}>
                                    <div className='bg-white rounded-t-md rounded-b-sm px-4 py-3 border-x border-t 
                                    border-x-gray-300/40 border-t-gray-300/20 relative'>
                                        <h2 className="font-bold text-xl text-gray-800 leading-tight mb-3">{plan}</h2>
                                        <span className="font-bold text-base text-gray-800 leading-tight mb-3 block">${39+index}.99 / monthly</span>
                                        {
                                            frequency === "yearly" ? (
                                                <span className='absolute top-0 right-0 rounded-bl-md rounded-tr-md text-sm 
                                                bg-[#CACED0]/50 px-2 py-1 text-gray-600'>
                                                    Save 10%
                                                </span>
                                            ) : null
                                        }
                                        <div className='text-base text-gray-600'>
                                            <p className='mb-2'>Take your application to the next level with our growth plan.</p>
                                            <ul className='space-y-1 flex flex-col'>
                                                <li className='inline-flex items-center'>
                                                    <span className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                                                        <IconCheckmark className='w-4 h-4 fill-current text-white'/>
                                                    </span>
                                                    Unlimited Servers
                                                </li>
                                                <li className='inline-flex items-center'>
                                                    <span className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                                                        <IconCheckmark className='w-4 h-4 fill-current text-white'/>
                                                    </span>
                                                    500 Deployments
                                                </li>
                                                <li className='inline-flex items-center'>
                                                    <span className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                                                        <IconCheckmark className='w-4 h-4 fill-current text-white'/>
                                                    </span>
                                                    Priority Support
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div className='px-4 py-3 flex flex-row justify-end border-x border-b border-t 
                                    border-gray-300 border-t-gray-300/20 border-x-gray-300/40 rounded-b-md h-[60px]'>
                                        {
                                            index === 0 ? (
                                                <span className='inline-flex items-center px-4 py-1 text-gray-400'>Current Subscribed</span>
                                            ) :(
                                                <PrimaryButton>Subscribe</PrimaryButton>
                                            )
                                        }
                                    </div>
                                </div>
                            ))
                        }
                    </div>
                </div> 
            </div>
        </Layout>
   )
}
  
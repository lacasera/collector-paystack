import {useState} from "react";

export default function Alert(props: { message: any; alert: any; }) {

    const { message, alert } = props
    const [show, setShow] = useState(true)
    return (
        <>
        {show && <div className={`${alert === 'success' ? 'bg-green-400 border-green-300' : 'bg-red-400 border-red-400'} px-6 py-4  mb-8  border  sm:rounded-lg shadow-sm`}>
            <div className="flex items-center text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="inline-block w-5 h-5 mr-2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <span className="grow">
                    {message}
                </span>

                <button onClick={() => setShow(false)} className="p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="w-4 h-4">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>}
        </>
    )
}
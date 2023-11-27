import IconCheckmark from "./Icon/IconCheckmark";

export default function Feature(props) {
    const { title } = props;
    return (
        <li className='inline-flex items-center'>
            <span
                className='bg-green-400 rounded-full w-[17px] h-[17px] flex items-center justify-center mr-2'>
                <IconCheckmark className='w-4 h-4 fill-current text-white'/>
            </span>
            {title}
        </li>
    )
}
import React from 'react';
import { cn } from '../../lib/utils';

interface PrimaryButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    className?: string;
    disabled?: boolean;
    children: React.ReactNode;
    variant?: 'primary' | 'secondary';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
}

export default function PrimaryButton({ 
    className = '', 
    disabled = false, 
    children, 
    variant = 'primary',
    size = 'md',
    type = 'button',
    ...props 
}: PrimaryButtonProps): React.JSX.Element {
    const baseClasses = 'inline-flex items-center border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150';
    
    const variantClasses = {
        primary: 'bg-gray-800 text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:ring-indigo-500',
        secondary: 'bg-white text-gray-700 hover:bg-gray-50 focus:bg-gray-50 active:bg-gray-100 focus:ring-gray-500 border-gray-300',
    };
    
    const sizeClasses = {
        sm: 'px-3 py-1.5 text-xs',
        md: 'px-4 py-2',
        lg: 'px-6 py-3 text-sm',
    };
    
    const disabledClasses = disabled ? 'opacity-25 cursor-not-allowed' : '';
    
    return (
        <button
            {...props}
            type={type}
            className={cn(
                baseClasses,
                variantClasses[variant],
                sizeClasses[size],
                disabledClasses,
                className
            )}
            disabled={disabled}
        >
            {children}
        </button>
    );
}

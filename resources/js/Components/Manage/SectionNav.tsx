import React from 'react';

export interface SectionDefinition {
    key: string;
    label: string;
    count?: number;
}

interface SectionNavProps {
    sections: SectionDefinition[];
    active: string;
    onChange: (key: string) => void;
}

/**
 * Vertical section nav for the management portal.
 *
 * Deliberately not a scrolling strip: the previous horizontal tabs needed an
 * overflow container, which rendered a visible scrollbar across the page.
 * Stacking the items lets them wrap naturally on narrow screens instead.
 */
export default function SectionNav({ sections, active, onChange }: SectionNavProps): React.JSX.Element {
    return (
        <nav
            role='tablist'
            aria-label='Billing sections'
            className='flex flex-row gap-1 overflow-visible sm:flex-col sm:gap-0.5 flex-wrap'
        >
            {sections.map(section => {
                const isActive = section.key === active;

                return (
                    <button
                        key={section.key}
                        type='button'
                        role='tab'
                        aria-selected={isActive}
                        onClick={() => onChange(section.key)}
                        className={`flex flex-row items-center justify-between gap-3 rounded-md px-3 py-2
                            text-sm font-medium text-left transition
                            ${isActive
                                ? 'bg-[#E7E9EA] text-gray-900'
                                : 'text-gray-600 hover:bg-[#E7E9EA]/60 hover:text-gray-900'}`}
                    >
                        <span>{section.label}</span>
                        {typeof section.count === 'number' && section.count > 0 && (
                            <span className='rounded-full bg-white/80 px-2 py-0.5 text-[11px] text-gray-600'>
                                {section.count}
                            </span>
                        )}
                    </button>
                );
            })}
        </nav>
    );
}

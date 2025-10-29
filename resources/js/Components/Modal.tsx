import React from 'react';
import IconClose from './Icon/IconClose';

interface ModalProps {
  children: React.ReactNode;
  show: boolean;
  onClose?: () => void;
  onHide?: () => void;
  className?: string;
}

function Modal({ children, show, onClose, onHide, className }: ModalProps): React.JSX.Element | null {
  const elementRef = React.useRef<HTMLDivElement>(null);

  React.useEffect(() => {
    function listener(event: MouseEvent) {
      if (elementRef?.current) {
        const element = elementRef.current;
        if (element && element.contains(event.target as Node)) {
          return;
        }
        onHide?.();
      }
    }

    if (show && onHide) {
      document.addEventListener('mousedown', listener);
    }

    return () => {
      document.removeEventListener('mousedown', listener);
    };
  }, [show, onHide]);

  if (!show) return null;

  return (
    <div
      className="fixed z-[60] inset-0 overflow-hidden"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div className="flex tablet:block items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center tablet:p-0">
        <div
          className="fixed inset-0 bg-[#303030] bg-opacity-75 transition-opacity"
          aria-hidden="true"
        />

        <span
          className="hidden tablet:inline-block tablet:align-middle tablet:h-screen"
          aria-hidden="true"
        >
          &#8203;
        </span>

        <div
          ref={elementRef}
          className={`relative inline-block align-bottom text-left overflow-hidden transform transition-all
            tablet:my-8 tablet:align-baseline tablet:w-fit ${className}`}
        >
          {onClose && (
            <div className="w-full h-9 mb-7">
              <button
                type="button"
                onClick={onClose}
                className="w-9 h-9 bg-white rounded-full flex items-center justify-center float-right cursor-pointer"
              >
                <IconClose id="close" className="w-3 h-3" />
              </button>
            </div>
          )}

          <div className="w-full rounded-md bg-white shadow-xl">{children}</div>
        </div>
      </div>
    </div>
  );
}

export default Modal;

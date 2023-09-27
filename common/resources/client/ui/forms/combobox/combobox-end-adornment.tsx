import React, {ReactElement, useEffect, useRef, useState} from 'react';
import {SvgIconProps} from '@common/icons/svg-icon';
import {useTrans} from '@common/i18n/use-trans';
import {useListboxContext} from '@common/ui/forms/listbox/listbox-context';
import {ProgressCircle} from '@common/ui/progress/progress-circle';
import {KeyboardArrowDownIcon} from '@common/icons/material/KeyboardArrowDown';

interface Props {
  isLoading?: boolean;
  icon?: ReactElement<SvgIconProps>;
}
export function ComboboxEndAdornment({isLoading, icon}: Props) {
  const timeout = useRef<any>(null);
  const {trans} = useTrans();
  const [showLoading, setShowLoading] = useState(false);

  const {
    state: {isOpen, inputValue},
  } = useListboxContext();

  const lastInputValue = useRef(inputValue);
  useEffect(() => {
    if (isLoading && !showLoading) {
      if (timeout.current === null) {
        timeout.current = setTimeout(() => {
          setShowLoading(true);
        }, 500);
      }

      // If user is typing, clear the timer and restart since it is a new request
      if (inputValue !== lastInputValue.current) {
        clearTimeout(timeout.current);
        timeout.current = setTimeout(() => {
          setShowLoading(true);
        }, 500);
      }
    } else if (!isLoading) {
      // If loading is no longer happening, clear any timers and hide the loading circle
      setShowLoading(false);
      clearTimeout(timeout.current);
      timeout.current = null;
    }

    lastInputValue.current = inputValue;
  }, [isLoading, showLoading, inputValue]);

  // loading circle should only be displayed if menu is open, if menuTrigger is "manual", or first time load (to stop circle from showing up when user selects an option)
  const showLoadingIndicator = showLoading && (isOpen || isLoading);

  if (showLoadingIndicator) {
    return (
      <ProgressCircle
        aria-label={trans({message: 'Loading'})}
        size="sm"
        isIndeterminate
      />
    );
  }

  return icon || <KeyboardArrowDownIcon />;
}

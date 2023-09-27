import React, {ReactElement, Ref} from 'react';
import {BaseFieldPropsWithDom} from '../input-field/base-field-props';
import {Item} from '../listbox/item';
import {useListbox} from '../listbox/use-listbox';
import {IconButton} from '../../buttons/icon-button';
import {TextField} from '../input-field/text-field/text-field';
import {Listbox} from '../listbox/listbox';
import {SvgIconProps} from '@common/icons/svg-icon';
import {useListboxKeyboardNavigation} from '@common/ui/forms/listbox/use-listbox-keyboard-navigation';
import {createEventHandler} from '@common/utils/dom/create-event-handler';
import {ListBoxChildren, ListboxProps} from '../listbox/types';
import {Popover} from '../../overlays/popover';
import {ComboboxEndAdornment} from '@common/ui/forms/combobox/combobox-end-adornment';

export {Item as Option};

export type ComboboxProps<T extends object> = Omit<
  BaseFieldPropsWithDom<HTMLInputElement>,
  'endAdornment'
> &
  ListBoxChildren<T> &
  ListboxProps & {
    selectionMode?: 'single' | 'none';
    isAsync?: boolean;
    isLoading?: boolean;
    openMenuOnFocus?: boolean;
    endAdornmentIcon?: ReactElement<SvgIconProps>;
    useOptionLabelAsInputValue?: boolean;
    hideEndAdornment?: boolean;
  };

function ComboBox<T extends object>(
  props: ComboboxProps<T> & {selectionMode: 'single'},
  ref: Ref<HTMLInputElement>
) {
  const {
    children,
    items,
    isAsync,
    isLoading,
    openMenuOnFocus = true,
    endAdornmentIcon,
    onItemSelected,
    maxItems,
    clearInputOnItemSelection,
    inputValue: userInputValue,
    selectedValue,
    onSelectionChange,
    allowCustomValue = false,
    onInputValueChange,
    defaultInputValue,
    selectionMode = 'single',
    useOptionLabelAsInputValue,
    showEmptyMessage,
    floatingMaxHeight,
    hideEndAdornment = false,
    blurReferenceOnItemSelection,
    isOpen: propsIsOpen,
    onOpenChange: propsOnOpenChange,
    ...textFieldProps
  } = props;

  const listbox = useListbox(
    {
      ...props,
      floatingMaxHeight,
      blurReferenceOnItemSelection,
      selectionMode,
      role: 'listbox',
      virtualFocus: true,
      clearSelectionOnInputClear: true,
    },
    ref
  );

  const {
    reference,
    listboxId,
    onInputChange,
    state: {
      isOpen,
      setIsOpen,
      inputValue,
      setInputValue,
      selectValues,
      selectedValues,
      setActiveCollection,
    },
    collection,
  } = listbox;

  const textLabel = selectedValues[0]
    ? collection.get(selectedValues[0])?.textLabel
    : undefined;

  const {handleListboxSearchFieldKeydown} =
    useListboxKeyboardNavigation(listbox);

  const handleFocusAndClick = createEventHandler(
    (e: React.FocusEvent<HTMLInputElement>) => {
      if (openMenuOnFocus && !isOpen) {
        setIsOpen(true);
      }
      e.target.select();
    }
  );

  return (
    <Listbox
      listbox={listbox}
      mobileOverlay={Popover}
      onPointerDown={e => {
        // prevent focus from leaving input when scrolling listbox via mouse
        e.preventDefault();
      }}
    >
      <TextField
        inputRef={reference}
        {...textFieldProps}
        endAdornment={
          !hideEndAdornment ? (
            <IconButton
              radius="rounded"
              size="md"
              tabIndex={-1}
              disabled={textFieldProps.disabled}
              className="pointer-events-auto"
              onPointerDown={e => {
                e.preventDefault();
                e.stopPropagation();
                setActiveCollection('all');
                setIsOpen(!isOpen);
              }}
            >
              <ComboboxEndAdornment
                isLoading={isLoading}
                icon={endAdornmentIcon}
              />
            </IconButton>
          ) : null
        }
        aria-expanded={isOpen ? 'true' : 'false'}
        aria-haspopup="listbox"
        aria-controls={isOpen ? listboxId : undefined}
        aria-autocomplete="list"
        autoComplete="off"
        autoCorrect="off"
        spellCheck="false"
        onChange={onInputChange}
        value={useOptionLabelAsInputValue && textLabel ? textLabel : inputValue}
        onBlur={e => {
          if (allowCustomValue) {
            selectValues(e.target.value);
          } else if (!clearInputOnItemSelection) {
            const val = selectedValues[0];
            setInputValue(selectValues.length && val != null ? `${val}` : '');
          }
        }}
        onFocus={handleFocusAndClick}
        onClick={handleFocusAndClick}
        onKeyDown={e => handleListboxSearchFieldKeydown(e)}
      />
    </Listbox>
  );
}

const ComboBoxForwardRef = React.forwardRef(ComboBox) as <T extends object>(
  props: ComboboxProps<T> & {ref?: Ref<HTMLInputElement>}
) => ReactElement;
export {ComboBoxForwardRef as ComboBox};

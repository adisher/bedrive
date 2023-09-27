import React, {
  cloneElement,
  ComponentPropsWithRef,
  ReactElement,
  ReactNode,
  useCallback,
  useId,
  useRef,
} from 'react';
import clsx from 'clsx';
import {mergeProps} from '@react-aria/utils';
import {toast} from '@common/ui/toast/toast';
import {Field} from '@common/ui/forms/input-field/field';
import {
  getInputFieldClassNames,
  InputFieldStyle,
} from '@common/ui/forms/input-field/get-input-field-class-names';
import {FileEntry} from '@common/uploads/file-entry';
import {useAutoFocus} from '@common/ui/focus/use-auto-focus';
import {UploadStrategyConfig} from '@common/uploads/uploader/strategy/upload-strategy';
import {useActiveUpload} from '@common/uploads/uploader/use-active-upload';
import {Disk} from '@common/uploads/types/backend-metadata';
import {Button} from '@common/ui/buttons/button';
import {Trans} from '@common/i18n/trans';
import {ProgressBar} from '@common/ui/progress/progress-bar';
import {Input} from '@common/ui/forms/input-field/input';
import {useController} from 'react-hook-form';
import {useFileEntryModel} from '@common/uploads/requests/use-file-entry-model';
import {Skeleton} from '@common/ui/skeleton/skeleton';
import {AnimatePresence, m} from 'framer-motion';
import {opacityAnimation} from '@common/ui/animation/opacity-animation';

interface Props {
  className?: string;
  label?: ReactNode;
  description?: ReactNode;
  invalid?: boolean;
  errorMessage?: ReactNode;
  required?: boolean;
  disabled?: boolean;
  value?: string;
  onChange?: (newValue: string) => void;
  allowedFileTypes?: string[];
  maxFileSize?: number;
  diskPrefix: string;
  showRemoveButton?: boolean;
  autoFocus?: boolean;
}
export function FileEntryField({
  className,
  label,
  description,
  value,
  onChange,
  diskPrefix,
  showRemoveButton,
  invalid,
  errorMessage,
  required,
  autoFocus,
  disabled,
  allowedFileTypes,
  maxFileSize,
}: Props) {
  const {
    uploadFile,
    entry,
    uploadStatus,
    deleteEntry,
    isDeletingEntry,
    percentage,
  } = useActiveUpload();

  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus({autoFocus}, inputRef);
  const {data} = useFileEntryModel(value, {enabled: !entry && !!value});

  const fieldId = useId();
  const labelId = label ? `${fieldId}-label` : undefined;
  const descriptionId = description ? `${fieldId}-description` : undefined;

  const currentValue = value || entry?.url;
  const currentEntry = entry || data?.fileEntry;

  const uploadOptions: UploadStrategyConfig = {
    showToastOnRestrictionFail: true,
    restrictions: {
      allowedFileTypes,
      maxFileSize,
    },
    metadata: {
      diskPrefix,
      disk: Disk.uploads,
    },
    onSuccess: (entry: FileEntry) => onChange?.(entry.url),
    onError: message => {
      if (message) {
        toast.danger(message);
      }
    },
  };

  const inputFieldClassNames = getInputFieldClassNames({
    description,
    descriptionPosition: 'top',
    invalid,
    disabled: disabled || uploadStatus === 'inProgress',
  });

  const removeButton = showRemoveButton ? (
    <Button
      variant="link"
      color="danger"
      size="xs"
      disabled={isDeletingEntry || !currentValue || disabled}
      onClick={() => {
        deleteEntry({
          onSuccess: () => onChange?.(''),
        });
      }}
    >
      <Trans message="Remove file" />
    </Button>
  ) : null;

  const handleUpload = useCallback(() => {
    inputRef.current?.click();
  }, []);

  return (
    <div className={clsx('text-sm', className)}>
      {label && (
        <div className="flex items-center gap-24 justify-between">
          <div id={labelId} className={inputFieldClassNames.label}>
            {label}
          </div>
          {removeButton}
        </div>
      )}
      {description && (
        <div className={inputFieldClassNames.description}>{description}</div>
      )}
      <div aria-labelledby={labelId} aria-describedby={descriptionId}>
        <Field
          fieldClassNames={inputFieldClassNames}
          errorMessage={errorMessage}
          invalid={invalid}
        >
          <FileInputField
            inputFieldClassNames={inputFieldClassNames}
            currentValue={currentValue}
            currentEntry={currentEntry}
            handleUpload={handleUpload}
          >
            <input
              ref={inputRef}
              aria-labelledby={labelId}
              aria-describedby={descriptionId}
              // if file is already uploaded (from form or via props) set
              // required to false, otherwise farm validation will always fail
              required={currentValue ? false : required}
              accept={allowedFileTypes?.join(',')}
              type="file"
              disabled={uploadStatus === 'inProgress'}
              className="sr-only"
              onChange={e => {
                if (e.target.files?.length) {
                  uploadFile(e.target.files[0], uploadOptions);
                }
              }}
            />
          </FileInputField>
          {uploadStatus === 'inProgress' && (
            <ProgressBar
              className="absolute top-0 left-0 right-0"
              size="xs"
              value={percentage}
            />
          )}
        </Field>
      </div>
    </div>
  );
}

interface FileInputFieldProps {
  children: ReactElement<ComponentPropsWithRef<'input'>>;
  inputFieldClassNames: InputFieldStyle;
  currentValue?: string;
  currentEntry?: FileEntry;
  handleUpload: () => void;
}
function FileInputField({
  children,
  inputFieldClassNames,
  currentValue,
  currentEntry,
  handleUpload,
}: FileInputFieldProps) {
  const buttonRef = useRef<HTMLButtonElement>(null);
  if (currentValue) {
    return (
      <Field
        wrapperProps={{
          onClick: () => {
            buttonRef.current?.focus();
            buttonRef.current?.click();
          },
        }}
        fieldClassNames={inputFieldClassNames}
      >
        <Input className={clsx(inputFieldClassNames.input, 'gap-10')}>
          <button
            ref={buttonRef}
            type="button"
            className="flex-shrink-0 bg-primary text-on-primary rounded text-sm font-semibold px-10 py-2 outline-none"
            onClick={() => handleUpload()}
          >
            <Trans message="Replace file" />
          </button>
          <AnimatePresence initial={false} mode="wait">
            <div className="min-w-0 whitespace-nowrap overflow-hidden overflow-ellipsis">
              {currentEntry ? (
                <m.div key="file-entry-name" {...opacityAnimation}>
                  {currentEntry.name}
                </m.div>
              ) : (
                <m.div key="skeleton" {...opacityAnimation}>
                  <Skeleton className="min-w-144" />
                </m.div>
              )}
            </div>
          </AnimatePresence>
          {children}
        </Input>
      </Field>
    );
  }

  return cloneElement(children, {
    className: clsx(
      inputFieldClassNames.input,
      'py-8',
      'file:bg-primary file:text-on-primary file:border-none file:rounded file:text-sm file:font-semibold file:px-10 file:h-24 file:mr-10'
    ),
  });
}

interface FormFileEntryFieldProps extends Props {
  name: string;
}
export function FormFileEntryField(props: FormFileEntryFieldProps) {
  const {
    field: {onChange, value = null},
    fieldState: {error},
  } = useController({
    name: props.name,
  });

  const formProps: Partial<Props> = {
    onChange,
    value,
    invalid: error != null,
    errorMessage: error ? <Trans message="Please select a file." /> : null,
  };

  return <FileEntryField {...mergeProps(formProps, props)} />;
}

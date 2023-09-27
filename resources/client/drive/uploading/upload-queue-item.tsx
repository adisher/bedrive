import {
  ComponentPropsWithoutRef,
  CSSProperties,
  memo,
  ReactElement,
  useMemo,
  useState,
} from 'react';
import {AnimatePresence, m} from 'framer-motion';
import {FileTypeIcon} from '@common/uploads/file-type-icon/file-type-icon';
import {prettyBytes} from '@common/uploads/utils/pretty-bytes';
import {IconButton} from '@common/ui/buttons/icon-button';
import {CloseIcon} from '@common/icons/material/Close';
import {ProgressCircle} from '@common/ui/progress/progress-circle';
import {CheckCircleIcon} from '@common/icons/material/CheckCircle';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {useFileUploadStore} from '@common/uploads/uploader/file-upload-provider';
import {Trans} from '@common/i18n/trans';
import {MixedText} from '@common/i18n/mixed-text';
import {Tooltip} from '@common/ui/tooltip/tooltip';
import {ErrorIcon} from '@common/icons/material/Error';
import {WarningIcon} from '@common/icons/material/Warning';
import {message} from '@common/i18n/message';

interface UploadQueueProps {
  file: UploadedFile;
  style: CSSProperties;
}
export const UploadQueueItem = memo(({file, style}: UploadQueueProps) => {
  return (
    <div
      className="p-10 flex items-center gap-14 w-full absolute top-0 left-0"
      style={style}
    >
      <div className="shrink-0 border rounded p-8">
        <FileTypeIcon className="w-22 h-22" mime={file.mime} />
      </div>
      <div className="flex-auto min-w-0 pr-10">
        <div className="mb-2 flex items-center min-w-0 gap-10">
          <div className="flex-auto font-medium whitespace-nowrap min-w-0 overflow-hidden overflow-ellipsis">
            {file.name}
          </div>
        </div>
        <SizeInfo file={file} />
      </div>
      <div className="mr-10">
        <FileStatus file={file} />
      </div>
    </div>
  );
});

interface SizeInfoProps {
  file: UploadedFile;
}
function SizeInfo({file}: SizeInfoProps) {
  const fileUpload = useFileUploadStore(s => s.fileUploads.get(file.id));
  const bytesUploaded = fileUpload?.bytesUploaded || 0;

  const totalBytes = useMemo(() => prettyBytes(file.size), [file]);
  const uploadedBytes = useMemo(
    () => prettyBytes(bytesUploaded),
    [bytesUploaded]
  );

  let statusMessage: ReactElement;
  if (fileUpload?.status === 'completed') {
    statusMessage = <Trans message="Upload complete" />;
  } else if (fileUpload?.status === 'aborted') {
    statusMessage = <Trans message="Upload cancelled" />;
  } else if (fileUpload?.status === 'failed') {
    statusMessage = <Trans message="Upload failed" />;
  } else {
    statusMessage = (
      <Trans
        message=":bytesUploaded of :totalBytes"
        values={{
          bytesUploaded: uploadedBytes,
          totalBytes,
        }}
      />
    );
  }

  return <div className="text-muted text-xs">{statusMessage}</div>;
}

interface FileStatusProps {
  file: UploadedFile;
}
function FileStatus({file}: FileStatusProps) {
  const fileUpload = useFileUploadStore(s => s.fileUploads.get(file.id));
  const abortUpload = useFileUploadStore(s => s.abortUpload);
  const percentage = fileUpload?.percentage || 0;
  const status = fileUpload?.status;
  const errorMessage = fileUpload?.errorMessage;
  const [isHovered, setIsHovered] = useState(false);

  const abortButton = (
    <IconButton
      size="sm"
      onClick={() => {
        abortUpload(file.id);
      }}
    >
      <CloseIcon />
    </IconButton>
  );

  const progressButton = (
    <ProgressCircle aria-label="Upload progress" size="sm" value={percentage} />
  );

  let statusButton: ReactElement;
  if (status === 'failed') {
    const errMessage =
      errorMessage || message('This file could not be uploaded');
    statusButton = (
      <AnimatedStatus>
        <Tooltip variant="danger" label={<MixedText value={errMessage} />}>
          <ErrorIcon className="text-danger" size="md" />
        </Tooltip>
      </AnimatedStatus>
    );
  } else if (status === 'aborted') {
    statusButton = (
      <AnimatedStatus>
        <WarningIcon className="text-warning" size="md" />
      </AnimatedStatus>
    );
  } else if (status === 'completed') {
    statusButton = (
      <AnimatedStatus>
        <CheckCircleIcon size="md" className="text-positive" />
      </AnimatedStatus>
    );
  } else {
    statusButton = (
      <AnimatedStatus
        onPointerEnter={e => {
          if (e.pointerType === 'mouse') {
            setIsHovered(true);
          }
        }}
        onPointerLeave={e => {
          if (e.pointerType === 'mouse') {
            setIsHovered(false);
          }
        }}
      >
        {isHovered ? abortButton : progressButton}
      </AnimatedStatus>
    );
  }

  return <AnimatePresence>{statusButton}</AnimatePresence>;
}

interface AnimatedStatusProps
  extends Omit<
    ComponentPropsWithoutRef<'div'>,
    'onAnimationStart' | 'onDragStart' | 'onDragEnd' | 'onDrag'
  > {
  children: ReactElement;
}
function AnimatedStatus({children, ...domProps}: AnimatedStatusProps) {
  return (
    <m.div
      {...domProps}
      initial={{scale: 0, opacity: 0}}
      animate={{scale: 1, opacity: 1}}
      exit={{scale: 0, opacity: 0}}
    >
      {children}
    </m.div>
  );
}

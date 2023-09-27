import {useFileUploadStore} from '@common/uploads/uploader/file-upload-provider';
import {useCallback} from 'react';
import {driveState, useDriveStore} from '../drive-store';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {UploadStrategyConfig} from '@common/uploads/uploader/strategy/upload-strategy';
import {queryClient} from '@common/http/query-client';
import {DriveQueryKeys, invalidateEntryQueries} from '../drive-query-keys';
import {useActiveWorkspaceId} from '@common/workspace/active-workspace-id-context';
import {useStorageSummary} from '../layout/sidebar/storage-summary/storage-summary';
import {toast} from '@common/ui/toast/toast';
import {message} from '@common/i18n/message';
import {useSettings} from '@common/core/settings/use-settings';

export type UploadFilesFn = (
  files: (File | UploadedFile)[] | FileList,
  options?: UploadStrategyConfig
) => void;

const EightMB = 8388608;

export function useDriveUploadQueue() {
  const uploadMultiple = useFileUploadStore(s => s.uploadMultiple);
  const activePage = useDriveStore(s => s.activePage);
  const {data: usage} = useStorageSummary();
  const {workspaceId} = useActiveWorkspaceId();
  const parentId = activePage?.folder?.id || null;

  const {uploads} = useSettings();

  const maxFileSize = uploads.max_size || EightMB;
  const allowedFileTypes = uploads.allowed_extensions;
  const blockedFileTypes = uploads.blocked_extensions;

  const uploadFiles: UploadFilesFn = useCallback(
    (files, options = {}) => {
      if (!options.metadata) {
        options.metadata = {};
      }
      options.metadata.workspaceId = workspaceId;
      if (!options.metadata.parentId) {
        options.metadata.parentId = parentId;
      }

      files = [...files].map(file => {
        return file instanceof UploadedFile ? file : new UploadedFile(file);
      });

      // check if this upload will not put user over their allowed storage space
      if (usage) {
        const sizeOfFiles = files.reduce((sum, file) => sum + file.size, 0);
        const currentlyUsing = usage.used;
        const availableSpace = usage.available;

        if (sizeOfFiles + currentlyUsing > availableSpace) {
          toast.danger(
            message(
              'You have exhausted your allowed space of :space. Delete some files or upgrade your plan.',
              {values: {space: usage.availableFormatted}}
            ),
            {action: {action: '/pricing', label: message('Upgrade')}}
          );
          return;
        }
      }

      uploadMultiple(files, {
        ...options,
        restrictions: {
          maxFileSize,
          allowedFileTypes,
          blockedFileTypes,
        },
        onSuccess: (entry, file) => {
          options?.onSuccess?.(entry, file);
          invalidateEntryQueries();
          queryClient.invalidateQueries(DriveQueryKeys.fetchStorageSummary);
        },
      });
      driveState().setUploadQueueIsOpen(true);
    },
    [
      uploadMultiple,
      parentId,
      workspaceId,
      allowedFileTypes,
      blockedFileTypes,
      maxFileSize,
      usage,
    ]
  );
  return {uploadFiles};
}

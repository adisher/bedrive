import {EntryAction} from './entry-action';
import {message} from '@common/i18n/message';
import {driveState, useDriveStore} from '../drive-store';
import {DrivePage, TrashPage} from '../drive-page/drive-page';
import {CreateNewFolderIcon} from '@common/icons/material/CreateNewFolder';
import {useDriveUploadQueue} from '../uploading/use-drive-upload-queue';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';
import {FileUploadIcon} from '@common/icons/material/FileUpload';
import {DriveFolderUploadIcon} from '@common/icons/material/DriveFolderUpload';
import {useDeleteEntries} from '../files/queries/use-delete-entries';
import {DeleteForeverIcon} from '@common/icons/material/DeleteForever';

export function useDrivePageActions(page: DrivePage): EntryAction[] {
  const newFolder = useNewFolderAction(page);
  const uploadFiles = useUploadFilesAction(page);
  const uploadFolder = useUploadFolderAction(page);
  const emptyTrash = useEmptyTrashAction(page);
  return [newFolder, uploadFiles, uploadFolder, emptyTrash].filter(
    action => !!action
  ) as EntryAction[];
}

function useNewFolderAction(page: DrivePage): EntryAction | undefined {
  if (!page.folder || !page.folder.permissions['files.update']) return;
  return {
    label: message('New folder'),
    icon: CreateNewFolderIcon,
    key: 'newFolder',
    execute: () => {
      if (page.folder) {
        driveState().setActiveActionDialog('newFolder', [page.folder]);
      }
    },
  };
}

function useUploadFilesAction(page: DrivePage): EntryAction | undefined {
  const {uploadFiles} = useDriveUploadQueue();
  if (!page.folder || !page.folder.permissions['files.update']) return;
  return {
    label: message('Upload files'),
    icon: FileUploadIcon,
    key: 'uploadFiles',
    execute: async () => {
      uploadFiles(await openUploadWindow({multiple: true}));
    },
  };
}

function useUploadFolderAction(page: DrivePage): EntryAction | undefined {
  const {uploadFiles} = useDriveUploadQueue();
  if (!page.folder || !page.folder.permissions['files.update']) return;
  return {
    label: message('Upload folder'),
    icon: DriveFolderUploadIcon,
    key: 'uploadFolder',
    execute: async () => {
      uploadFiles(await openUploadWindow({directory: true}));
    },
  };
}

function useEmptyTrashAction(page: DrivePage): EntryAction | undefined {
  const deleteEntries = useDeleteEntries();
  const activePage = useDriveStore(s => s.activePage);
  if (activePage !== TrashPage) return;
  return {
    label: message('Empty trash'),
    icon: DeleteForeverIcon,
    key: 'emptyTrash',
    execute: () => {
      deleteEntries.mutate({entryIds: [], emptyTrash: true});
      driveState().selectEntries([]);
    },
  };
}

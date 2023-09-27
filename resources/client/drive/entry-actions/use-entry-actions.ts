import {message} from '@common/i18n/message';
import {PersonAddIcon} from '@common/icons/material/PersonAdd';
import {driveState, useDriveStore} from '../drive-store';
import {EntryAction} from './entry-action';
import {DriveEntry} from '../files/drive-entry';
import {RemoveRedEyeIcon} from '@common/icons/material/RemoveRedEye';
import {LinkIcon} from '@common/icons/material/Link';
import {useCreateShareableLink} from '../shareable-link/queries/create-shareable-link';
import {StarIcon} from '@common/icons/material/Star';
import {useAddStarToEntries} from '../files/queries/use-add-star-to-entries';
import {StarOutlineIcon} from '@common/icons/material/StarOutline';
import {useRemoveStarFromEntries} from '../files/queries/use-remove-star-from-entries';
import {DriveFileMoveIcon} from '@common/icons/material/DriveFileMove';
import {DriveFileRenameOutlineIcon} from '@common/icons/material/DriveFileRenameOutline';
import {ContentCopyIcon} from '@common/icons/material/ContentCopy';
import {useDuplicateEntries} from '../files/queries/use-duplicate-entries';
import {FileDownloadIcon} from '@common/icons/material/FileDownload';
import {DeleteIcon} from '@common/icons/material/Delete';
import {SharesPage, TrashPage} from '../drive-page/drive-page';
import {useDeleteEntries} from '../files/queries/use-delete-entries';
import {useUnshareEntries} from '../share-dialog/queries/use-unshare-entries';
import {toast} from '@common/ui/toast/toast';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';
import {useFileEntryUrls} from '@common/uploads/hooks/file-entry-urls';
import {downloadFileFromUrl} from '@common/uploads/utils/download-file-from-url';
import {useRestoreEntries} from '../files/queries/use-restore-entries';
import {RestoreIcon} from '@common/icons/material/Restore';

export function useEntryActions(entries: DriveEntry[]): EntryAction[] {
  const preview = usePreviewAction(entries);
  const share = useShareAction(entries);
  const getLink = useGetLinkAction(entries);
  const addStar = useAddToStarredAction(entries);
  const removeStar = useRemoveFromStarred(entries);
  const moveTo = useMoveToAction(entries);
  const rename = useRenameAction(entries);
  const makeCopy = useMakeCopyAction(entries);
  const download = useDownloadEntriesAction(entries);
  const deleteAction = useDeleteEntriesAction(entries);
  const removeSharedEntries = useRemoveSharedEntriesAction(entries);
  const restoreEntries = useRestoreEntriesAction(entries);

  return [
    preview,
    share,
    getLink,
    addStar,
    removeStar,
    moveTo,
    rename,
    makeCopy,
    download,
    deleteAction,
    removeSharedEntries,
    restoreEntries,
  ].filter(action => !!action) as EntryAction[];
}

export function usePreviewAction(
  entries: DriveEntry[]
): EntryAction | undefined {
  if (!entries.some(e => e.type !== 'folder')) return;
  return {
    label: message('Preview'),
    icon: RemoveRedEyeIcon,
    key: 'preview',
    execute: () => {
      driveState().setActiveActionDialog('preview', entries);
    },
  };
}

export function useShareAction(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  if (
    entries.length > 1 ||
    !entries.every(e => e.permissions['files.update']) ||
    activePage === TrashPage
  )
    return;

  return {
    label: message('Share'),
    icon: PersonAddIcon,
    key: 'share',
    execute: () => {
      driveState().setActiveActionDialog('share', entries);
    },
  };
}

function useGetLinkAction(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  const createLink = useCreateShareableLink();
  if (
    entries.length > 1 ||
    !entries.every(e => e.permissions['files.update']) ||
    activePage === TrashPage
  ) {
    return;
  }
  return {
    label: message('Get link'),
    icon: LinkIcon,
    key: 'getLink',
    execute: () => {
      createLink.mutate({entryId: entries[0].id});
      driveState().setActiveActionDialog('getLink', entries);
    },
  };
}

function useAddToStarredAction(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  const starEntries = useAddStarToEntries();
  if (
    entries.every(e => e.tags?.find(tag => tag.name === 'starred')) ||
    !entries.every(e => e.permissions['files.update']) ||
    activePage === TrashPage
  ) {
    return;
  }
  return {
    label: message('Add to starred'),
    icon: StarIcon,
    key: 'addToStarred',
    execute: () => {
      starEntries.mutate({entryIds: entries.map(e => e.id)});
      driveState().selectEntries([]);
    },
  };
}

function useRemoveFromStarred(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  const removeStar = useRemoveStarFromEntries();
  if (
    !entries.every(e => e.tags?.find(tag => tag.name === 'starred')) ||
    activePage === TrashPage
  )
    return;
  return {
    label: message('Remove from starred'),
    icon: StarOutlineIcon,
    key: 'removeFromStarred',
    execute: () => {
      removeStar.mutate({entryIds: entries.map(e => e.id)});
      driveState().selectEntries([]);
    },
  };
}

function useMoveToAction(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  if (
    !entries.every(e => e.permissions['files.update']) ||
    activePage === SharesPage ||
    activePage === TrashPage
  ) {
    return;
  }

  return {
    label: message('Move to'),
    icon: DriveFileMoveIcon,
    key: 'moveTo',
    execute: () => {
      driveState().setActiveActionDialog('moveTo', entries);
    },
  };
}

function useRenameAction(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  if (
    entries.length > 1 ||
    !entries.every(e => e.permissions['files.update']) ||
    activePage === TrashPage
  )
    return;
  return {
    label: message('Rename'),
    icon: DriveFileRenameOutlineIcon,
    key: 'rename',
    execute: () => {
      driveState().setActiveActionDialog('rename', entries);
    },
  };
}

function useMakeCopyAction(entries: DriveEntry[]): EntryAction | undefined {
  const activePage = useDriveStore(s => s.activePage);
  const duplicateEntries = useDuplicateEntries();
  if (
    entries.length > 1 ||
    !entries.every(e => e.permissions['files.update']) ||
    activePage === TrashPage
  )
    return;
  return {
    label: message('Make a copy'),
    icon: ContentCopyIcon,
    key: 'makeCopy',
    execute: () => {
      duplicateEntries.mutate({entryIds: entries.map(e => e.id)});
      driveState().selectEntries([]);
    },
  };
}

function useDownloadEntriesAction(
  entries: DriveEntry[]
): EntryAction | undefined {
  const {downloadUrl} = useFileEntryUrls(entries[0], {
    downloadHashes: entries.map(e => e.hash),
  });
  if (!entries.every(e => e.permissions['files.download'])) return;
  return {
    label: message('Download'),
    icon: FileDownloadIcon,
    key: 'download',
    execute: () => {
      if (downloadUrl) {
        downloadFileFromUrl(downloadUrl);
      }
      driveState().selectEntries([]);
    },
  };
}

export function useDeleteEntriesAction(
  entries: DriveEntry[]
): EntryAction | undefined {
  const deleteEntries = useDeleteEntries();
  const activePage = useDriveStore(s => s.activePage);
  if (
    activePage === SharesPage ||
    !entries.every(e => e.permissions['files.delete'])
  )
    return;
  return {
    label:
      activePage === TrashPage ? message('Delete forever') : message('Remove'),
    icon: DeleteIcon,
    key: 'delete',
    execute: () => {
      if (activePage === TrashPage) {
        driveState().setActiveActionDialog('confirmAndDeleteForever', entries);
      } else {
        deleteEntries.mutate({
          entryIds: entries.map(e => e.id),
          deleteForever: activePage === TrashPage,
        });
        driveState().selectEntries([]);
      }
    },
  };
}

export function useRestoreEntriesAction(
  entries: DriveEntry[]
): EntryAction | undefined {
  const restoreEntries = useRestoreEntries();
  const activePage = useDriveStore(s => s.activePage);
  if (
    activePage !== TrashPage ||
    !entries.every(e => e.permissions['files.delete'])
  )
    return;
  return {
    label: message('Restore'),
    icon: RestoreIcon,
    key: 'restore',
    execute: () => {
      restoreEntries.mutate({
        entryIds: entries.map(e => e.id),
      });
      driveState().selectEntries([]);
    },
  };
}

export function useRemoveSharedEntriesAction(
  entries: DriveEntry[]
): EntryAction | undefined {
  const unshareEntries = useUnshareEntries();
  const activePage = useDriveStore(s => s.activePage);
  if (activePage !== SharesPage) return;
  return {
    label: message('Remove'),
    icon: DeleteIcon,
    key: 'removeSharedEntry',
    execute: () => {
      unshareEntries.mutate(
        {entryIds: entries.map(e => e.id), userId: 'me'},
        {
          onSuccess: (r, p) => {
            toast(
              message('Removed [one 1 item|other {count} items]', {
                values: {count: p.entryIds.length},
              })
            );
          },
          onError: err =>
            showHttpErrorToast(err, message('Could not remove items')),
        }
      );
      driveState().selectEntries([]);
    },
  };
}

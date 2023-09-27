import React, {ReactElement} from 'react';
import {ActiveActionDialog, driveState, useDriveStore} from '../../drive-store';
import {RenameEntryDialog} from './rename-entry-dialog';
import {NewFolderDialog} from './new-folder-dialog';
import {EntryPreviewDialog} from './entry-preview-dialog';
import {ShareDialog} from '../../share-dialog/share-dialog';
import {MoveEntriesDialog} from './move-entries-dialog/move-entries-dialog';
import {DialogTrigger} from '@common/ui/overlays/dialog/dialog-trigger';
import {DeleteEntriesForeverDialog} from './delete-entries-forever-dialog';
import {BlockTrashFolderViewDialog} from './block-trash-folder-view-dialog';

export function DriveDialogsContainer() {
  const activeDialog = useDriveStore(s => s.activeActionDialog);
  const dialog = getDialog(activeDialog);

  return (
    <DialogTrigger
      type="modal"
      isOpen={!!dialog}
      onClose={() => {
        driveState().setActiveActionDialog(null);
      }}
    >
      {dialog}
    </DialogTrigger>
  );
}

function getDialog(dialog?: ActiveActionDialog | null): ReactElement | null {
  switch (dialog?.name) {
    case 'rename':
      return <RenameEntryDialog entries={dialog.entries} />;
    case 'newFolder':
      return <NewFolderDialog parentId={dialog.entries[0]?.id} />;
    case 'preview':
      return <EntryPreviewDialog selectedEntry={dialog.entries[0]} />;
    case 'share':
      return <ShareDialog entry={dialog.entries[0]} />;
    case 'getLink':
      return <ShareDialog entry={dialog.entries[0]} focusLinkInput />;
    case 'moveTo':
      return <MoveEntriesDialog entries={dialog.entries} />;
    case 'confirmAndDeleteForever':
      return <DeleteEntriesForeverDialog entries={dialog.entries} />;
    case 'trashFolderBlock':
      return <BlockTrashFolderViewDialog entries={dialog.entries} />;
    default:
      return null;
  }
}

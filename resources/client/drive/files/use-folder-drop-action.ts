import {FileEntry} from '@common/uploads/file-entry';
import {useMoveEntries} from './queries/use-move-entries';
import {useDriveUploadQueue} from '../uploading/use-drive-upload-queue';
import {canMoveEntriesInto} from './utils/can-move-entries-into';
import {
  ConnectedDraggable,
  MixedDraggable,
} from '@common/ui/interactions/dnd/use-draggable';
import {NativeFileDraggable} from '@common/ui/interactions/dnd/use-droppable';
import {driveState} from '@app/drive/drive-store';

export function useFolderDropAction(folder: FileEntry) {
  const moveEntries = useMoveEntries();
  const {uploadFiles} = useDriveUploadQueue();

  const onDrop = async (target: ConnectedDraggable | NativeFileDraggable) => {
    if (folder.type !== 'folder') return;
    if (target.type === 'nativeFile') {
      uploadFiles(await target.getData(), {
        metadata: {parentId: folder.id},
      });
    } else if (target.type === 'fileEntry') {
      const entries = target.getData() as FileEntry[];
      if (entries?.length && canMoveEntriesInto(entries, folder)) {
        moveEntries.mutate({
          destinationId: folder.id,
          entryIds: entries.map(e => e.id),
        });
        driveState().deselectEntries('all');
      }
    }
  };

  return {onDrop};
}

export function folderAcceptsDrop(target: MixedDraggable, entry: FileEntry) {
  if (target.type === 'fileEntry') {
    const entries = target.getData() as FileEntry[];
    return canMoveEntriesInto(entries, entry);
  }
  return true;
}

import {
  ConnectedDraggable,
  useDraggable,
} from '@common/ui/interactions/dnd/use-draggable';
import {driveState, useDriveStore} from '../drive-store';
import {getSelectedEntries} from '../files/use-selected-entries';
import {useDroppable} from '@common/ui/interactions/dnd/use-droppable';
import {
  folderAcceptsDrop,
  useFolderDropAction,
} from '../files/use-folder-drop-action';
import {DriveEntry} from '../files/drive-entry';
import {useRef, useState} from 'react';
import clsx from 'clsx';
import {useIsTouchDevice} from '@common/utils/hooks/is-touch-device';
import {FileEntry} from '@common/uploads/file-entry';
import {useMouseSelectable} from '@common/ui/interactions/dnd/mouse-selection/use-mouse-selectable';
import {TrashPage} from '@app/drive/drive-page/drive-page';

export function useFileViewDnd<T extends HTMLElement = HTMLElement>(
  entry: DriveEntry
) {
  const isTouchDevice = useIsTouchDevice();
  const ref = useRef<T>(null);
  const {onDrop} = useFolderDropAction(entry);
  const [isDragOver, setIsDragOver] = useState(false);
  const isDragging = useDriveStore(s =>
    s.entriesBeingDragged.includes(entry.id)
  );
  const activePage = useDriveStore(s => s.activePage);

  const {draggableProps} = useDraggable({
    disabled: !!isTouchDevice || activePage === TrashPage,
    id: entry.id,
    type: 'fileEntry',
    ref,
    hidePreview: true,
    onDragStart: (e, target: ConnectedDraggable<FileEntry[]>) => {
      if (!driveState().selectedEntries.has(entry.id)) {
        driveState().selectEntries([entry.id]);
      }
      driveState().setEntriesBeingDragged(target.getData().map(e => e.id));
    },
    onDragEnd: () => {
      driveState().setEntriesBeingDragged([]);
    },
    getData: () => getSelectedEntries(),
  });

  const {droppableProps} = useDroppable<T>({
    id: entry.id,
    disabled: isTouchDevice || entry.type !== 'folder',
    ref,
    types: ['fileEntry', 'nativeFile'],
    acceptsDrop: target => folderAcceptsDrop(target, entry),
    onDragEnter: () => setIsDragOver(true),
    onDragLeave: () => setIsDragOver(false),
    onDrop,
  });

  useMouseSelectable({
    id: entry.id,
    ref,
    onSelected: () => {
      driveState().selectEntries([entry.id], true);
    },
    onDeselected: () => {
      driveState().deselectEntries([entry.id]);
    },
  });

  const itemClassName = clsx(
    isDragging && 'opacity-20',
    isDragOver && 'ring ring-offset-4 ring-primary bg-primary-light/10 rounded'
  );

  return {
    draggableProps,
    droppableProps,
    isDragOver,
    isDragging,
    itemClassName,
    ref,
  };
}

import {useDroppable} from '@common/ui/interactions/dnd/use-droppable';
import {driveState} from '../../drive-store';
import {RefObject, useState} from 'react';
import {
  folderAcceptsDrop,
  useFolderDropAction,
} from '../../files/use-folder-drop-action';
import {DriveFolder} from '../../files/drive-entry';
import {makeFolderTreeDragId} from './folder-tree-drag-id';

interface Props {
  folder: DriveFolder;
  ref: RefObject<HTMLDivElement>;
}
export function useSidebarTreeDropTarget({folder, ref}: Props) {
  const [isDragOver, setIsDragOver] = useState(false);
  const {onDrop} = useFolderDropAction(folder);

  const dropProps = useDroppable({
    id: makeFolderTreeDragId(folder),
    ref,
    types: ['fileEntry', 'nativeFile'],
    acceptsDrop: draggable => folderAcceptsDrop(draggable, folder),
    onDragEnter: draggable => {
      if (folderAcceptsDrop(draggable, folder)) {
        setIsDragOver(true);
      }
    },
    onDragLeave: () => {
      setIsDragOver(false);
    },
    onDropActivate: () => {
      if (!driveState().sidebarExpandedKeys.includes(folder.id)) {
        driveState().setSidebarExpandedKeys([
          ...driveState().sidebarExpandedKeys,
          folder.id,
        ]);
      }
    },
    onDrop,
  });

  return {...dropProps, isDragOver};
}

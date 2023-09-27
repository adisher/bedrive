import React, {useMemo, useRef} from 'react';
import {arrayToTree} from 'performant-array-to-tree';
import {useFolders} from '../../files/queries/use-folders';
import {DriveFolder} from '../../files/drive-entry';
import {driveState, useDriveStore} from '../../drive-store';
import {FolderIcon} from '@common/icons/material/Folder';
import {getPathForFolder, RootFolderPage} from '../../drive-page/drive-page';
import {mergeProps} from '@react-aria/utils';
import {
  ConnectedDraggable,
  useDraggable,
} from '@common/ui/interactions/dnd/use-draggable';
import {useSidebarTreeDropTarget} from './use-sidebar-tree-drop-target';
import {makeFolderTreeDragId} from './folder-tree-drag-id';
import {FileEntry} from '@common/uploads/file-entry';
import clsx from 'clsx';
import {BackupIcon} from '@common/icons/material/Backup';
import {TreeItem, TreeItemProps} from '@common/ui/tree/tree-item';
import {Tree} from '@common/ui/tree/tree';
import {useNavigate} from '@common/utils/hooks/use-navigate';

interface TreeFolder extends DriveFolder {
  children: TreeFolder[];
}

export function FolderTree() {
  const navigate = useNavigate();
  const {data} = useFolders();
  const expandedKeys = useDriveStore(s => s.sidebarExpandedKeys);

  const activePage = useDriveStore(s => s.activePage);
  let selectedKeys: number[] = [];
  if (activePage?.isFolderPage) {
    selectedKeys = activePage.folder ? [activePage.folder.id] : [];
  }

  const tree = useMemo(() => {
    const folders = arrayToTree(data?.folders || [], {
      parentId: 'parent_id',
      dataField: null,
    }) as TreeFolder[];
    const rootFolder = {
      ...RootFolderPage.folder,
      children: folders,
    };
    return [rootFolder];
  }, [data?.folders]);

  return (
    <Tree
      nodes={tree}
      expandedKeys={expandedKeys}
      onExpandedKeysChange={keys => {
        driveState().setSidebarExpandedKeys(keys);
      }}
      selectedKeys={selectedKeys}
      onSelectedKeysChange={([id]) => {
        const entryHash = findHash(id as number, tree);
        if (entryHash) {
          navigate(getPathForFolder(entryHash));
        } else {
          navigate(RootFolderPage.path);
        }
      }}
    >
      {() => <FolderTreeItem />}
    </Tree>
  );
}

// props will be passed by tree via cloneElement
function FolderTreeItem(props: Partial<TreeItemProps<TreeFolder>>) {
  const {node} = props as Required<TreeItemProps<TreeFolder>>;
  const labelRef = useRef<HTMLDivElement>(null);
  const isRootFolder = node.id === 0;
  const isDragging = useDriveStore(s =>
    s.entriesBeingDragged.includes(node.id)
  );

  const {draggableProps} = useDraggable({
    type: 'fileEntry',
    id: makeFolderTreeDragId(node),
    ref: labelRef,
    disabled: isRootFolder,
    hidePreview: true,
    onDragStart: (e, draggable) => {
      const d = draggable as ConnectedDraggable<FileEntry[]>;
      driveState().setEntriesBeingDragged(d.getData().map(e => e.id));
      driveState().selectEntries([]);
    },
    onDragEnd: () => {
      driveState().setEntriesBeingDragged([]);
    },
    getData: () => [node],
  });

  const {droppableProps, isDragOver} = useSidebarTreeDropTarget({
    folder: node,
    ref: labelRef,
  });

  return (
    <TreeItem
      {...mergeProps(draggableProps, droppableProps, props)}
      onContextMenu={e => {
        e.preventDefault();
        e.stopPropagation();
        driveState().deselectEntries('all');
        driveState().setContextMenuData({
          x: e.clientX,
          y: e.clientY,
          entry: node,
        });
      }}
      labelRef={labelRef}
      className={isRootFolder ? 'focus-visible:ring-2' : undefined}
      labelClassName={clsx(
        isDragOver && 'bg-primary/selected ring ring-2 ring-inset ring-primary',
        isDragging && 'opacity-30',
        isRootFolder && 'h-40'
      )}
      icon={
        isRootFolder ? (
          <BackupIcon size="md" className="mr-6" />
        ) : (
          <FolderIcon size="sm" className="mr-4" />
        )
      }
      label={node.name}
    />
  );
}

const findHash = (id: number, nodes: FileEntry[]): string | undefined => {
  for (const item of nodes) {
    if (item.id === id) {
      return item.hash;
    } else if (item.children) {
      const hash = findHash(id, item.children);
      if (hash) {
        return hash;
      }
    }
  }
};

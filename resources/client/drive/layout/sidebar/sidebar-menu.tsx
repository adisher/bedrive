import {CustomMenu, CustomMenuItem} from '@common/menus/custom-menu';
import {MenuPositions} from '../../menu-positions';
import React, {
  ComponentPropsWithoutRef,
  forwardRef,
  useRef,
  useState,
} from 'react';
import {MenuItemConfig} from '@common/core/settings/settings';
import clsx from 'clsx';
import {useDroppable} from '@common/ui/interactions/dnd/use-droppable';
import {FileEntry} from '@common/uploads/file-entry';
import {FolderTree} from './folder-tree';
import {useDeleteEntries} from '../../files/queries/use-delete-entries';
import {useActiveWorkspaceId} from '@common/workspace/active-workspace-id-context';

export function SidebarMenu() {
  const {workspaceId} = useActiveWorkspaceId();
  return (
    <div className="text-muted mt-26 px-12">
      <FolderTree />
      <CustomMenu
        menu={MenuPositions.DriveSidebar}
        orientation="vertical"
        gap="gap-0"
      >
        {item => {
          if (item.action === '/drive/trash') {
            return <TrashMenuItem key={item.id} item={item} />;
          }
          return <MenuItem key={item.id} item={item} />;
        }}
      </CustomMenu>
    </div>
  );
}

interface MenuItemProps extends ComponentPropsWithoutRef<'a'> {
  item: MenuItemConfig;
  className?: string;
}
export const MenuItem = forwardRef<HTMLAnchorElement, MenuItemProps>(
  ({item, className, ...domProps}, ref) => {
    return (
      <CustomMenuItem
        className={({isActive}) =>
          clsx(
            className,
            'h-40 w-full my-4 px-24 rounded',
            isActive
              ? 'text-primary font-bold bg-primary/selected cursor-default'
              : 'hover:bg-hover'
          )
        }
        item={item}
        ref={ref}
        {...domProps}
      />
    );
  }
);

interface TrashMenuItemProps {
  item: MenuItemConfig;
}
function TrashMenuItem({item}: TrashMenuItemProps) {
  const deleteEntries = useDeleteEntries();
  const [isDragOver, setIsDragOver] = useState(false);
  const ref = useRef<HTMLAnchorElement>(null);

  const {droppableProps} = useDroppable({
    id: 'trash',
    types: ['fileEntry'],
    ref,
    onDragEnter: () => {
      setIsDragOver(true);
    },
    onDragLeave: () => {
      setIsDragOver(false);
    },
    onDrop: draggable => {
      const entryIds = (draggable.getData() as FileEntry[]).map(e => e.id);
      deleteEntries.mutate({entryIds, deleteForever: false});
    },
  });
  return (
    <MenuItem
      className={clsx(isDragOver && 'bg-primary/selected')}
      ref={ref}
      {...droppableProps}
      item={item}
    />
  );
}

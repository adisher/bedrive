import {DriveEntry} from '../../files/drive-entry';
import {RowElementProps} from '@common/ui/tables/table-row';
import {mergeProps} from '@react-aria/utils';
import {useFileViewDnd} from '../use-file-view-dnd';
import clsx from 'clsx';
import React, {useContext} from 'react';
import {driveState} from '../../drive-store';
import {TableContext} from '@common/ui/tables/table-context';

export function FileTableRow({
  item,
  children,
  className,
  ...domProps
}: RowElementProps<DriveEntry>) {
  const {isCollapsedMode} = useContext(TableContext);
  const {draggableProps, droppableProps, itemClassName, ref} =
    useFileViewDnd<HTMLDivElement>(item);

  return (
    <div
      className={clsx(className, itemClassName)}
      ref={ref}
      {...mergeProps(draggableProps, droppableProps, domProps, {
        onContextMenu: (e: React.MouseEvent) => {
          e.preventDefault();
          e.stopPropagation();
          if (!isCollapsedMode) {
            if (!driveState().selectedEntries.has(item.id)) {
              driveState().selectEntries([item.id]);
            }
            driveState().setContextMenuData({x: e.clientX, y: e.clientY});
          }
        },
      })}
    >
      {children}
    </div>
  );
}

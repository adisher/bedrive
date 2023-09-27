import clsx from 'clsx';
import {useContext} from 'react';
import {TableContext} from '@common/ui/tables/table-context';

interface Props {
  index: number;
  isHeader: boolean;
}
export function useTableCellStyle({index, isHeader}: Props) {
  const {columns, cellHeight = 'h-46'} = useContext(TableContext);
  const column = columns[index];

  const userPadding = column?.padding;

  let justify = 'justify-start';
  if (column?.align === 'center') {
    justify = 'justify-center';
  } else if (column?.align === 'end') {
    justify = 'justify-end';
  }

  return clsx(
    'flex items-center overflow-hidden whitespace-nowrap overflow-ellipsis outline-none focus-visible:outline focus-visible:outline-offset-2',
    isHeader ? 'h-46' : cellHeight,
    column?.width ?? 'flex-1',
    column?.maxWidth,
    column?.minWidth,
    justify,
    userPadding,
    column?.className
  );
}

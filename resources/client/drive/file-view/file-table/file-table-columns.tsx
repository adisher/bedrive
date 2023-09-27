import {ColumnConfig} from '@common/datatable/column-config';
import {DriveEntry} from '../../files/drive-entry';
import {Trans} from '@common/i18n/trans';
import {FileThumbnail} from '@common/uploads/file-type-icon/file-thumbnail';
import {FormattedDate} from '@common/i18n/formatted-date';
import {prettyBytes} from '@common/uploads/utils/pretty-bytes';
import React, {useContext} from 'react';
import memoize from 'nano-memoize';
import {TableContext} from '@common/ui/tables/table-context';
import {Checkbox} from '@common/ui/forms/toggle/checkbox';
import {EntryActionMenuTrigger} from '@app/drive/entry-actions/entry-action-menu-trigger';
import {IconButton} from '@common/ui/buttons/icon-button';
import {MoreVertIcon} from '@common/icons/material/MoreVert';
import clsx from 'clsx';

const formatFileSize = memoize(bytes => {
  return prettyBytes(bytes);
});

export const fileTableColumns: ColumnConfig<DriveEntry>[] = [
  {
    key: 'name',
    allowsSorting: true,
    header: () => <Trans message="Name" />,
    visibleInMode: 'all',
    width: 'flex-3 min-w-200',
    body: entry => <FileNameColumn entry={entry} />,
  },
  {
    key: 'updated_at',
    allowsSorting: true,
    maxWidth: 'max-w-184',
    header: () => <Trans message="Last modified" />,
    body: user => <FormattedDate date={user.updated_at} />,
  },
  {
    key: 'file_size',
    allowsSorting: true,
    header: () => <Trans message="Size" />,
    maxWidth: 'max-w-144',
    body: entry => formatFileSize(entry.file_size) ?? '-',
  },
  {
    key: 'actions',
    hideHeader: true,
    header: () => <Trans message="Actions" />,
    align: 'end',
    width: 'w-42 flex-shrink-0',
    visibleInMode: 'all',
    body: entry => <ActionsColumn entry={entry} />,
  },
];

function FileNameColumn({entry}: {entry: DriveEntry}) {
  const {isCollapsedMode} = useContext(TableContext);
  const sizeClassName = isCollapsedMode ? 'w-30 h-30' : 'w-24 h-24';
  return (
    <div className="flex items-center gap-14">
      <FileThumbnail
        className={clsx('rounded', sizeClassName)}
        iconClassName={sizeClassName}
        file={entry}
      />
      <div className="min-w-0">
        <div className="overflow-hidden overflow-ellipsis">{entry.name}</div>
        {isCollapsedMode && (
          <div className="text-muted text-xs flex items-center mt-4">
            <FormattedDate date={entry.updated_at} />
            <div>·</div>
            <div>{formatFileSize(entry.file_size)}</div>
          </div>
        )}
      </div>
    </div>
  );
}

interface ActionsBodyProps {
  entry: DriveEntry;
}
function ActionsColumn({entry}: ActionsBodyProps) {
  const {selectedRows} = useContext(TableContext);
  return selectedRows.length ? (
    <Checkbox
      className="block mr-8"
      checked={selectedRows.includes(entry.id)}
    />
  ) : (
    <EntryActionMenuTrigger entries={[entry]}>
      <IconButton className="text-muted">
        <MoreVertIcon />
      </IconButton>
    </EntryActionMenuTrigger>
  );
}

import {useIsMobileMediaQuery} from '@common/utils/hooks/is-mobile-media-query';
import {DriveSortDescriptor} from '../../../drive-store';
import React from 'react';
import {Table} from '@common/ui/tables/table';
import {linkPageState, useLinkPageStore} from '../link-page-store';
import type {FolderPreviewGridProps} from './folder-preview-file-grid';
import {fileTableColumns} from '../../../file-view/file-table/file-table-columns';

const mobileColumns = fileTableColumns.filter(
  config => config.key !== 'updated_at'
);

export function FolderPreviewFileTable({
  entries,
  onEntrySelected,
}: FolderPreviewGridProps) {
  const sortDescriptor = useLinkPageStore(s => s.activeSort);
  const isMobile = useIsMobileMediaQuery();

  return (
    <Table
      columns={isMobile ? mobileColumns : fileTableColumns}
      data={entries}
      sortDescriptor={sortDescriptor}
      onSortChange={value => {
        linkPageState().setActiveSort(value as DriveSortDescriptor);
      }}
      onAction={(item, index) => {
        onEntrySelected(item, index);
      }}
      enableSelection={false}
    />
  );
}

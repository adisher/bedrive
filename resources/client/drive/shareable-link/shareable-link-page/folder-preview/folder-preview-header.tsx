import React from 'react';
import {FolderPreviewBreadcrumb} from './folder-preview-breadcrumb';
import {EntriesSortButton} from '../../../layout/sorting/entries-sort-button';
import {linkPageState, useLinkPageStore} from '../link-page-store';
import {IconButton} from '@common/ui/buttons/icon-button';
import {GridViewIcon} from '@common/icons/material/GridView';
import {useShareableLinkPage} from '../../queries/use-shareable-link-page';
import {DriveFolder} from '../../../files/drive-entry';

export function FolderPreviewHeader() {
  const activeSort = useLinkPageStore(s => s.activeSort);
  const {link, isFetching} = useShareableLinkPage();
  const hasEntry = link && link.entry;

  return (
    <div className="md:flex-row flex flex-col md:items-center gap-14 justify-between p-14 md:p-24 md:h-90">
      {hasEntry && (
        <FolderPreviewBreadcrumb
          link={link}
          folder={link.entry as DriveFolder}
          className="flex-auto"
        />
      )}
      {hasEntry && (
        <div className="flex items-center justify-between md:justify-start text-muted">
          <EntriesSortButton
            isDisabled={isFetching}
            descriptor={activeSort}
            onChange={value => {
              linkPageState().setActiveSort(value);
            }}
          />
          <div className="md:border-l md:pl-10 ml-10">
            <IconButton
              onClick={() => {
                linkPageState().setViewMode(
                  linkPageState().viewMode === 'grid' ? 'list' : 'grid'
                );
              }}
            >
              <GridViewIcon />
            </IconButton>
          </div>
        </div>
      )}
    </div>
  );
}

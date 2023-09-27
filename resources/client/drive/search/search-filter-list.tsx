import React, {useContext} from 'react';
import {driveSearchFilters} from './drive-search-filters';
import {useDriveStore} from '../drive-store';
import {SearchPage} from '../drive-page/drive-page';
import {TextField} from '@common/ui/forms/input-field/text-field/text-field';
import {SearchIcon} from '@common/icons/material/Search';
import {DashboardLayoutContext} from '@common/ui/layout/dashboard-layout-context';
import {FilterList} from '@common/datatable/filters/filter-list/filter-list';

const alwaysShownFilters = driveSearchFilters.map(f => f.key);

export function SearchFilterList() {
  const activePage = useDriveStore(s => s.activePage);
  const {isMobileMode} = useContext(DashboardLayoutContext);

  if (activePage !== SearchPage) {
    return null;
  }

  return (
    <div className="mt-10 mb-30 px-10 md:px-26">
      {isMobileMode && (
        <TextField
          autoFocus
          className="mb-20"
          startAdornment={<SearchIcon />}
          placeholder="Type to search"
        />
      )}
      <FilterList
        filters={driveSearchFilters}
        pinnedFilters={alwaysShownFilters}
      />
    </div>
  );
}

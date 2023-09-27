import {PageBreadcrumbs} from '../page-breadcrumbs';
import {DashboardContentHeader} from '@common/ui/layout/dashboard-content-header';
import React, {useContext} from 'react';
import {driveState, useDriveStore} from '../drive-store';
import {Trans} from '@common/i18n/trans';
import {Tooltip} from '@common/ui/tooltip/tooltip';
import {IconButton} from '@common/ui/buttons/icon-button';
import {ViewListIcon} from '@common/icons/material/ViewList';
import {ViewModuleIcon} from '@common/icons/material/ViewModule';
import {DashboardLayoutContext} from '@common/ui/layout/dashboard-layout-context';
import {InfoIcon} from '@common/icons/material/Info';
import {DriveSortButton} from './sorting/drive-sort-button';

export function DriveContentHeader() {
  const {isMobileMode} = useContext(DashboardLayoutContext);
  const activePage = useDriveStore(s => s.activePage);
  return (
    <DashboardContentHeader className="px-8 md:px-26 py-4 flex items-center gap-20 border-b h-60">
      {isMobileMode ? (
        <DriveSortButton isDisabled={activePage?.disableSort} />
      ) : (
        <PageBreadcrumbs />
      )}
      <div className="text-muted ml-auto flex-shrink-0">
        <ToggleViewModeButton />
        <ToggleDetailsButton />
      </div>
    </DashboardContentHeader>
  );
}

function ToggleViewModeButton() {
  const viewMode = useDriveStore(s => s.viewMode);
  const tooltip =
    viewMode === 'grid' ? (
      <Trans message="List view" />
    ) : (
      <Trans message="Grid view" />
    );
  return (
    <Tooltip label={tooltip}>
      <IconButton
        size="md"
        onClick={() => {
          driveState().setViewMode(
            driveState().viewMode === 'list' ? 'grid' : 'list'
          );
        }}
      >
        {viewMode === 'list' ? <ViewListIcon /> : <ViewModuleIcon />}
      </IconButton>
    </Tooltip>
  );
}

function ToggleDetailsButton() {
  const {rightSidenavStatus: status, setRightSidenavStatus} = useContext(
    DashboardLayoutContext
  );
  const tooltip = status ? (
    <Trans message="Hide details" />
  ) : (
    <Trans message="Show details" />
  );
  return (
    <Tooltip label={tooltip}>
      <IconButton
        size="md"
        color={status === 'open' ? 'primary' : null}
        onClick={() => {
          setRightSidenavStatus(status === 'open' ? 'closed' : 'open');
        }}
      >
        <InfoIcon />
      </IconButton>
    </Tooltip>
  );
}

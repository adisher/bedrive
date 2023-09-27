import React, {ReactNode, useContext} from 'react';
import {DashboardLayoutContext} from '@common/ui/layout/dashboard-layout-context';
import {FileTypeIcon} from '@common/uploads/file-type-icon/file-type-icon';
import {IconButton} from '@common/ui/buttons/icon-button';
import {CloseIcon} from '@common/icons/material/Close';

interface HeaderProps {
  entryType: string;
  entryName: ReactNode;
}
export function DetailsSidebarHeader({entryType, entryName}: HeaderProps) {
  const {setRightSidenavStatus} = useContext(DashboardLayoutContext);
  return (
    <div className="flex items-center gap-16 text-text-main mb-38">
      <FileTypeIcon className="w-24 h-24" type={entryType} />
      <div className="text-xl font-normal text-ellipsis flex-auto mr-auto min-w-0 break-words">
        {entryName}
      </div>
      <IconButton
        size="md"
        className="flex-shrink-0"
        onClick={() => {
          setRightSidenavStatus('closed');
        }}
      >
        <CloseIcon />
      </IconButton>
    </div>
  );
}

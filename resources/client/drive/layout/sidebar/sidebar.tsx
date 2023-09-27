import React from 'react';
import {StorageMeter} from './storage-summary/storage-meter';
import {WorkspaceSelector} from '@common/workspace/workspace-selector';
import {RootFolderPage} from '../../drive-page/drive-page';
import {SidebarMenu} from './sidebar-menu';
import {useNavigate} from '@common/utils/hooks/use-navigate';
import {CreateNewButton} from '../create-new-button';
import {Button} from '@common/ui/buttons/button';
import {Link} from 'react-router-dom';
import {Trans} from '@common/i18n/trans';
import {useAuth} from '@common/auth/use-auth';
import clsx from 'clsx';
import {useSettings} from '@common/core/settings/use-settings';

interface SidebarProps {
  className?: string;
}
export function Sidebar({className}: SidebarProps) {
  const {isSubscribed} = useAuth();
  const {billing} = useSettings();
  return (
    <div
      className={clsx(
        className,
        'text-sm text-muted font-medium bg-alt border-r flex flex-col gap-20'
      )}
    >
      <div className="flex-auto">
        <CreateNewButton className="text-center px-12 pt-28" />
        <SidebarMenu />
        <StorageMeter />
        {billing.enable && (
          <div className="pl-60 mt-14">
            <Button
              elementType={Link}
              to={isSubscribed ? '/billing/change-plan' : '/pricing'}
              variant="outline"
              color="primary"
              size="xs"
            >
              <Trans message="Upgrade" />
            </Button>
          </div>
        )}
      </div>
      <WorkspaceSwitcher />
    </div>
  );
}

function WorkspaceSwitcher() {
  const navigate = useNavigate();
  return (
    <WorkspaceSelector
      onChange={() => {
        navigate(RootFolderPage.path);
      }}
      className="w-full px-24 py-18 border-t flex-shrink-0 mt-auto"
    />
  );
}

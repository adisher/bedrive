import React, {ReactNode, useContext} from 'react';
import {useDriveStore} from './drive-store';
import {
  DrivePage,
  makeFolderPage,
  RootFolderPage,
  SharesPage,
  TrashPage,
} from './drive-page/drive-page';
import {Breadcrumb} from '@common/ui/breadcrumbs/breadcrumb';
import {useAuth} from '@common/auth/use-auth';
import {useFolderPath} from './files/queries/use-folder-path';
import {EntryActionMenuTrigger} from './entry-actions/entry-action-menu-trigger';
import {useActiveWorkspace} from '@common/workspace/active-workspace-id-context';
import {ButtonBase} from '@common/ui/buttons/button-base';
import {BreadcrumbItem} from '@common/ui/breadcrumbs/breadcrumb-item';
import {ArrowDropDownIcon} from '@common/icons/material/ArrowDropDown';
import {MessageDescriptor} from '@common/i18n/message-descriptor';
import {MixedText} from '@common/i18n/mixed-text';
import {useNavigate} from '@common/utils/hooks/use-navigate';
import {DashboardLayoutContext} from '@common/ui/layout/dashboard-layout-context';

interface ItemConfig {
  page: DrivePage;
  label: MessageDescriptor | string;
}

interface PageBreadcrumbsProps {
  className?: string;
}
export function PageBreadcrumbs({className}: PageBreadcrumbsProps) {
  const {isMobileMode} = useContext(DashboardLayoutContext);
  const navigate = useNavigate();
  const page = useDriveStore(s => s.activePage);
  const folder = page?.folder;
  const query = useFolderPath({
    hash: folder?.hash,
    isEnabled: folder?.hash !== RootFolderPage.folder.hash,
  });
  const workspace = useActiveWorkspace();
  const rootItem = useRootItem();
  // wait until path, folder and workspace load fully
  const isLoading =
    !page ||
    !workspace ||
    (page.isFolderPage && !folder) ||
    query.fetchStatus !== 'idle';

  let content: ReactNode;

  if (isLoading) {
    content = null;
  } else {
    const items: ItemConfig[] = rootItem ? [rootItem] : [];

    if (query.data) {
      query.data.path.forEach(parent => {
        items.push({
          page: makeFolderPage(parent),
          label: parent.name,
        });
      });
    }

    content = (
      <Breadcrumb
        className={className}
        size={isMobileMode ? 'md' : 'lg'}
        currentIsClickable
      >
        {items.map((item, index) => {
          const isLast = index === items.length - 1;
          if (!isLast) {
            return (
              <BreadcrumbItem
                key={item.page.id}
                onSelected={() => {
                  navigate(item.page.path);
                }}
              >
                <MixedText value={item.label} />
              </BreadcrumbItem>
            );
          }

          return (
            <BreadcrumbItem key={item.page.id}>
              {({isMenuItem}) => {
                if (
                  isMenuItem ||
                  (!item.page.folder && item.page !== TrashPage)
                )
                  return <MixedText value={item.label} />;
                return (
                  <EntryActionMenuTrigger page={item.page}>
                    <ButtonBase className="flex items-center gap-2 rounded focus-visible:ring-offset-4">
                      <MixedText value={item.label} />
                      <ArrowDropDownIcon className="icon-md text-muted" />
                    </ButtonBase>
                  </EntryActionMenuTrigger>
                );
              }}
            </BreadcrumbItem>
          );
        })}
      </Breadcrumb>
    );
  }

  return content;
}

function useRootItem(): ItemConfig | null {
  const page = useDriveStore(s => s.activePage);
  const workspace = useActiveWorkspace();
  const {user} = useAuth();

  if (!page) return null;

  // in workspace
  if (workspace && !workspace.default) {
    if (
      page?.isFolderPage &&
      (page?.id === RootFolderPage.id ||
        page.folder?.workspace_id === workspace.id)
    ) {
      return {label: workspace.name, page: RootFolderPage};
    }
  }

  if (page?.isFolderPage) {
    const owner = page.folder?.users.find(u => u.owns_entry);
    // inside shared folder
    if (owner?.id !== user?.id) {
      return {label: SharesPage.label, page: SharesPage};
    }
    // if folder is currently active, root item will always be root folder page
    return {label: RootFolderPage.label, page: RootFolderPage};
  }

  // if folder page is not active, we are already at the root
  return {label: page.label, page};
}

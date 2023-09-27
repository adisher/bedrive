import React, {createElement, ReactElement} from 'react';
import {
  Menu,
  MenuItem,
  MenuTrigger,
} from '@common/ui/navigation/menu/menu-trigger';
import {Trans} from '@common/i18n/trans';
import {DriveEntry} from '../files/drive-entry';
import {useEntryActions} from './use-entry-actions';
import {EntryAction} from './entry-action';
import {DrivePage, RootFolderPage, TrashPage} from '../drive-page/drive-page';
import {useDrivePageActions} from './use-drive-page-actions';

interface Props {
  children: ReactElement;
  entries?: DriveEntry[];
  page?: DrivePage;
}
export function EntryActionMenuTrigger({children, entries, page}: Props) {
  if (page?.id === RootFolderPage.id) {
    return <PageMenu page={RootFolderPage}>{children}</PageMenu>;
  }

  if (page === TrashPage) {
    return <PageMenu page={TrashPage}>{children}</PageMenu>;
  }

  if (page?.folder) {
    return <EntriesMenu entries={[page.folder]}>{children}</EntriesMenu>;
  }

  if (entries?.length) {
    return <EntriesMenu entries={entries}>{children}</EntriesMenu>;
  }

  return null;
}

interface EntriesContextMenuProps extends Omit<BaseMenuProps, 'actions'> {
  entries: DriveEntry[];
}
function EntriesMenu({entries, children}: EntriesContextMenuProps) {
  const actions = useEntryActions(entries);
  return <BaseMenu actions={actions}>{children}</BaseMenu>;
}

interface PageContextMenuProps extends Omit<BaseMenuProps, 'actions'> {
  page: DrivePage;
}
function PageMenu({page, children}: PageContextMenuProps) {
  const actions = useDrivePageActions(page);
  return <BaseMenu actions={actions}>{children}</BaseMenu>;
}

interface BaseMenuProps {
  actions: EntryAction[];
  children: ReactElement;
}
function BaseMenu({actions, children}: BaseMenuProps) {
  return (
    <MenuTrigger>
      {children}
      <Menu>
        {actions.map(action => {
          return (
            <MenuItem
              onSelected={() => {
                action.execute();
              }}
              key={action.key}
              value={action.key}
              startIcon={createElement(action.icon)}
            >
              <Trans {...action.label} />
            </MenuItem>
          );
        })}
      </Menu>
    </MenuTrigger>
  );
}

import {useSelectedEntries} from './use-selected-entries';
import {DriveEntry} from './drive-entry';
import {useEntryActions} from '../entry-actions/use-entry-actions';
import {driveState, useDriveStore} from '../drive-store';
import {ContextMenu} from '@common/ui/navigation/menu/context-menu';
import {MenuItem} from '@common/ui/navigation/menu/menu-trigger';
import React, {createElement} from 'react';
import {Trans} from '@common/i18n/trans';
import {EntryAction} from '../entry-actions/entry-action';
import {DrivePage, RootFolderPage} from '../drive-page/drive-page';
import {useDrivePageActions} from '../entry-actions/use-drive-page-actions';

export function DriveContextMenu() {
  const selectedEntries = useSelectedEntries();
  const activePage = useDriveStore(s => s.activePage);
  const data = useDriveStore(s => s.contextMenuData);
  const entries = data?.entry ? [data.entry] : selectedEntries;

  // right-clicked root folder
  if (data?.entry?.id === 0) {
    return <PageContextMenu position={data} page={RootFolderPage} />;
  }

  if (data && entries.length) {
    return <EntriesContextMenu entries={entries} position={data} />;
  }

  if (data && activePage) {
    return <PageContextMenu position={data} page={activePage} />;
  }

  return null;
}

interface EntriesContextMenuProps {
  position: {x: number; y: number};
  entries: DriveEntry[];
}
function EntriesContextMenu({entries, position}: EntriesContextMenuProps) {
  const actions = useEntryActions(entries);
  return <BaseContextMenu position={position} actions={actions} />;
}

interface PageContextMenuProps {
  position: {x: number; y: number};
  page: DrivePage;
}
function PageContextMenu({page, position}: PageContextMenuProps) {
  const actions = useDrivePageActions(page);
  return <BaseContextMenu position={position} actions={actions} />;
}

interface BaseContextMenuProps {
  position: {x: number; y: number};
  actions: EntryAction[];
}
function BaseContextMenu({position, actions}: BaseContextMenuProps) {
  return (
    <ContextMenu
      position={position}
      onOpenChange={isOpen => {
        if (!isOpen) {
          driveState().setContextMenuData(null);
        }
      }}
    >
      {actions.map(action => (
        <MenuItem
          value={action.key}
          key={action.key}
          onSelected={action.execute}
          startIcon={createElement(action.icon)}
        >
          <Trans {...action.label} />
        </MenuItem>
      ))}
    </ContextMenu>
  );
}

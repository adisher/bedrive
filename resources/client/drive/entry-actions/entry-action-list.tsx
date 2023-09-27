import React, {createElement} from 'react';
import {IconButton} from '@common/ui/buttons/icon-button';
import {EntryActionMenuTrigger} from './entry-action-menu-trigger';
import {MoreVertIcon} from '@common/icons/material/MoreVert';
import {Tooltip} from '@common/ui/tooltip/tooltip';
import {Trans} from '@common/i18n/trans';
import {useSelectedEntries} from '../files/use-selected-entries';
import {
  useDeleteEntriesAction,
  usePreviewAction,
  useRemoveSharedEntriesAction,
  useShareAction,
} from './use-entry-actions';
import {DriveEntry} from '../files/drive-entry';
import {EntryAction} from './entry-action';

interface EntryActionListProps {
  className?: string;
}
export function EntryActionList({className}: EntryActionListProps) {
  const selectedEntries = useSelectedEntries();

  if (!selectedEntries.length) {
    return null;
  }

  return (
    <div className={className}>
      <ActionList entries={selectedEntries} />
    </div>
  );
}

interface ActionListProps {
  entries: DriveEntry[];
}
function ActionList({entries}: ActionListProps) {
  const preview = usePreviewAction(entries);
  const share = useShareAction(entries);
  const deleteAction = useDeleteEntriesAction(entries);
  const removeShared = useRemoveSharedEntriesAction(entries);

  const actions = [preview, share, deleteAction, removeShared].filter(
    action => !!action
  ) as EntryAction[];

  return (
    <div className="entry-action-list">
      {actions.map(action => (
        <Tooltip key={action.key} label={<Trans {...action.label} />}>
          <IconButton
            size="sm"
            onClick={() => {
              action.execute();
            }}
          >
            {createElement(action.icon)}
          </IconButton>
        </Tooltip>
      ))}
      <EntryActionMenuTrigger entries={entries}>
        <Tooltip label={<Trans message="More actions" />}>
          <IconButton>
            <MoreVertIcon />
          </IconButton>
        </Tooltip>
      </EntryActionMenuTrigger>
    </div>
  );
}

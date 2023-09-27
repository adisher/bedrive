import {DriveEntry} from '../drive-entry';
import {ConfirmationDialog} from '@common/ui/overlays/dialog/confirmation-dialog';
import {Trans} from '@common/i18n/trans';
import {driveState} from '../../drive-store';
import {useRestoreEntries} from '../queries/use-restore-entries';

interface DeleteEntriesForeverDialogProps {
  entries: DriveEntry[];
}
export function BlockTrashFolderViewDialog({
  entries,
}: DeleteEntriesForeverDialogProps) {
  const restoreEntries = useRestoreEntries();

  return (
    <ConfirmationDialog
      title={<Trans message="This folder is in your trash" />}
      body={<Trans message="To view this folder, restore it from the trash." />}
      confirm={<Trans message="Restore" />}
      onConfirm={() => {
        restoreEntries.mutate({
          entryIds: entries.map(e => e.id),
        });
        driveState().selectEntries([]);
      }}
    />
  );
}

import {driveState, useDriveStore} from '../drive-store';
import {getAllEntries} from './queries/use-paginated-entries';
import {DriveEntry, DriveFolder} from './drive-entry';
import {useEntries} from './queries/use-entries';
import {useFolders} from './queries/use-folders';

export function useSelectedEntries(): DriveEntry[] {
  const ids = useDriveStore(s => s.selectedEntries);
  const entries = useEntries();
  return Array.from(ids)
    .map(id => entries.find(entry => entry.id === id))
    .filter(e => !!e) as DriveEntry[];
}

export function useSelectedEntry(): DriveEntry | null {
  const entries = useSelectedEntries();
  return entries[0];
}

export function useSelectedEntryParent(): DriveFolder | null | undefined {
  const entry = useSelectedEntry();
  const {data} = useFolders();
  if (!entry || !data) return;
  return data.folders.find(e => e.id === entry.parent_id) as DriveFolder;
}

export function getSelectedEntries(): DriveEntry[] {
  const ids = Array.from(driveState().selectedEntries);
  const allEntries = getAllEntries();
  return ids
    .map(id => allEntries.find(entry => entry.id === id))
    .filter(e => !!e) as DriveEntry[];
}

import {DriveEntry} from '../drive-entry';
import {usePaginatedEntries} from './use-paginated-entries';

export function useEntries(): DriveEntry[] {
  const query = usePaginatedEntries();
  if (!query.data) return [];
  return query.data.pages.flatMap(p => p.data);
}

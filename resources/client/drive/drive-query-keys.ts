import type {DriveApiIndexParams} from './files/queries/use-paginated-entries';
import type {UserFoldersApiParams} from './files/queries/use-folders';
import {queryClient} from '@common/http/query-client';
import {Key} from 'react';

export const DriveQueryKeys = {
  fetchEntries: (params?: DriveApiIndexParams) => {
    const key: ('drive-entries' | DriveApiIndexParams)[] = ['drive-entries'];
    if (params) key.push(params);
    return key;
  },
  fetchUserFolders(params?: UserFoldersApiParams) {
    const key: (string | UserFoldersApiParams)[] = ['user-folders'];
    if (params) {
      key.push(params);
    }
    return key;
  },
  fetchShareableLink: (params?: {hash?: string; sort?: string}) => {
    const key: (string | object)[] = ['shareable-link'];
    if (params) {
      key.push(params);
    }
    return key;
  },
  fetchFolderPath(
    hash?: string,
    params?: Record<string, string | number | null>
  ) {
    const key: (string | any)[] = ['folder-path'];
    if (hash) {
      key.push(hash);
    }
    if (params) {
      key.push(params);
    }
    return key;
  },
  fetchEntryShareableLink: (entryId: number) => {
    return ['file-entries', entryId, 'shareable-link'];
  },
  fetchFileEntry: (id?: number) => {
    const key: Key[] = ['drive/file-entries/model'];
    if (id) key.push(id);
    return key;
  },
  fetchStorageSummary: ['storage-summary'],
};

export function invalidateEntryQueries() {
  queryClient.invalidateQueries(DriveQueryKeys.fetchEntries());
  queryClient.invalidateQueries(DriveQueryKeys.fetchFolderPath());
  queryClient.invalidateQueries(DriveQueryKeys.fetchUserFolders());
  // fetching model for single file entry in "useFileEntry"
  queryClient.invalidateQueries(DriveQueryKeys.fetchFileEntry());
}

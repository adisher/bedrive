import {useMutation} from '@tanstack/react-query';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveEntryUser} from '../../files/drive-entry';
import {apiClient} from '@common/http/query-client';
import {invalidateEntryQueries} from '../../drive-query-keys';

interface Response extends BackendResponse {
  users: DriveEntryUser[];
}

interface Payload {
  userId: number | 'me';
  entryIds: number[];
}

export function useUnshareEntries() {
  return useMutation((payload: Payload) => unshareEntries(payload), {
    onSuccess: () => {
      invalidateEntryQueries();
    },
  });
}

function unshareEntries({entryIds, ...payload}: Payload): Promise<Response> {
  return apiClient
    .post(`file-entries/${entryIds.join(',')}/unshare`, payload)
    .then(response => response.data);
}

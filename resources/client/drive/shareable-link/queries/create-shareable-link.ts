import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {FetchShareableLinkResponse} from './use-entry-shareable-link';
import {DriveQueryKeys} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

function createShareableLink(
  entryId?: number | null
): Promise<FetchShareableLinkResponse> {
  if (!entryId) {
    return Promise.reject(new Error('Invalid entry id'));
  }
  return apiClient
    .post(`file-entries/${entryId}/shareable-link`)
    .then(response => response.data);
}

interface Payload {
  entryId: number;
}
export function useCreateShareableLink() {
  return useMutation(({entryId}: Payload) => createShareableLink(entryId), {
    onSuccess: (data, {entryId}) => {
      queryClient.setQueryData<FetchShareableLinkResponse>(
        DriveQueryKeys.fetchEntryShareableLink(entryId),
        data
      );
    },
    onError: err => showHttpErrorToast(err, message('Could not create link')),
  });
}

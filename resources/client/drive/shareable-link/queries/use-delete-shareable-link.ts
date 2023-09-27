import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {FetchShareableLinkResponse} from './use-entry-shareable-link';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveQueryKeys} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface DeleteLinkParams {
  entryId: number;
}

function deleteShareableLink({
  entryId,
}: DeleteLinkParams): Promise<BackendResponse> {
  return apiClient
    .delete(`file-entries/${entryId}/shareable-link`)
    .then(r => r.data);
}

interface Payload {
  entryId: number;
}
export function useDeleteShareableLink() {
  return useMutation(({entryId}: Payload) => deleteShareableLink({entryId}), {
    onSuccess: (response, {entryId}) => {
      queryClient.setQueryData<FetchShareableLinkResponse>(
        DriveQueryKeys.fetchEntryShareableLink(entryId),
        {...response, link: null}
      );
    },
    onError: err => showHttpErrorToast(err, message('Could not delete link')),
  });
}

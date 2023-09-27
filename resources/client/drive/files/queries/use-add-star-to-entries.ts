import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {toast} from '@common/ui/toast/toast';
import {Tag} from '@common/tags/tag';
import {invalidateEntryQueries} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {useMutation} from '@tanstack/react-query';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {
  tag: Tag;
}

interface Payload {
  entryIds: number[];
}

function addStarToEntries({entryIds}: Payload): Promise<Response> {
  return apiClient
    .post('file-entries/star', {entryIds})
    .then(response => response.data);
}

export function useAddStarToEntries() {
  return useMutation((payload: Payload) => addStarToEntries(payload), {
    onSuccess: (data, {entryIds}) => {
      invalidateEntryQueries();
      toast(
        message('Starred [one 1 item|other :count items]', {
          values: {count: entryIds.length},
        })
      );
    },
    onError: err => showHttpErrorToast(err, message('Could not star items')),
  });
}

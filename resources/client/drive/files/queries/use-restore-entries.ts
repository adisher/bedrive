import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {toast} from '@common/ui/toast/toast';
import {invalidateEntryQueries} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {}

interface Payload {
  entryIds: number[];
}

export function useRestoreEntries() {
  return useMutation((payload: Payload) => restoreEntries(payload), {
    onSuccess: (r, p) => {
      invalidateEntryQueries();
      toast(
        message('Restored [one 1 item|other :count items]', {
          values: {count: p.entryIds.length},
        })
      );
    },
    onError: err => showHttpErrorToast(err, message('Could not restore items')),
  });
}

function restoreEntries(payload: Payload): Promise<Response> {
  return apiClient
    .post('file-entries/restore', payload)
    .then(response => response.data);
}

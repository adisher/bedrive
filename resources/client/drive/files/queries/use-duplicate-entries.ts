import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveEntry} from '../drive-entry';
import {toast} from '@common/ui/toast/toast';
import {DriveQueryKeys, invalidateEntryQueries} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {
  entries: DriveEntry[];
}

interface Payload {
  entryIds: number[];
  destinationId?: number;
}

function duplicateEntries(payload: Payload): Promise<Response> {
  return apiClient
    .post('file-entries/duplicate', payload)
    .then(response => response.data);
}

export function useDuplicateEntries() {
  return useMutation(
    (payload: Payload) => {
      toast.loading(
        message('Duplicating [one 1 item|other :count items]...', {
          values: {
            count: payload.entryIds.length,
          },
        }),
        {disableExitAnimation: true}
      );
      return duplicateEntries(payload);
    },
    {
      onSuccess: (r, p) => {
        invalidateEntryQueries();
        queryClient.invalidateQueries(DriveQueryKeys.fetchStorageSummary);
        toast(
          message('Duplicated [one 1 item|other :count items]', {
            values: {count: p.entryIds.length},
          }),
          {disableEnterAnimation: true}
        );
      },
      onError: err =>
        showHttpErrorToast(err, message('Could not duplicate items'), null, {
          disableEnterAnimation: true,
        }),
    }
  );
}

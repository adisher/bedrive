import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {toast} from '@common/ui/toast/toast';
import {DriveQueryKeys, invalidateEntryQueries} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {getAxiosErrorMessage} from '@common/utils/http/get-axios-error-message';

interface Response extends BackendResponse {}

interface Payload {
  entryIds: number[];
  emptyTrash?: boolean;
  deleteForever?: boolean;
}

function deleteEntries(payload: Payload): Promise<Response> {
  return apiClient
    .post('file-entries/delete', payload)
    .then(response => response.data);
}

export function useDeleteEntries() {
  return useMutation(
    (payload: Payload) => {
      toast.loading(
        payload.emptyTrash
          ? message('Emptying trash...')
          : message('Moving to trash...'),
        {disableExitAnimation: true}
      );
      return deleteEntries(payload);
    },
    {
      onSuccess: (r, {entryIds, emptyTrash, deleteForever}) => {
        invalidateEntryQueries();
        queryClient.invalidateQueries(DriveQueryKeys.fetchStorageSummary);
        if (emptyTrash) {
          toast(message('Emptied trash'), {disableEnterAnimation: true});
        } else if (deleteForever) {
          toast(
            message('Permanently deleted [one 1 item|other :count items]', {
              values: {count: entryIds.length},
            }),
            {disableEnterAnimation: true}
          );
        } else {
          toast(
            message('Moved [one 1 item|other :count items] to trash', {
              values: {count: entryIds.length},
            }),
            {disableEnterAnimation: true}
          );
        }
      },
      onError: (err, {emptyTrash}) => {
        const backendError = getAxiosErrorMessage(err);
        if (backendError) {
          toast.danger(backendError, {disableEnterAnimation: true});
        } else if (emptyTrash) {
          toast.danger('could not empty trash', {disableEnterAnimation: true});
        } else {
          toast.danger('Could not delete items', {disableEnterAnimation: true});
        }
      },
    }
  );
}

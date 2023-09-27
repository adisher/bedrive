import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveEntry} from '../drive-entry';
import {toast} from '@common/ui/toast/toast';
import {invalidateEntryQueries} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {FileEntry} from '@common/uploads/file-entry';
import {RootFolderPage} from '../../drive-page/drive-page';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {
  entries: DriveEntry[];
  destination: FileEntry | null;
}

interface Payload {
  entryIds: number[];
  destinationId?: number | null;
}

export function useMoveEntries() {
  return useMutation(
    (payload: Payload) => {
      toast.loading(
        message('Moving [one 1 item|other :count items]...', {
          values: {
            count: payload.entryIds.length,
          },
        }),
        {disableExitAnimation: true}
      );
      return moveEntries(payload);
    },
    {
      onSuccess: (r, p) => {
        invalidateEntryQueries();
        toast(
          message('Moved [one 1 item|other :count items] to ":destination"', {
            values: {
              count: p.entryIds.length,
              destination: (r.destination || RootFolderPage.folder).name,
            },
          }),
          {disableEnterAnimation: true}
        );
      },
      onError: err =>
        showHttpErrorToast(err, message('Could not move items'), null, {
          disableEnterAnimation: true,
        }),
    }
  );
}

function moveEntries(payload: Payload): Promise<Response> {
  // backend expects null for root folder, it might be zero or empty string on frontend
  payload.destinationId = !payload.destinationId ? null : payload.destinationId;
  return apiClient
    .post('file-entries/move', payload)
    .then(response => response.data);
}

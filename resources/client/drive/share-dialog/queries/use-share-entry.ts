import axios from 'axios';
import {useMutation} from '@tanstack/react-query';
import {DriveEntryPermissions} from '../../files/drive-entry';
import {apiClient} from '@common/http/query-client';
import {toast} from '@common/ui/toast/toast';
import {invalidateEntryQueries} from '../../drive-query-keys';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {BackendErrorResponse} from '@common/errors/backend-error-response';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

export interface ShareEntryPayload {
  emails: string[];
  permissions: DriveEntryPermissions;
  entryId: number;
}

function shareEntry({
  entryId,
  ...payload
}: ShareEntryPayload): Promise<BackendResponse> {
  return apiClient
    .post(`file-entries/${entryId}/share`, payload)
    .then(response => response.data);
}

export function useShareEntry() {
  return useMutation((payload: ShareEntryPayload) => shareEntry(payload), {
    onSuccess: () => {
      invalidateEntryQueries();
    },
    onError: err => {
      if (axios.isAxiosError(err) && err.response) {
        const response = err.response.data as BackendErrorResponse;
        if (response.errors?.emails) {
          toast.danger(response.errors?.emails[0]);
        } else {
          showHttpErrorToast(err);
        }
      }
    },
  });
}

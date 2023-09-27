import {useMutation} from '@tanstack/react-query';
import {DriveEntryPermissions} from '../../files/drive-entry';
import {apiClient} from '@common/http/query-client';
import {toast} from '@common/ui/toast/toast';
import {invalidateEntryQueries} from '../../drive-query-keys';
import {message} from '@common/i18n/message';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

export interface ChangePermissionsPayload {
  userId: number;
  permissions: DriveEntryPermissions;
  entryId: number;
}

export function useChangePermission() {
  return useMutation(
    (payload: ChangePermissionsPayload) => changePermission(payload),
    {
      onSuccess: () => {
        invalidateEntryQueries();
        toast(message('Updated user permissions'));
      },
      onError: err =>
        showHttpErrorToast(err, message('Could not update permissions')),
    }
  );
}

function changePermission({
  entryId,
  ...payload
}: ChangePermissionsPayload): Promise<BackendResponse> {
  return apiClient
    .put(`file-entries/${entryId}/change-permissions`, payload)
    .then(response => response.data);
}

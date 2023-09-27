import {useMutation} from '@tanstack/react-query';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveEntryUser} from '../../../files/drive-entry';
import {toast} from '@common/ui/toast/toast';
import {useLinkPageStore} from '../link-page-store';
import {apiClient, queryClient} from '@common/http/query-client';
import {DriveQueryKeys} from '../../../drive-query-keys';
import {message} from '@common/i18n/message';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {
  users: DriveEntryUser[];
}

interface Props {
  password: string | null;
  linkId: number;
}

function importIntoOwnDrive({linkId, password}: Props): Promise<Response> {
  return apiClient
    .post(`shareable-links/${linkId}/import`, {password})
    .then(r => r.data);
}

export function useImportIntoOwnDrive() {
  const password = useLinkPageStore(s => s.password);
  return useMutation(
    (props: Omit<Props, 'password'>) =>
      importIntoOwnDrive({...props, password}),
    {
      onSuccess: () => {
        toast(message('Item imported into your drive'));
        queryClient.invalidateQueries(DriveQueryKeys.fetchShareableLink());
      },
      onError: err => showHttpErrorToast(err, message('Could not create link')),
    }
  );
}

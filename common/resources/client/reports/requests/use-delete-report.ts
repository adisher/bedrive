import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';
import {toast} from '@common/ui/toast/toast';
import {message} from '@common/i18n/message';
import {Reportable} from '@common/reports/Reportable';

interface Response extends BackendResponse {}

export function useDeleteReport(model: Reportable) {
  return useMutation(() => deleteReport(model), {
    onSuccess: () => {
      toast(message('Report removed'));
    },
    onError: err => showHttpErrorToast(err),
  });
}

function deleteReport(reportable: Reportable) {
  return apiClient
    .delete<Response>(`report/${reportable.model_type}/${reportable.id}`)
    .then(r => r.data);
}

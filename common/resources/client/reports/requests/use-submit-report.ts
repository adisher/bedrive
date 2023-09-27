import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';
import {toast} from '@common/ui/toast/toast';
import {message} from '@common/i18n/message';
import {Reportable} from '@common/reports/Reportable';

interface Response extends BackendResponse {
  model: Reportable;
}

interface Payload {
  reason?: string;
}

export function useSubmitReport(model: Reportable) {
  return useMutation((payload: Payload) => submitReport(model, payload), {
    onSuccess: () => {
      toast(message('Thanks for reporting. We will review this content.'));
    },
    onError: err => showHttpErrorToast(err),
  });
}

function submitReport(model: Reportable, payload: Payload) {
  return apiClient
    .post<Response>('report', {
      reason: payload.reason,
      model_id: model.id,
      model_type: model.model_type,
    })
    .then(r => r.data);
}

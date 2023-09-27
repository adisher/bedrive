import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {toast} from '@common/ui/toast/toast';
import {message} from '@common/i18n/message';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {
  //
}

interface Payload {
  commentIds: number[];
}

export function useDeleteComments() {
  return useMutation((payload: Payload) => deleteComments(payload), {
    onSuccess: (response, payload) => {
      toast(
        message('[one Comment deleted|other Deleted :count comments]', {
          values: {count: payload.commentIds.length},
        })
      );
    },
    onError: err => showHttpErrorToast(err),
  });
}

function deleteComments({commentIds}: Payload): Promise<Response> {
  return apiClient.delete(`comment/${commentIds.join(',')}`).then(r => r.data);
}

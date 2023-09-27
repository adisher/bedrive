import {useMutation} from '@tanstack/react-query';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {linkPageState} from '../link-page-store';
import {apiClient} from '@common/http/query-client';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';
import {message} from '@common/i18n/message';

interface Response extends BackendResponse {
  matches: boolean;
}

interface Props {
  password: string;
  linkHash: string;
}

function checkLinkPassword({password, linkHash}: Props): Promise<Response> {
  return apiClient
    .post(`shareable-links/${linkHash}/check-password`, {password})
    .then(r => r.data);
}

export function useCheckLinkPassword() {
  return useMutation((props: Props) => checkLinkPassword(props), {
    onSuccess: (response, props) => {
      if (response.matches) {
        linkPageState().setPassword(props.password);
      }
    },
    onError: err => showHttpErrorToast(err, message('Could not create link')),
  });
}

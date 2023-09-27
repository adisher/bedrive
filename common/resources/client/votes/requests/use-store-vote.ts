import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {VotableModel} from '@common/votes/votable-model';
import {showHttpErrorToast} from '@common/utils/http/show-http-error-toast';

interface Response extends BackendResponse {
  model: VotableModel;
}

interface Payload {
  voteType: 'upvote' | 'downvote';
}

export function useStoreVote(model: VotableModel) {
  return useMutation((payload: Payload) => changeVote(model, payload), {
    onSuccess: response => {
      //
    },
    onError: err => showHttpErrorToast(err),
  });
}

function changeVote(model: VotableModel, payload: Payload) {
  return apiClient
    .post<Response>('vote', {
      vote_type: payload.voteType,
      model_id: model.id,
      model_type: model.model_type,
    })
    .then(r => r.data);
}

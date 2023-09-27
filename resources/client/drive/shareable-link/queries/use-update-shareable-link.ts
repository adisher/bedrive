import {useMutation} from '@tanstack/react-query';
import {UseFormReturn} from 'react-hook-form';
import {apiClient, queryClient} from '@common/http/query-client';
import {FetchShareableLinkResponse} from './use-entry-shareable-link';
import {onFormQueryError} from '@common/errors/on-form-query-error';
import {DriveQueryKeys} from '../../drive-query-keys';

export interface UpdateShareableLinkPayload {
  allowEdit: boolean;
  allowDownload: boolean;
  expiresAt: string;
  password: string;
  entryId: number;
}

function updateShareableLink({
  entryId,
  ...payload
}: UpdateShareableLinkPayload): Promise<FetchShareableLinkResponse> {
  return apiClient
    .put(`file-entries/${entryId}/shareable-link`, payload)
    .then(response => response.data);
}

export function useUpdateShareableLink(
  form: UseFormReturn<UpdateShareableLinkPayload>
) {
  return useMutation(
    (payload: UpdateShareableLinkPayload) => updateShareableLink(payload),
    {
      onSuccess: (data, {entryId}) => {
        queryClient.setQueryData<FetchShareableLinkResponse>(
          DriveQueryKeys.fetchEntryShareableLink(entryId),
          data
        );
      },
      onError: r => onFormQueryError(r, form),
    }
  );
}

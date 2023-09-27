import {useQuery} from '@tanstack/react-query';
import {ShareableLink} from '../shareable-link';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveQueryKeys} from '../../drive-query-keys';
import {apiClient} from '@common/http/query-client';

export interface FetchShareableLinkResponse extends BackendResponse {
  link: ShareableLink | null;
}

export function useEntryShareableLink(entryId: number) {
  return useQuery(
    DriveQueryKeys.fetchEntryShareableLink(entryId!),
    () => fetchLinkByEntryId(entryId!),
    {enabled: !!entryId}
  );
}

function fetchLinkByEntryId(
  entryId: number
): Promise<FetchShareableLinkResponse> {
  return apiClient
    .get(`file-entries/${entryId}/shareable-link`)
    .then(response => response.data);
}

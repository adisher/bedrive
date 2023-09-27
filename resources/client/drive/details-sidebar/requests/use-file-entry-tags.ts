import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {NormalizedModel} from '@common/datatable/filters/normalized-model';
import {BackendResponse} from '@common/http/backend-response/backend-response';

interface Response extends BackendResponse {
  results: NormalizedModel[];
}

export function useFileEntryTags(query?: string) {
  return useQuery(['file-entry-tags', query], () => fetchTags(query), {
    keepPreviousData: true,
  });
}

async function fetchTags(query?: string) {
  return apiClient
    .get<Response>('file-entry-tags', {params: {query}})
    .then(r => r.data);
}

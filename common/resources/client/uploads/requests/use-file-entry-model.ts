import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {FileEntry} from '@common/uploads/file-entry';

interface Response extends BackendResponse {
  fileEntry: FileEntry;
}

interface Options {
  enabled?: boolean;
}

export function useFileEntryModel(
  entryIdOrUrl: number | string | undefined,
  options: Options = {enabled: true}
) {
  const entryId = extractEntryId(entryIdOrUrl);
  return useQuery(
    ['file-entries', `${entryId}`],
    () => fetchFileEntry(entryId!),
    {enabled: !!entryId && options.enabled}
  );
}

function fetchFileEntry(entryId: number | string) {
  return apiClient
    .get<Response>(`file-entries/${entryId}/model`)
    .then(response => response.data);
}

function extractEntryId(entryIdOrUrl: number | string | undefined) {
  if (!entryIdOrUrl) {
    return undefined;
  }
  const parsedId = parseInt(entryIdOrUrl as string);
  if (!isNaN(parsedId)) {
    return parsedId;
  }
  return `${entryIdOrUrl}`.split('/').pop();
}

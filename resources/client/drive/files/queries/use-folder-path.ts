import {useQuery} from '@tanstack/react-query';
import {DriveFolder} from '../drive-entry';
import {DriveQueryKeys} from '../../drive-query-keys';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {apiClient} from '@common/http/query-client';

interface FolderPathResponse extends BackendResponse {
  path: DriveFolder[];
}

type Params = Record<string, string | number | null>;

interface Props {
  hash?: string;
  params?: Params;
  isEnabled?: boolean;
}
export function useFolderPath({hash, params, isEnabled = true}: Props) {
  return useQuery(
    DriveQueryKeys.fetchFolderPath(hash!, params),
    () => fetchFolderPath(hash!, params),
    {enabled: !!hash && isEnabled}
  );
}

function fetchFolderPath(
  hash: string,
  params?: Params
): Promise<FolderPathResponse> {
  return apiClient
    .get(`folders/${hash}/path`, {params})
    .then(response => response.data);
}

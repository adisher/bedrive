import {useQuery} from '@tanstack/react-query';
import {DriveFolder} from '../drive-entry';
import {useAuth} from '@common/auth/use-auth';
import {DriveQueryKeys} from '../../drive-query-keys';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {apiClient} from '@common/http/query-client';
import {useActiveWorkspaceId} from '@common/workspace/active-workspace-id-context';

export interface UserFoldersApiParams {
  userId: number;
  workspaceId: number | null;
}

interface UserFoldersResponse extends BackendResponse {
  folders: DriveFolder[];
  rootFolder: DriveFolder;
}

function fetchUserFolders(
  params: UserFoldersApiParams
): Promise<UserFoldersResponse> {
  return apiClient
    .get(`users/${params.userId}/folders`, {params})
    .then(response => response.data);
}

export function useFolders() {
  const {user} = useAuth();
  const {workspaceId} = useActiveWorkspaceId();
  const params: UserFoldersApiParams = {
    userId: user!.id,
    workspaceId,
  };
  return useQuery(
    DriveQueryKeys.fetchUserFolders(params),
    () => fetchUserFolders(params),
    {enabled: !!user}
  );
}

import {useMutation} from '@tanstack/react-query';
import {UseFormReturn} from 'react-hook-form';
import {apiClient} from '@common/http/query-client';
import {onFormQueryError} from '@common/errors/on-form-query-error';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {DriveFolder} from '../drive-entry';
import {invalidateEntryQueries} from '../../drive-query-keys';

interface Response extends BackendResponse {
  folder: DriveFolder;
}

interface CreateFolderProps {
  name: string;
  parentId?: number | null;
}

function createFolder({name, parentId}: CreateFolderProps): Promise<Response> {
  return apiClient
    .post('folders', {
      name,
      parentId: parentId === 0 ? null : parentId,
    })
    .then(response => response.data);
}

export function useCreateFolder(form: UseFormReturn<any>) {
  return useMutation(
    ({name, parentId}: CreateFolderProps) => {
      return createFolder({name, parentId});
    },
    {
      onSuccess: () => invalidateEntryQueries(),
      onError: r => onFormQueryError(r, form),
    }
  );
}

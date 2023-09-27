import {useMutation} from '@tanstack/react-query';
import {UseFormReturn} from 'react-hook-form';
import {toast} from '@common/ui/toast/toast';
import {message} from '@common/i18n/message';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {FileEntry} from '@common/uploads/file-entry';
import {apiClient} from '@common/http/query-client';
import {invalidateEntryQueries} from '../../drive-query-keys';
import {onFormQueryError} from '@common/errors/on-form-query-error';

interface Response extends BackendResponse {
  fileEntry: FileEntry;
}

interface Payload {
  entryId: number;
  name: string;
  initialName: string;
}

export function useRenameEntry(form: UseFormReturn<any>) {
  return useMutation((payload: Payload) => renameEntry(payload), {
    onSuccess: (r, p) => {
      invalidateEntryQueries();
      toast(
        message(':oldName renamed to :newName', {
          values: {oldName: p.initialName, newName: r.fileEntry.name},
        })
      );
    },
    onError: err => onFormQueryError(err, form),
  });
}

function renameEntry({entryId, ...payload}: Payload): Promise<Response> {
  return apiClient
    .put(`file-entries/${entryId}`, payload)
    .then(response => response.data);
}

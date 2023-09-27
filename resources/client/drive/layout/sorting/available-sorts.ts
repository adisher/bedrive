import {message} from '@common/i18n/message';
import {MessageDescriptor} from '@common/i18n/message-descriptor';

export const AVAILABLE_SORTS: DriveSortOption[] = [
  {id: 'file_size', label: message('Size')},
  {id: 'name', label: message('Name')},
  {id: 'updated_at', label: message('Last modified')},
  {id: 'created_at', label: message('Upload date')},
  {id: 'type', label: message('Type')},
  {id: 'extension', label: message('Extension')},
];

export interface DriveSortOption {
  id: SortColumn;
  label: MessageDescriptor;
}

export type SortColumn =
  | 'file_size'
  | 'name'
  | 'updated_at'
  | 'created_at'
  | 'type'
  | 'extension';

export type SortDirection = 'desc' | 'asc';

export interface SortValue {
  column: SortColumn;
  direction: SortDirection;
}

import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '@common/datatable/filters/backend-filter';
import {FILE_ENTRY_TYPE_FILTER} from '@common/admin/file-entry/file-entry-index-filters';
import {message} from '@common/i18n/message';
import {
  createdAtFilter,
  updatedAtFilter,
} from '@common/datatable/filters/timestamp-filters';

export const driveSearchFilters: BackendFilter[] = [
  FILE_ENTRY_TYPE_FILTER,
  {
    key: 'owner_id',
    label: message('Owner'),
    description: message('User file was uploaded by'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: '02',
      options: [
        {
          key: '01',
          label: message('anyone'),
          value: {value: null, operator: '!='},
        },
        {
          key: '02',
          label: message('me'),
          value: '{authId}',
        },
        {
          key: '03',
          label: message('not me'),
          value: {value: '{authId}', operator: '!='},
        },
      ],
    },
  },
  createdAtFilter({
    description: message('Date file was uploaded'),
  }),
  updatedAtFilter({
    description: message('Date file was last changed'),
  }),
  {
    key: 'deleted_at',
    label: message('In trash'),
    description: message('Only show files that are in the trash'),
    defaultOperator: FilterOperator.ne,
    control: {
      type: FilterControlType.BooleanToggle,
      defaultValue: null,
    },
  },
  {
    key: 'shareableLink',
    label: message('Has shareable link'),
    description: message('Only show files that have a shareable link'),
    defaultOperator: FilterOperator.has,
    control: {
      type: FilterControlType.BooleanToggle,
      defaultValue: '*',
    },
  },
  {
    control: {
      type: FilterControlType.BooleanToggle,
      defaultValue: true,
    },
    key: 'sharedByMe',
    label: message('Shared by me'),
    description: message('Only show files that are shared with someone'),
    defaultOperator: FilterOperator.eq,
  },
];

import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '@common/datatable/filters/backend-filter';
import {message} from '@common/i18n/message';
import {USER_MODEL} from '@common/auth/user';
import {
  createdAtFilter,
  updatedAtFilter,
} from '@common/datatable/filters/timestamp-filters';

export const CommentsDatatableFilters: BackendFilter[] = [
  {
    key: 'deleted',
    label: message('Status'),
    description: message('Whether comment is active or deleted'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: '01',
      options: [
        {
          key: '01',
          label: message('Active'),
          value: false,
        },
        {
          key: '02',
          label: message('Deleted'),
          value: true,
        },
      ],
    },
  },
  {
    key: 'user_id',
    label: message('User'),
    description: message('User comment was created by'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.SelectModel,
      model: USER_MODEL,
    },
  },
  createdAtFilter({
    description: message('Date comment was created'),
  }),
  updatedAtFilter({
    description: message('Date comment was last updated'),
  }),
];

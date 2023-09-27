import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '../../datatable/filters/backend-filter';
import {message} from '../../i18n/message';
import {
  createdAtFilter,
  updatedAtFilter,
} from '@common/datatable/filters/timestamp-filters';

export const RoleIndexPageFilters: BackendFilter[] = [
  {
    key: 'type',
    label: message('Type'),
    description: message('Type of the role'),
    defaultOperator: FilterOperator.ne,
    control: {
      type: FilterControlType.Select,
      defaultValue: '01',
      options: [
        {
          key: '01',
          label: message('Sitewide'),
          value: 'sitewide',
        },
        {
          key: '02',
          label: message('Workspace'),
          value: 'workspace',
        },
      ],
    },
  },
  createdAtFilter({
    description: message('Date role was created'),
  }),
  updatedAtFilter({
    description: message('Date role was last updated'),
  }),
];

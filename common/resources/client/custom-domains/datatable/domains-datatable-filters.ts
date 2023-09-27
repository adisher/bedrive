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

export const DomainsDatatableFilters: BackendFilter[] = [
  {
    key: 'global',
    label: message('Global'),
    description: message('Whether domain is marked as global'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.BooleanToggle,
      defaultValue: true,
    },
  },
  createdAtFilter({
    description: message('Date domain was created'),
  }),
  updatedAtFilter({
    description: message('Date domain was last updated'),
  }),
  {
    key: 'user_id',
    label: message('Owner'),
    description: message('User domain belongs to'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.SelectModel,
      model: USER_MODEL,
    },
  },
];

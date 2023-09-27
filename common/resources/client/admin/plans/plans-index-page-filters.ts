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

export const PlansIndexPageFilters: BackendFilter[] = [
  {
    key: 'subscriptions',
    label: message('Subscriptions'),
    description: message('Whether plan has any active subscriptions'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: '01',
      options: [
        {
          key: '01',
          label: message('Has active subscriptions'),
          value: {value: '*', operator: FilterOperator.has},
        },
        {
          key: '02',
          label: message('Does not have active subscriptions'),
          value: {value: '*', operator: FilterOperator.doesntHave},
        },
      ],
    },
  },
  createdAtFilter({
    description: message('Date plan was created'),
  }),
  updatedAtFilter({
    description: message('Date plan was last updated'),
  }),
];

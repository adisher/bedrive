import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '../../datatable/filters/backend-filter';
import {
  createdAtFilter,
  updatedAtFilter,
} from '../../datatable/filters/timestamp-filters';
import {message} from '../../i18n/message';

export const UserIndexFilters: BackendFilter[] = [
  {
    key: 'email_verified_at',
    label: message('Email'),
    description: message('Email verification status'),
    defaultOperator: FilterOperator.ne,
    control: {
      type: FilterControlType.Select,
      defaultValue: '01',
      options: [
        {
          key: '01',
          label: message('is confirmed'),
          value: {value: null, operator: FilterOperator.ne},
        },
        {
          key: '02',
          label: message('is not confirmed'),
          value: {value: null, operator: FilterOperator.eq},
        },
      ],
    },
  },
  createdAtFilter({
    description: message('Date user registered or was created'),
  }),
  updatedAtFilter({
    description: message('Date user was last updated'),
  }),
  {
    key: 'subscriptions',
    label: message('Subscription'),
    description: message('Whether user is subscribed or not'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: '01',
      options: [
        {
          key: '01',
          label: message('is subscribed'),
          value: {value: '*', operator: FilterOperator.has},
        },
        {
          key: '02',
          label: message('is not subscribed'),
          value: {value: '*', operator: FilterOperator.doesntHave},
        },
      ],
    },
  },
];

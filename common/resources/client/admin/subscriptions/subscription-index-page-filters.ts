import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '../../datatable/filters/backend-filter';
import {message} from '../../i18n/message';
import {
  createdAtFilter,
  timestampFilter,
  updatedAtFilter,
} from '../../datatable/filters/timestamp-filters';

export const SubscriptionIndexPageFilters: BackendFilter[] = [
  {
    key: 'ends_at',
    label: message('Status'),
    description: message('Whether subscription is active or cancelled'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: 'active',
      options: [
        {
          key: 'active',
          label: message('Active'),
          value: {value: null, operator: FilterOperator.eq},
        },
        {
          key: 'cancelled',
          label: message('Cancelled'),
          value: {value: null, operator: FilterOperator.ne},
        },
      ],
    },
  },
  {
    control: {
      type: FilterControlType.Select,
      defaultValue: 'stripe',
      options: [
        {
          key: 'stripe',
          label: message('Stripe'),
          value: 'stripe',
        },
        {
          key: 'paypal',
          label: message('PayPal'),
          value: 'paypal',
        },
        {
          key: 'none',
          label: message('None'),
          value: 'none',
        },
      ],
    },
    key: 'gateway_name',
    label: message('Gateway'),
    description: message(
      'With which payment provider was subscription created'
    ),
    defaultOperator: FilterOperator.eq,
  },
  timestampFilter({
    key: 'renews_at',
    label: message('Renew date'),
    description: message('Date subscription will renew'),
  }),
  createdAtFilter({
    description: message('Date subscription was created'),
  }),
  updatedAtFilter({
    description: message('Date subscription was last updated'),
  }),
];

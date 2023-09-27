import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '../../datatable/filters/backend-filter';
import {message} from '../../i18n/message';
import {USER_MODEL} from '../../auth/user';
import {SiteConfigContextValue} from '@common/core/settings/site-config-context';
import {
  createdAtFilter,
  updatedAtFilter,
} from '@common/datatable/filters/timestamp-filters';

export const CustomPageDatatableFilters = (
  config: SiteConfigContextValue
): BackendFilter[] => {
  const dynamicFilters: BackendFilter[] =
    config.customPages.types.length > 1
      ? [
          {
            control: {
              type: FilterControlType.Select,
              defaultValue: 'default',
              options: config.customPages.types.map(type => ({
                value: type.type,
                label: type.label,
                key: type.type,
              })),
            },

            key: 'type',
            label: message('Type'),
            description: message('Type of the page'),
            defaultOperator: FilterOperator.eq,
          },
        ]
      : [];

  return [
    {
      key: 'user_id',
      label: message('User'),
      description: message('User page was created by'),
      defaultOperator: FilterOperator.eq,
      control: {
        type: FilterControlType.SelectModel,
        model: USER_MODEL,
      },
    },
    ...dynamicFilters,
    createdAtFilter({
      description: message('Date page was created'),
    }),
    updatedAtFilter({
      description: message('Date page was last updated'),
    }),
  ];
};

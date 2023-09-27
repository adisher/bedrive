import {Key} from 'react';
import {BackendFilter} from '../backend-filter';

export interface FilterListValue {
  key: Key;
  value: BackendFilter['control']['defaultValue'];
  operator?: BackendFilter['defaultOperator'];
  valueKey?: Key;
  isInactive?: boolean;
  extraFilters?: {key: string; operator: string; value: any}[];
}

export function encodeBackendFilters(
  filterValues: FilterListValue[] | null,
  filters?: BackendFilter[]
): string {
  if (!filterValues) return '';

  // prepare values for backend
  filterValues = !filters
    ? filterValues
    : filterValues
        .filter(item => item.value !== '')
        .map(item => transformValue(item, filters));

  // remove all placeholder filters
  filterValues = filterValues.filter(fm => !fm.isInactive);

  if (!filterValues.length) {
    return '';
  }

  return encodeURIComponent(btoa(JSON.stringify(filterValues)));
}

function transformValue(
  filterValue: FilterListValue,
  filters: BackendFilter[]
) {
  const filterConfig = filters.find(f => f.key === filterValue.key);
  // select components will use a key always, because we can't use objects as
  // value. Map over select options and replace key with actual value
  if (filterConfig?.control.type === 'select') {
    const option = (filterConfig.control.options || []).find(
      o => o.key === filterValue.value
    );
    // if it's language or country select, there might not be an option
    if (option) {
      return {...filterValue, value: option.value, valueKey: option.key};
    }
  }

  if (filterConfig?.extraFilters?.length) {
    filterValue['extraFilters'] = filterConfig.extraFilters;
  }

  return filterValue;
}

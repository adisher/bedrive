import {
  BackendFilter,
  DatePickerFilterControl,
  FilterControlType,
  FilterOperator,
} from './backend-filter';
import {
  DateRangePreset,
  DateRangePresets,
} from '../../ui/forms/input-field/date/date-range-picker/dialog/date-range-presets';
import {message} from '../../i18n/message';
import {dateRangeToAbsoluteRange} from '../../ui/forms/input-field/date/date-range-picker/form-date-range-picker';
import {PartialWithRequired} from '@common/utils/ts/partial-with-required';

export function timestampFilter(
  options: PartialWithRequired<
    BackendFilter<DatePickerFilterControl>,
    'key' | 'label'
  >
): BackendFilter<DatePickerFilterControl> {
  return {
    ...options,
    defaultOperator: FilterOperator.between,
    control: {
      type: FilterControlType.DateRangePicker,
      defaultValue:
        options.control?.defaultValue ||
        dateRangeToAbsoluteRange(
          (DateRangePresets[3] as Required<DateRangePreset>).getRangeValue()
        ),
    },
  };
}

export function createdAtFilter(
  options: Partial<BackendFilter<DatePickerFilterControl>>
): BackendFilter<DatePickerFilterControl> {
  return timestampFilter({
    key: 'created_at',
    label: message('Date created'),
    ...options,
  });
}

export function updatedAtFilter(
  options: Partial<BackendFilter<DatePickerFilterControl>>
): BackendFilter<DatePickerFilterControl> {
  return timestampFilter({
    key: 'updated_at',
    label: message('Last updated'),
    ...options,
  });
}

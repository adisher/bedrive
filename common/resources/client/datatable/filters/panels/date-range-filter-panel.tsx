import {FormDateRangePicker} from '@common/ui/forms/input-field/date/date-range-picker/form-date-range-picker';
import {FilterPanelProps} from '@common/datatable/filters/panels/filter-panel-props';
import {DatePickerFilterControl} from '@common/datatable/filters/backend-filter';

export function DateRangeFilterPanel({
  filter,
}: FilterPanelProps<DatePickerFilterControl>) {
  return (
    <FormDateRangePicker
      min={filter.control.min}
      max={filter.control.max}
      size="sm"
      name={`${filter.key}.value`}
      granularity="day"
      closeDialogOnSelection={true}
    />
  );
}

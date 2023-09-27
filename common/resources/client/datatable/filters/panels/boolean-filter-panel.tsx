import {FilterPanelProps} from './filter-panel-props';
import {FilterBooleanToggleControl} from '@common/datatable/filters/backend-filter';

export function BooleanFilterPanel({
  filter,
}: FilterPanelProps<FilterBooleanToggleControl>) {
  // Toggling accordion in the dialog will already apply boolean filter, no need for any extra fields here
  return null;
}

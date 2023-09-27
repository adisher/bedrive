import {FilterPanelProps} from './filter-panel-props';
import {FormNormalizedModelField} from '@common/ui/forms/normalized-model-field';
import {FilterSelectModelControl} from '@common/datatable/filters/backend-filter';

export function NormalizedModelFilterPanel({
  filter,
}: FilterPanelProps<FilterSelectModelControl>) {
  return (
    <FormNormalizedModelField
      name={`${filter.key}.value`}
      modelType={filter.control.model}
      openMenuOnFocus
    />
  );
}

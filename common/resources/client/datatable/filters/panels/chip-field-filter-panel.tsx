import {Item} from '@common/ui/forms/listbox/item';
import {FilterPanelProps} from '@common/datatable/filters/panels/filter-panel-props';
import {FormChipField} from '@common/ui/forms/input-field/chip-field/form-chip-field';
import {useTrans} from '@common/i18n/use-trans';
import {FilterChipFieldControl} from '@common/datatable/filters/backend-filter';
import {Trans} from '@common/i18n/trans';

export function ChipFieldFilterPanel({
  filter,
}: FilterPanelProps<FilterChipFieldControl>) {
  const {trans} = useTrans();
  return (
    <FormChipField
      size="sm"
      name={`${filter.key}.value`}
      valueKey="id"
      allowCustomValue={false}
      showDropdownArrow
      placeholder={
        filter.control.placeholder
          ? trans(filter.control.placeholder)
          : undefined
      }
      displayWith={chip =>
        filter.control.options.find(o => o.key === chip.id)?.label.message
      }
      suggestions={filter.control.options.map(o => ({
        id: o.key,
        name: o.label.message,
      }))}
    >
      {chip => (
        <Item key={chip.id} value={chip.id}>
          {<Trans message={chip.name} />}
        </Item>
      )}
    </FormChipField>
  );
}

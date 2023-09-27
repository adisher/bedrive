import {FormTextField} from '@common/ui/forms/input-field/text-field/text-field';
import {Trans} from '@common/i18n/trans';
import {FormSelect} from '@common/ui/forms/select/select';
import {Item} from '@common/ui/forms/listbox/item';
import {FilterOperatorNames} from '@common/datatable/filters/filter-operator-names';
import {Fragment} from 'react';
import {FilterPanelProps} from '@common/datatable/filters/panels/filter-panel-props';
import {
  FilterNumberInputControl,
  FilterTextInputControl,
} from '@common/datatable/filters/backend-filter';

export function InputFilterPanel({
  filter,
}: FilterPanelProps<FilterTextInputControl | FilterNumberInputControl>) {
  const control = filter.control;
  return (
    <Fragment>
      <FormSelect
        selectionMode="single"
        name={`${filter.key}.operator`}
        className="mb-14"
        size="sm"
        required
      >
        {filter.operators?.map(operator => (
          <Item key={operator} value={operator}>
            {<Trans {...FilterOperatorNames[operator]} />}
          </Item>
        ))}
      </FormSelect>
      <FormTextField
        size="sm"
        name={`${filter.key}.value`}
        type={filter.control.inputType}
        min={'minValue' in control ? control.minValue : undefined}
        max={'maxValue' in control ? control.maxValue : undefined}
        minLength={'minLength' in control ? control.minLength : undefined}
        maxLength={'maxLength' in control ? control.maxLength : undefined}
        required
      />
    </Fragment>
  );
}

import {ComponentType} from 'react';
import {MessageDescriptor} from '../../i18n/message-descriptor';
import {NormalizedModel} from '@common/datatable/filters/normalized-model';
import {ChipValue} from '@common/ui/forms/input-field/chip-field/chip-field';
import {AbsoluteDateRange} from '@common/ui/forms/input-field/date/date-range-picker/form-date-range-picker';
import {DateValue} from '@internationalized/date';
import {FilterListControlProps} from '@common/datatable/filters/filter-list/filter-list-control';

export interface FilterSelectControl {
  type: FilterControlType.Select;
  options: {label: MessageDescriptor; key: string | number; value: any}[];
  defaultValue?: string | number | boolean;
  placeholder?: MessageDescriptor;
  searchPlaceholder?: MessageDescriptor;
  showSearchField?: boolean;
}

export interface FilterNumberInputControl {
  type: FilterControlType.Input;
  placeholder?: MessageDescriptor;
  inputType: 'number';
  minValue?: number;
  maxValue?: number;
  defaultValue: number;
}

export interface FilterTextInputControl {
  type: FilterControlType.Input;
  placeholder?: MessageDescriptor;
  inputType: 'string';
  minLength?: number;
  maxLength?: number;
  defaultValue: string;
}

export interface FilterSelectModelControl {
  type: FilterControlType.SelectModel;
  model: string;
  defaultValue?: NormalizedModel;
}

export interface FilterChipFieldControl {
  type: FilterControlType.ChipField;
  options: FilterSelectControl['options'];
  placeholder?: MessageDescriptor;
  defaultValue: ChipValue[];
}

export interface FilterBooleanToggleControl {
  type: FilterControlType.BooleanToggle;
  // value can be something other than boolean, toggling will either send that value or nothing
  defaultValue: string | number | boolean | null;
}

export interface DatePickerFilterControl {
  type: FilterControlType.DateRangePicker;
  defaultValue: AbsoluteDateRange;
  min?: DateValue;
  max?: DateValue;
}

export interface CustomFilterControl {
  type: FilterControlType.Custom;
  panel: ComponentType<{filter: BackendFilter}>;
  listItem: ComponentType<FilterListControlProps<number, CustomFilterControl>>;
  defaultValue?: any;
}

export type FilterControl =
  | FilterSelectControl
  | FilterNumberInputControl
  | FilterTextInputControl
  | FilterSelectModelControl
  | FilterChipFieldControl
  | DatePickerFilterControl
  | FilterBooleanToggleControl
  | CustomFilterControl;

export interface BackendFilter<T = FilterControl> {
  key: string;
  label: MessageDescriptor;
  description?: MessageDescriptor;
  control: T;
  defaultOperator: FilterOperator;
  operators?: FilterOperator[];
  extraFilters?: {key: string; operator: FilterOperator; value: any}[];
}

export enum FilterControlType {
  Select = 'select',
  DateRangePicker = 'dateRangePicker',
  SelectModel = 'selectModel',
  Input = 'input',
  BooleanToggle = 'booleanToggle',
  ChipField = 'chipField',
  Custom = 'custom',
}

export enum FilterOperator {
  eq = '=',
  ne = '!=',
  gt = '>',
  gte = '>=',
  lt = '<',
  lte = '<=',
  has = 'has',
  hasAll = 'hasAll',
  doesntHave = 'doesntHave',
  between = 'between',
}

export const ALL_PRIMITIVE_OPERATORS = [
  FilterOperator.eq,
  FilterOperator.ne,
  FilterOperator.gt,
  FilterOperator.gte,
  FilterOperator.lt,
  FilterOperator.lte,
];

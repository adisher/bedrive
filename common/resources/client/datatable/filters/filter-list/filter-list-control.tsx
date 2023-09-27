import {
  BackendFilter,
  CustomFilterControl,
  DatePickerFilterControl,
  FilterBooleanToggleControl,
  FilterChipFieldControl,
  FilterControl,
  FilterControlType,
  FilterNumberInputControl,
  FilterOperator,
  FilterSelectControl,
  FilterSelectModelControl,
  FilterTextInputControl,
} from '../backend-filter';
import {FilterListTriggerButton} from './filter-list-trigger-button';
import {Trans} from '@common/i18n/trans';
import {SelectFilterPanel} from '../panels/select-filter-panel';
import {FilterListItemDialogTrigger} from './filter-list-item-dialog-trigger';
import {Avatar} from '@common/ui/images/avatar';
import {NormalizedModelFilterPanel} from '../panels/normalized-model-filter-panel';
import {DateRangeFilterPanel} from '../panels/date-range-filter-panel';
import {Fragment, Key, ReactNode} from 'react';
import {DateRangePresets} from '@common/ui/forms/input-field/date/date-range-picker/dialog/date-range-presets';
import {FormattedDateTimeRange} from '@common/i18n/formatted-date-time-range';
import {AbsoluteDateRange} from '@common/ui/forms/input-field/date/date-range-picker/form-date-range-picker';
import {InputFilterPanel} from '../panels/input-filter-panel';
import {FilterOperatorNames} from '../filter-operator-names';
import {FilterItemFormValue} from '../add-filter-dialog';
import {useNormalizedModel} from '@common/users/queries/use-normalized-model';
import {Skeleton} from '@common/ui/skeleton/skeleton';
import {useTrans} from '@common/i18n/use-trans';
import {ChipFieldFilterPanel} from '@common/datatable/filters/panels/chip-field-filter-panel';
import {FormattedNumber} from '@common/i18n/formatted-number';

export interface FilterListControlProps<T = unknown, E = FilterControl> {
  filter: BackendFilter<E>;
  onValueChange: (value: FilterItemFormValue<T>) => void;
  value: T;
  operator?: FilterOperator;
  isInactive?: boolean;
}
export function FilterListControl(props: FilterListControlProps<any, any>) {
  switch (props.filter.control.type) {
    case FilterControlType.DateRangePicker:
      return <DatePickerControl {...props} />;
    case FilterControlType.BooleanToggle:
      return <BooleanToggleControl {...props} />;
    case FilterControlType.Select:
      return <SelectControl {...props} />;
    case FilterControlType.ChipField:
      return <ChipFieldControl {...props} />;
    case FilterControlType.Input:
      return <InputControl {...props} />;
    case FilterControlType.SelectModel:
      return <SelectModelControl {...props} />;
    case FilterControlType.Custom:
      const Control = (props.filter.control as CustomFilterControl).listItem;
      return <Control {...props} />;
    default:
      return null;
  }
}

function DatePickerControl(
  props: FilterListControlProps<
    Required<AbsoluteDateRange>,
    DatePickerFilterControl
  >
) {
  const {value, filter} = props;

  let valueLabel: ReactNode;
  if (value.preset !== undefined) {
    valueLabel = <Trans {...DateRangePresets[value.preset].label} />;
  } else {
    valueLabel = (
      <FormattedDateTimeRange
        start={new Date(value.start)}
        end={new Date(value.end)}
        options={{dateStyle: 'medium'}}
      />
    );
  }

  return (
    <FilterListItemDialogTrigger
      {...props}
      label={valueLabel}
      panel={<DateRangeFilterPanel filter={filter} />}
    />
  );
}

function BooleanToggleControl({
  filter,
  isInactive,
  onValueChange,
}: FilterListControlProps<
  FilterBooleanToggleControl['defaultValue'],
  FilterBooleanToggleControl
>) {
  // todo: toggle control on or off here
  return (
    <FilterListTriggerButton
      onClick={() => {
        onValueChange({value: filter.control.defaultValue});
      }}
      filter={filter}
      isInactive={isInactive}
    />
  );
}

function SelectControl(
  props: FilterListControlProps<Key, FilterSelectControl>
) {
  const {filter, value} = props;
  const option = filter.control.options.find(o => o.key === value);
  return (
    <FilterListItemDialogTrigger
      {...props}
      label={option ? <Trans {...option.label} /> : null}
      panel={<SelectFilterPanel filter={filter} />}
    />
  );
}

function ChipFieldControl(
  props: FilterListControlProps<string[], FilterChipFieldControl>
) {
  return (
    <FilterListItemDialogTrigger
      {...props}
      label={<MultipleValues {...props} />}
      panel={<ChipFieldFilterPanel filter={props.filter} />}
    />
  );
}

function MultipleValues(
  props: FilterListControlProps<string[], FilterChipFieldControl>
) {
  const {trans} = useTrans();
  const {filter, value} = props;
  const options = value.map(v => filter.control.options.find(o => o.key === v));
  const maxShownCount = 3;
  const notShownCount = value.length - maxShownCount;

  // translate names, add commas and limit to 3
  const names = (
    <Fragment>
      {options
        .filter(Boolean)
        .slice(0, maxShownCount)
        .map((o, i) => {
          let name = '';
          if (i !== 0) {
            name += ', ';
          }
          name += trans(o!.label);
          return name;
        })}
    </Fragment>
  );

  // indicate that there are some names not shown
  return notShownCount > 0 ? (
    <Trans
      message=":names + :count more"
      values={{names: names, count: notShownCount}}
    />
  ) : (
    names
  );
}

function InputControl(
  props: FilterListControlProps<
    string,
    FilterTextInputControl | FilterNumberInputControl
  >
) {
  const {filter, value, operator} = props;

  const operatorLabel = operator ? (
    <Trans {...FilterOperatorNames[operator]} />
  ) : null;

  const formattedValue =
    filter.control.inputType === 'number' ? (
      <FormattedNumber value={value as any} />
    ) : (
      value
    );

  return (
    <FilterListItemDialogTrigger
      {...props}
      label={
        <Fragment>
          {operatorLabel} {formattedValue}
        </Fragment>
      }
      panel={<InputFilterPanel filter={filter} />}
    />
  );
}

function SelectModelControl(
  props: FilterListControlProps<string, FilterSelectModelControl>
) {
  const {value, filter} = props;
  const {isLoading, data} = useNormalizedModel(filter.control.model!, value);

  const skeleton = (
    <Fragment>
      <Skeleton variant="avatar" size="w-18 h-18 mr-6" />
      <Skeleton variant="rect" size="w-50" />
    </Fragment>
  );
  const modelPreview = (
    <Fragment>
      <Avatar size="xs" src={data?.model.image} className="mr-6" />
      {data?.model.name}
    </Fragment>
  );

  const label = isLoading || !data ? skeleton : modelPreview;

  return (
    <FilterListItemDialogTrigger
      {...props}
      label={label}
      panel={<NormalizedModelFilterPanel filter={filter} />}
    />
  );
}

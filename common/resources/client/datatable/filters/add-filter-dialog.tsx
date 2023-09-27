import {Dialog} from '../../ui/overlays/dialog/dialog';
import {
  BackendFilter,
  DatePickerFilterControl,
  FilterBooleanToggleControl,
  FilterChipFieldControl,
  FilterControlType,
  FilterOperator,
  FilterSelectControl,
  FilterSelectModelControl,
  FilterTextInputControl,
} from './backend-filter';
import {Trans} from '../../i18n/trans';
import {Key, useState} from 'react';
import {DialogHeader} from '../../ui/overlays/dialog/dialog-header';
import {DialogBody} from '../../ui/overlays/dialog/dialog-body';
import {useBackendFilterUrlParams} from './backend-filter-url-params';
import {useDialogContext} from '../../ui/overlays/dialog/dialog-context';
import {Accordion, AccordionItem} from '../../ui/accordion/accordion';
import {Button} from '../../ui/buttons/button';
import {useForm} from 'react-hook-form';
import {Form} from '../../ui/forms/form';
import {Checkbox} from '../../ui/forms/toggle/checkbox';
import {SelectFilterPanel} from './panels/select-filter-panel';
import {DateRangeFilterPanel} from './panels/date-range-filter-panel';
import {NormalizedModelFilterPanel} from './panels/normalized-model-filter-panel';
import {InputFilterPanel} from './panels/input-filter-panel';
import {BooleanFilterPanel} from './panels/boolean-filter-panel';
import clsx from 'clsx';
import {ChipFieldFilterPanel} from '@common/datatable/filters/panels/chip-field-filter-panel';

export interface FilterItemFormValue<T = any> {
  value: T;
  operator?: FilterOperator;
}

interface AddFilterDialogProps {
  filters: BackendFilter[];
}
export function AddFilterDialog({filters}: AddFilterDialogProps) {
  const {decodedFilters} = useBackendFilterUrlParams(filters);
  const {formId} = useDialogContext();

  // expand currently active filters
  const [expandedFilters, setExpandedFilters] = useState<Key[]>(() => {
    return decodedFilters.map(f => f.key);
  });

  const clearButton = (
    <Button
      size="xs"
      variant="outline"
      className="mr-auto"
      onClick={() => {
        setExpandedFilters([]);
      }}
    >
      <Trans message="Clear" />
    </Button>
  );

  const applyButton = (
    <Button
      size="xs"
      variant="flat"
      color="primary"
      className="ml-auto"
      type="submit"
      form={formId}
    >
      <Trans message="Apply" />
    </Button>
  );

  return (
    <Dialog className="min-w-[300px]" maxWidth="max-w-400" size="auto">
      <DialogHeader
        padding="px-14 py-10"
        leftAdornment={clearButton}
        rightAdornment={applyButton}
      >
        <Trans message="Filter" />
      </DialogHeader>
      <DialogBody padding="p-0">
        <FilterList
          filters={filters}
          expandedFilters={expandedFilters}
          setExpandedFilters={setExpandedFilters}
        />
      </DialogBody>
    </Dialog>
  );
}

interface FilterListProps {
  filters: BackendFilter[];
  expandedFilters: Key[];
  setExpandedFilters: (value: Key[]) => void;
}
function FilterList({
  filters,
  expandedFilters,
  setExpandedFilters,
}: FilterListProps) {
  const {decodedFilters, replaceAll} = useBackendFilterUrlParams(filters);

  // either get value and operator from url params if filter is active, or get defaults from filter config
  const defaultValues: Record<string, FilterItemFormValue> = {};
  filters.forEach(filter => {
    const appliedFilter = decodedFilters.find(f => f.key === filter.key);
    defaultValues[filter.key] =
      appliedFilter?.value !== undefined
        ? // there might be some extra keys set on filter besides
          // "value" and "operator", so add the whole object to form
          appliedFilter
        : {
            value: filter.control.defaultValue,
            operator: filter.defaultOperator,
          };
  });
  const form = useForm<Record<string, FilterItemFormValue>>({defaultValues});
  const {formId, close} = useDialogContext();

  return (
    <Form
      form={form}
      id={formId}
      onSubmit={formValue => {
        const filterValue = Object.entries(formValue)
          // remove undefined and non-expanded filters, so "clear" button will correctly remove active filters
          .filter(
            ([key, fieldValue]) =>
              expandedFilters.includes(key) && fieldValue !== undefined
          )
          .map(([key, fieldValue]) => ({
            key,
            ...fieldValue, // value and operator from form
          }));

        replaceAll(filterValue);
        close();
      }}
    >
      <Accordion
        mode="multiple"
        expandedValues={expandedFilters}
        onExpandedChange={setExpandedFilters}
      >
        {filters.map(filter => (
          <AccordionItem
            dataTestId={`filter-${filter.key}-toggle`}
            startIcon={
              <Checkbox checked={expandedFilters.includes(filter.key)} />
            }
            key={filter.key}
            value={filter.key}
            label={<Trans {...filter.label} />}
            bodyClassName="max-h-288 overflow-y-auto compact-scrollbar"
          >
            {filter.description && (
              <div
                className={clsx(
                  'text-muted text-xs',
                  // boolean filter will have nothing in the panel, no need to add margin
                  filter.control.type !== FilterControlType.BooleanToggle &&
                    'mb-14'
                )}
              >
                <Trans {...filter.description} />
              </div>
            )}
            <AddFilterDialogPanel filter={filter} />
          </AccordionItem>
        ))}
      </Accordion>
    </Form>
  );
}

interface ActiveFilterPanelProps {
  filter: BackendFilter;
}
export function AddFilterDialogPanel({filter}: ActiveFilterPanelProps) {
  switch (filter.control.type) {
    case FilterControlType.Select:
      return (
        <SelectFilterPanel
          filter={filter as BackendFilter<FilterSelectControl>}
        />
      );
    case FilterControlType.ChipField:
      return (
        <ChipFieldFilterPanel
          filter={filter as BackendFilter<FilterChipFieldControl>}
        />
      );
    case FilterControlType.DateRangePicker:
      return (
        <DateRangeFilterPanel
          filter={filter as BackendFilter<DatePickerFilterControl>}
        />
      );
    case FilterControlType.SelectModel:
      return (
        <NormalizedModelFilterPanel
          filter={filter as BackendFilter<FilterSelectModelControl>}
        />
      );
    case FilterControlType.Input:
      return (
        <InputFilterPanel
          filter={filter as BackendFilter<FilterTextInputControl>}
        />
      );
    case FilterControlType.BooleanToggle:
      return (
        <BooleanFilterPanel
          filter={filter as BackendFilter<FilterBooleanToggleControl>}
        />
      );
    case 'custom':
      const CustomComponent = filter.control.panel;
      return <CustomComponent filter={filter} />;
    default:
      return null;
  }
}

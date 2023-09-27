import {AVAILABLE_SORTS, SortColumn} from './available-sorts';
import {Button} from '@common/ui/buttons/button';
import {SortIcon} from '@common/icons/material/Sort';
import {
  Menu,
  MenuItem,
  MenuSection,
  MenuTrigger,
} from '@common/ui/navigation/menu/menu-trigger';
import {Trans} from '@common/i18n/trans';
import type {DriveSortDescriptor} from '../../drive-store';

interface Props {
  descriptor: DriveSortDescriptor;
  onChange: (value: DriveSortDescriptor) => void;
  isDisabled?: boolean;
}
export function EntriesSortButton({
  descriptor,
  onChange,
  isDisabled = false,
}: Props) {
  const column = descriptor.orderBy;
  const direction = descriptor.orderDir;
  const sort = AVAILABLE_SORTS.find(s => s.id === column);

  return (
    <MenuTrigger
      showCheckmark
      selectionMode="multiple"
      selectedValue={[direction || 'desc', column || '']}
      onItemSelected={key => {
        if (key === 'asc' || key === 'desc') {
          onChange({
            orderBy: column,
            orderDir: key,
          });
        } else {
          onChange({
            orderBy: key as SortColumn,
            orderDir: direction,
          });
        }
      }}
    >
      <Button
        className="text-muted"
        variant="text"
        size="sm"
        startIcon={<SortIcon />}
        disabled={isDisabled}
      >
        {sort ? <Trans {...sort.label} /> : null}
      </Button>
      <Menu>
        <MenuSection label={<Trans message="Direction" />}>
          <MenuItem value="asc">
            <Trans message="Ascending" />
          </MenuItem>
          <MenuItem value="desc">
            <Trans message="Descending" />
          </MenuItem>
        </MenuSection>
        <MenuSection label={<Trans message="Sort By" />}>
          {AVAILABLE_SORTS.map(item => (
            <MenuItem key={item.id} value={item.id}>
              <Trans {...item.label} />
            </MenuItem>
          ))}
        </MenuSection>
      </Menu>
    </MenuTrigger>
  );
}

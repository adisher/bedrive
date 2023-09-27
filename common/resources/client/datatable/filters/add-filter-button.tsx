import {Button, ButtonProps} from '../../ui/buttons/button';
import {BackendFilter} from './backend-filter';
import {FilterAltIcon} from '../../icons/material/FilterAlt';
import {Trans} from '../../i18n/trans';
import {useIsMobileMediaQuery} from '../../utils/hooks/is-mobile-media-query';
import {IconButton} from '../../ui/buttons/icon-button';
import {DialogTrigger} from '../../ui/overlays/dialog/dialog-trigger';
import {AddFilterDialog} from './add-filter-dialog';
import {ReactElement} from 'react';

interface AddFilterButtonProps {
  filters: BackendFilter[];
  icon?: ReactElement;
  color?: ButtonProps['color'];
  variant?: ButtonProps['variant'];
  disabled?: boolean;
  size?: ButtonProps['size'];
}
export function AddFilterButton({
  filters,
  icon = <FilterAltIcon />,
  color = 'primary',
  variant = 'outline',
  size = 'sm',
  disabled,
}: AddFilterButtonProps) {
  const isMobile = useIsMobileMediaQuery();

  const desktopButton = (
    <Button
      variant={variant}
      color={color}
      startIcon={icon}
      disabled={disabled}
      size={size}
    >
      <Trans message="Filter" />
    </Button>
  );

  const mobileButton = (
    <IconButton
      color={color}
      size="sm"
      variant={variant}
      disabled={disabled}
      radius="rounded"
    >
      {icon}
    </IconButton>
  );

  return (
    <DialogTrigger type="popover">
      {isMobile ? mobileButton : desktopButton}
      <AddFilterDialog filters={filters} />
    </DialogTrigger>
  );
}

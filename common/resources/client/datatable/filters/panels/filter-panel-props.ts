import {BackendFilter, FilterControl} from '../backend-filter';

export interface FilterPanelProps<T = FilterControl> {
  filter: BackendFilter<T>;
}

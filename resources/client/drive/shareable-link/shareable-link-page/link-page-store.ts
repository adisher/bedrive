import {create} from 'zustand';
import {immer} from 'zustand/middleware/immer';
import {DriveSortDescriptor} from '../../drive-store';
import {
  getFromLocalStorage,
  setInLocalStorage,
} from '@common/utils/hooks/local-storage';

interface LinkPageState {
  password: string | null;
  setPassword: (value: string) => void;
  isPasswordProtected: boolean;
  setIsPasswordProtected: (value: boolean) => void;
  activeSort: DriveSortDescriptor;
  setActiveSort: (value: DriveSortDescriptor) => void;
  viewMode: 'grid' | 'list';
  setViewMode: (mode: 'grid' | 'list') => void;
}

export const useLinkPageStore = create<LinkPageState>()(
  immer(set => ({
    password: null,
    viewMode: getFromLocalStorage<'list' | 'grid'>('drive.viewMode'),
    activeSort: {
      orderBy: 'updated_at',
      orderDir: 'desc',
    },
    setPassword: value => {
      set(state => {
        state.password = value;
      });
    },
    isPasswordProtected: false,
    setIsPasswordProtected: value => {
      set(state => {
        state.isPasswordProtected = value;
      });
    },

    setViewMode: mode => {
      set(state => {
        state.viewMode = mode;
        setInLocalStorage('drive.viewMode', mode);
      });
    },
    setActiveSort: value => {
      set(state => {
        state.activeSort = value;
      });
    },
  }))
);

export function linkPageState() {
  return useLinkPageStore.getState();
}

import {enableMapSet} from 'immer';
import {create} from 'zustand';
import {immer} from 'zustand/middleware/immer';
import type {DrivePage} from './drive-page/drive-page';
import {Key} from 'react';
import {DriveEntry} from './files/drive-entry';
import {SortColumn, SortDirection} from './layout/sorting/available-sorts';
import {
  getFromLocalStorage,
  setInLocalStorage,
} from '@common/utils/hooks/local-storage';
import {getBootstrapData} from '@common/core/bootstrap-data/use-backend-bootstrap-data';

const stableArray: DriveEntry[] = [];
enableMapSet();

export type DriveDialog =
  | 'rename'
  | 'newFolder'
  | 'preview'
  | 'share'
  | 'getLink'
  | 'moveTo'
  | 'confirmAndDeleteForever'
  | 'trashFolderBlock';

export interface ActiveActionDialog {
  name: DriveDialog;
  entries: DriveEntry[];
}

export interface DriveSortDescriptor {
  orderBy?: SortColumn;
  orderDir?: SortDirection;
}

interface State {
  uploadQueueIsOpen: boolean;
  selectedEntries: Set<number>;
  sidebarExpandedKeys: Key[];
  activePage?: DrivePage;
  activeActionDialog?: {
    name: DriveDialog;
    entries: DriveEntry[];
  } | null;
  entriesBeingDragged: number[];
  viewMode: 'grid' | 'list';
  sortDescriptor: DriveSortDescriptor;
  contextMenuData: {x: number; y: number; entry?: DriveEntry} | null;
}

interface Actions {
  setUploadQueueIsOpen: (isOpen: boolean) => void;
  setSidebarExpandedKeys: (keys: Key[]) => void;
  expandSidebarItem: (key: Key) => void;
  collapseSidebarItem: (key: Key) => void;
  toggleSidebarItem: (key: Key) => void;
  setActivePage: (page: DrivePage) => void;
  setActiveActionDialog: (
    name: DriveDialog | null,
    entries?: DriveEntry[]
  ) => void;
  setViewMode: (mode: 'grid' | 'list') => void;
  setSortDescriptor: (value: DriveSortDescriptor) => void;
  setContextMenuData: (
    position: {x: number; y: number; entry?: DriveEntry} | null
  ) => void;
  setEntriesBeingDragged: (value: number[]) => void;
  selectEntries: (
    entries: (number | undefined | null)[],
    merge?: boolean
  ) => void;
  deselectEntries: (entries: number[] | 'all') => void;
  reset: () => void;
}

const initialState: State = {
  uploadQueueIsOpen: false,
  contextMenuData: null,
  selectedEntries: new Set(),
  entriesBeingDragged: [],
  activeActionDialog: null,
  sidebarExpandedKeys: [],
  viewMode: getFromLocalStorage<'list' | 'grid'>(
    'drive.viewMode',
    getBootstrapData().settings?.drive?.default_view || 'grid'
  ),
  sortDescriptor: {
    orderBy: 'updated_at',
    orderDir: 'desc',
  },
};

export const useDriveStore = create<State & Actions>()(
  immer((set, get) => ({
    ...initialState,
    setUploadQueueIsOpen: isOpen => {
      set(state => {
        state.uploadQueueIsOpen = isOpen;
      });
    },
    setContextMenuData: data => {
      set(state => {
        state.contextMenuData = data;
      });
    },
    setSortDescriptor: value => {
      set(state => {
        const activePageId = get().activePage?.id;
        if (activePageId) {
          setInLocalStorage('selectedSorting', {
            ...getFromLocalStorage('selectedSorting'),
            [activePageId]: value,
          });
        }
        state.sortDescriptor = value;
      });
    },
    setActivePage: value => {
      set(state => {
        state.activePage = value;

        const storedDescriptor =
          getFromLocalStorage('selectedSorting')?.[value.id];
        state.sortDescriptor = storedDescriptor
          ? storedDescriptor
          : value.sortDescriptor;
      });
    },
    setEntriesBeingDragged: value => {
      set(state => {
        state.entriesBeingDragged = value;
      });
    },
    setActiveActionDialog: (name, entries = stableArray) => {
      set(state => {
        const current = get().activeActionDialog;
        // prevent creating a new object, if neither name nor entries changed
        if (current?.name !== name || current.entries !== entries) {
          state.activeActionDialog = name ? {name, entries} : null;
        }
      });
    },
    setViewMode: mode => {
      set(state => {
        state.viewMode = mode;
        setInLocalStorage('drive.viewMode', mode);
      });
    },
    setSidebarExpandedKeys: value =>
      set(state => {
        state.sidebarExpandedKeys = value;
      }),
    expandSidebarItem: key =>
      set(state => {
        if (!state.sidebarExpandedKeys.includes(key)) {
          state.sidebarExpandedKeys.push(key);
        }
      }),
    collapseSidebarItem: key =>
      set(state => {
        const index = state.sidebarExpandedKeys.indexOf(key);
        if (index > -1) {
          state.sidebarExpandedKeys.splice(index, 1);
        }
      }),
    toggleSidebarItem: key =>
      set(state => {
        if (state.sidebarExpandedKeys.includes(key)) {
          state.expandSidebarItem(key);
        } else {
          state.collapseSidebarItem(key);
        }
      }),
    selectEntries: (entries, merge) => {
      set(state => {
        if (!merge) {
          state.selectedEntries.clear();
        }
        entries.forEach(e => e && state.selectedEntries.add(e));
      });
    },
    deselectEntries: entries => {
      set(state => {
        if (!state.selectedEntries.size) return;
        if (entries === 'all') {
          state.selectedEntries = new Set();
        } else {
          entries.forEach(e => state.selectedEntries.delete(e));
        }
      });
    },
    reset: () => {
      set(initialState);
    },
  }))
);

export function driveState() {
  return useDriveStore.getState();
}

export function useActiveDialogEntry() {
  const dialog = useDriveStore(s => s.activeActionDialog);
  // this will only be used inside dialog, so entry will always be set in that case
  return dialog?.entries[0]!;
}

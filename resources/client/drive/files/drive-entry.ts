import {FileEntry} from '@common/uploads/file-entry';

export interface DriveEntry extends FileEntry {
  users: DriveEntryUser[];
  workspace_id?: number;
  permissions: {
    'files.create': boolean;
    'files.update': boolean;
    'files.delete': boolean;
    'files.download': boolean;
  };
}

export interface DriveFolder extends DriveEntry {
  type: 'folder';
  model_type?: string;
}

export interface DriveEntryUser {
  id: number;
  email: string;
  display_name: string;
  avatar?: string;
  owns_entry: boolean;
  entry_permissions: DriveEntryPermissions;
}

export interface DriveEntryPermissions {
  edit?: boolean;
  view?: boolean;
  download?: boolean;
}

export const DRIVE_ENTRY_FULL_PERMISSIONS = {
  edit: true,
  view: true,
  download: true,
};

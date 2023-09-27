import {driveState, useDriveStore} from '../drive-store';
import {useDriveUploadQueue} from '../uploading/use-drive-upload-queue';
import {
  Menu,
  MenuItem,
  MenuTrigger,
} from '@common/ui/navigation/menu/menu-trigger';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';
import {Button} from '@common/ui/buttons/button';
import {FileUploadIcon} from '@common/icons/material/FileUpload';
import {Trans} from '@common/i18n/trans';
import {CreateNewFolderIcon} from '@common/icons/material/CreateNewFolder';
import {UploadFileIcon} from '@common/icons/material/UploadFile';
import {DriveFolderUploadIcon} from '@common/icons/material/DriveFolderUpload';
import React from 'react';
import {IconButton} from '@common/ui/buttons/icon-button';
import {AddIcon} from '@common/icons/material/Add';

interface CreateNewButtonProps {
  isCompact?: boolean;
  className?: string;
}
export function CreateNewButton({isCompact, className}: CreateNewButtonProps) {
  const activePage = useDriveStore(s => s.activePage);
  const {uploadFiles} = useDriveUploadQueue();

  const button = isCompact ? (
    <IconButton size="md">
      <AddIcon />
    </IconButton>
  ) : (
    <Button
      className="min-w-160"
      color="primary"
      variant="flat"
      size="sm"
      startIcon={<FileUploadIcon />}
      disabled={!activePage?.canUpload}
    >
      <Trans message="Upload" />
    </Button>
  );

  return (
    <div className={className}>
      <MenuTrigger
        onItemSelected={async value => {
          if (value === 'uploadFiles') {
            uploadFiles(await openUploadWindow({multiple: true}));
          } else if (value === 'uploadFolder') {
            uploadFiles(await openUploadWindow({directory: true}));
          } else if (value === 'newFolder') {
            const activeFolder = driveState().activePage?.folder;
            driveState().setActiveActionDialog(
              'newFolder',
              activeFolder ? [activeFolder] : []
            );
          }
        }}
      >
        {button}
        <Menu>
          <MenuItem value="uploadFiles" startIcon={<UploadFileIcon />}>
            <Trans message="Upload files" />
          </MenuItem>
          <MenuItem value="uploadFolder" startIcon={<DriveFolderUploadIcon />}>
            <Trans message="Upload folder" />
          </MenuItem>
          <MenuItem value="newFolder" startIcon={<CreateNewFolderIcon />}>
            <Trans message="Create folder" />
          </MenuItem>
        </Menu>
      </MenuTrigger>
    </div>
  );
}

import React from 'react';
import {IconButton} from '@common/ui/buttons/icon-button';
import {ArrowBackIcon} from '@common/icons/material/ArrowBack';
import {Breadcrumb} from '@common/ui/breadcrumbs/breadcrumb';
import {FolderIcon} from '@common/icons/material/Folder';
import {BreadcrumbItem} from '@common/ui/breadcrumbs/breadcrumb-item';
import {PartialFolder} from '../../utils/can-move-entries-into';

interface FolderBreadCrumbsProps {
  selectedFolder: PartialFolder;
  allFolders: PartialFolder[];
  rootFolder: PartialFolder;
  onFolderSelected: (folder: PartialFolder) => void;
}
export function MoveEntriesDialogBreadcrumbs({
  selectedFolder,
  allFolders,
  rootFolder,
  onFolderSelected,
}: FolderBreadCrumbsProps) {
  const path: PartialFolder[] = selectedFolder.path
    .split('/')
    .map(part => {
      const folderId = parseInt(part);
      return allFolders.find(folder => folderId === folder.id);
    })
    .filter(f => !!f) as PartialFolder[];
  const fullPath: PartialFolder[] = [rootFolder, ...path];

  const previous = path[path.length - 2];

  return (
    <div className="flex items-center border-b pb-10 gap-6">
      <IconButton
        className="flex-shrink-0"
        variant="outline"
        size="xs"
        radius="rounded"
        disabled={!previous && !selectedFolder.id}
        onClick={() => {
          onFolderSelected(previous || rootFolder);
        }}
      >
        <ArrowBackIcon />
      </IconButton>
      <Breadcrumb size="sm" className="flex-auto">
        {fullPath.map(item => {
          return (
            <BreadcrumbItem
              onSelected={() => {
                onFolderSelected(item);
              }}
              key={item.id || 'root'}
              className="flex items-center gap-8"
            >
              {!item.id && <FolderIcon className="icon-sm" />}
              {item.name}
            </BreadcrumbItem>
          );
        })}
      </Breadcrumb>
    </div>
  );
}

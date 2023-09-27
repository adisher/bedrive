import React, {useMemo} from 'react';
import {IllustratedMessage} from '@common/ui/images/illustrated-message';
import {SvgImage} from '@common/ui/images/svg-image/svg-image';
import {FileTypeIcon} from '@common/uploads/file-type-icon/file-type-icon';
import {ChevronRightIcon} from '@common/icons/material/ChevronRight';
import {Trans} from '@common/i18n/trans';
import {List, ListItem} from '@common/ui/list/list';
import myFilesSvg from './my-files.svg';
import {PartialFolder} from '../../utils/can-move-entries-into';

interface FolderListProps {
  selectedFolder: PartialFolder;
  allFolders: PartialFolder[];
  onFolderSelected: (folder: PartialFolder) => void;
}

export function MoveEntriesDialogFolderList(props: FolderListProps) {
  const {onFolderSelected, selectedFolder, allFolders} = props;

  const subFolders = useMemo(() => {
    const parentId = selectedFolder.id || null;
    return allFolders.filter(f => f.parent_id === parentId);
  }, [selectedFolder.id, allFolders]);

  if (!subFolders.length) {
    return (
      <IllustratedMessage
        size="xs"
        className="pt-64 pb-20 min-h-288"
        image={<SvgImage src={myFilesSvg} />}
        title={
          <Trans
            message={`There are no subfolders in ":folder"`}
            values={{folder: selectedFolder.name}}
          />
        }
      />
    );
  }

  return (
    <List className="h-288 overflow-y-auto">
      {subFolders.map(folder => {
        return (
          <ListItem
            className="border-b min-h-48"
            key={folder.id}
            onSelected={() => {
              onFolderSelected(folder);
            }}
            startIcon={<FileTypeIcon type="folder" />}
            endIcon={<ChevronRightIcon size="md" />}
          >
            {folder.name}
          </ListItem>
        );
      })}
    </List>
  );
}

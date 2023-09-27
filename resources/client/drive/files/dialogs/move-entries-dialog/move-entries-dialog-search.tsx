import React from 'react';
import {SearchIcon} from '@common/icons/material/Search';
import {ComboBox} from '@common/ui/forms/combobox/combobox';
import {Item} from '@common/ui/forms/listbox/item';
import {useTrans} from '@common/i18n/use-trans';
import {PartialFolder} from '../../utils/can-move-entries-into';

interface SearchComboBoxProps {
  allFolders: PartialFolder[];
  onFolderSelected: (folder: PartialFolder) => void;
}
export function MoveEntriesDialogSearch({
  allFolders,
  onFolderSelected,
}: SearchComboBoxProps) {
  const {trans} = useTrans();
  const searchLabel = trans({message: 'Search folders'});
  return (
    <ComboBox
      size="sm"
      maxItems={10}
      placeholder={searchLabel}
      aria-label={searchLabel}
      className="pt-20"
      endAdornmentIcon={<SearchIcon />}
      items={allFolders}
      clearInputOnItemSelection
      onItemSelected={value => {
        const folderId = parseInt(value as string);
        const folder = allFolders.find(f => f.id === folderId);
        if (folder) {
          onFolderSelected(folder);
        }
      }}
    >
      {item => (
        <Item key={item.id} value={item.id}>
          {item.name}
        </Item>
      )}
    </ComboBox>
  );
}

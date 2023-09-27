import {BaseFileGridItem} from '../../../file-view/file-grid/base-file-grid-item';
import React from 'react';
import {DriveEntry} from '../../../files/drive-entry';

export interface FolderPreviewGridProps {
  entries: DriveEntry[];
  onEntrySelected: (entry: DriveEntry, index: number) => void;
}
export function FolderPreviewFileGrid({
  entries,
  onEntrySelected,
}: FolderPreviewGridProps) {
  return (
    <div className="file-grid">
      {entries.map((entry, index) => (
        <BaseFileGridItem
          tabIndex={-1}
          className="hover:shadow-md cursor-pointer bg"
          entry={entry}
          key={entry.id}
          onContextMenu={e => {
            e.preventDefault();
          }}
          onKeyDown={e => {
            if (e.key === 'Enter' || e.key === ' ') {
              onEntrySelected(entry, index);
            }
          }}
          onClick={() => {
            onEntrySelected(entry, index);
          }}
        />
      ))}
    </div>
  );
}

import {FileGridItem} from './file-grid-item';
import React from 'react';
import {DriveEntry} from '../../files/drive-entry';

interface FileGridProps {
  entries: DriveEntry[];
}
export function FileGrid({entries}: FileGridProps) {
  return (
    <div className="file-grid-container">
      <div className="file-grid">
        {entries.map(entry => {
          return <FileGridItem key={entry.id} entry={entry} />;
        })}
      </div>
    </div>
  );
}

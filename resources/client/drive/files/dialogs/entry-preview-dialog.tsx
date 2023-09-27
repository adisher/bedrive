import {FilePreviewDialog} from '@common/uploads/preview/file-preview-dialog';
import {DriveEntry} from '../drive-entry';
import {createElement, useState} from 'react';
import {useShareAction} from '../../entry-actions/use-entry-actions';
import {Button} from '@common/ui/buttons/button';
import {Trans} from '@common/i18n/trans';
import {useIsMobileMediaQuery} from '@common/utils/hooks/is-mobile-media-query';
import {IconButton} from '@common/ui/buttons/icon-button';
import {useEntries} from '../queries/use-entries';

interface EntryPreviewDialogProps {
  selectedEntry: DriveEntry;
}
export function EntryPreviewDialog({selectedEntry}: EntryPreviewDialogProps) {
  const files = useEntries().filter(entry => entry.type !== 'folder');
  const defaultActiveIndex = files.findIndex(
    file => file.id === selectedEntry?.id
  );
  const [activeIndex, setActiveIndex] = useState(defaultActiveIndex);
  return (
    <FilePreviewDialog
      allowDownload={selectedEntry.permissions['files.download']}
      headerActionsLeft={
        <DriveActions activeIndex={activeIndex} entries={files} />
      }
      activeIndex={activeIndex}
      onActiveIndexChange={setActiveIndex}
      entries={files}
    />
  );
}

interface DriveActionsProps {
  activeIndex: number;
  entries: DriveEntry[];
}
function DriveActions({activeIndex, entries}: DriveActionsProps) {
  const selectedEntry = entries[activeIndex];
  const share = useShareAction([selectedEntry]);
  const isMobile = useIsMobileMediaQuery();
  if (!selectedEntry || !share) return null;

  if (isMobile) {
    return (
      <IconButton
        onClick={() => {
          share.execute();
        }}
      >
        {createElement(share.icon)}
      </IconButton>
    );
  }

  return (
    <Button
      variant="text"
      startIcon={createElement(share.icon)}
      onClick={() => {
        share.execute();
      }}
    >
      <Trans {...share.label} />
    </Button>
  );
}

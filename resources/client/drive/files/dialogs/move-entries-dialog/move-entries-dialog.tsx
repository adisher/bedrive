import React, {useState} from 'react';
import {Button} from '@common/ui/buttons/button';
import {useFolders} from '../../queries/use-folders';
import {useMoveEntries} from '../../queries/use-move-entries';
import {NewFolderDialog} from '../new-folder-dialog';
import {CreateNewFolderIcon} from '@common/icons/material/CreateNewFolder';
import {MoveEntriesDialogSearch} from './move-entries-dialog-search';
import {MoveEntriesDialogBreadcrumbs} from './move-entries-dialog-breadcrumbs';
import {MoveEntriesDialogFolderList} from './move-entries-dialog-folder-list';
import {DialogTrigger} from '@common/ui/overlays/dialog/dialog-trigger';
import {DialogFooter} from '@common/ui/overlays/dialog/dialog-footer';
import {useDialogContext} from '@common/ui/overlays/dialog/dialog-context';
import {Dialog} from '@common/ui/overlays/dialog/dialog';
import {DialogHeader} from '@common/ui/overlays/dialog/dialog-header';
import {DialogBody} from '@common/ui/overlays/dialog/dialog-body';
import {Trans} from '@common/i18n/trans';
import {RootFolderPage} from '../../../drive-page/drive-page';
import {DriveEntry} from '../../drive-entry';
import {useDriveStore} from '../../../drive-store';
import {useIsMobileMediaQuery} from '@common/utils/hooks/is-mobile-media-query';
import {
  canMoveEntriesInto,
  PartialFolder,
} from '../../utils/can-move-entries-into';

interface MoveEntriesDialogProps {
  entries: DriveEntry[];
}
export function MoveEntriesDialog({entries}: MoveEntriesDialogProps) {
  const {data} = useFolders();
  const allFolders = data?.folders || [];
  const activePage = useDriveStore(s => s.activePage);
  const [selectedFolder, setSelectedFolder] = useState<PartialFolder>(
    activePage?.folder || RootFolderPage.folder
  );

  return (
    <Dialog size="lg">
      <DialogHeader>
        <Trans
          message="Move [one ‘:name‘|other :count items]"
          values={{
            count: entries.length,
            name: entries[0].name,
          }}
        />
      </DialogHeader>
      <DialogBody>
        <div className="text-sm">
          <Trans message="Select a destination folder." />
        </div>
        <MoveEntriesDialogSearch
          allFolders={allFolders}
          onFolderSelected={setSelectedFolder}
        />
        <div className="mt-40 mb-20">
          <MoveEntriesDialogBreadcrumbs
            selectedFolder={selectedFolder}
            allFolders={allFolders}
            rootFolder={RootFolderPage.folder}
            onFolderSelected={setSelectedFolder}
          />
          <MoveEntriesDialogFolderList
            selectedFolder={selectedFolder}
            allFolders={allFolders}
            onFolderSelected={setSelectedFolder}
          />
        </div>
      </DialogBody>
      <Footer
        selectedFolder={selectedFolder}
        setSelectedFolder={setSelectedFolder}
        entries={entries}
      />
    </Dialog>
  );
}

interface FooterProps {
  selectedFolder: PartialFolder;
  setSelectedFolder: (folder: PartialFolder) => void;
  entries: DriveEntry[];
}
function Footer({selectedFolder, setSelectedFolder, entries}: FooterProps) {
  const {close} = useDialogContext();
  const isMobile = useIsMobileMediaQuery();
  const moveEntries = useMoveEntries();
  return (
    <DialogFooter
      className="border-t"
      startAction={
        <DialogTrigger
          type="modal"
          onClose={folder => {
            if (folder) {
              setSelectedFolder(folder);
            }
          }}
        >
          <Button startIcon={<CreateNewFolderIcon />} variant="text">
            <Trans message="New Folder" />
          </Button>
          <NewFolderDialog parentId={selectedFolder.id} />
        </DialogTrigger>
      }
    >
      {!isMobile && (
        <Button variant="flat" onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
      )}
      <Button
        type="submit"
        variant="flat"
        color="primary"
        disabled={
          !canMoveEntriesInto(entries, selectedFolder) || moveEntries.isLoading
        }
        onClick={() => {
          moveEntries.mutate(
            {
              destinationId: selectedFolder.id,
              entryIds: entries.map(e => e.id),
            },
            {onSuccess: close}
          );
        }}
      >
        <Trans message="Move here" />
      </Button>
    </DialogFooter>
  );
}

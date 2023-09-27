import {Fragment, useState} from 'react';
import {AnimatePresence, m} from 'framer-motion';
import {SharePanel} from './share-panel';
import {ShareableLinkPanel} from './link-panel/shareable-link-panel';
import {LinkSettingsDialog} from './link-panel/link-settings-dialog';
import {Dialog} from '@common/ui/overlays/dialog/dialog';
import {DialogHeader} from '@common/ui/overlays/dialog/dialog-header';
import {DialogBody} from '@common/ui/overlays/dialog/dialog-body';
import {Trans} from '@common/i18n/trans';
import {DriveEntry} from '../files/drive-entry';
import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {DriveQueryKeys} from '../drive-query-keys';

export type ShareDialogActivePanel = 'main' | 'linkSettings';

interface ShareDialogProps {
  entry: DriveEntry;
  focusLinkInput?: boolean;
}
export function ShareDialog({
  entry: initialEntry,
  focusLinkInput,
}: ShareDialogProps) {
  const {
    data: {fileEntry},
  } = useQuery(
    DriveQueryKeys.fetchFileEntry(initialEntry.id),
    () => {
      return apiClient
        .get(`drive/file-entries/${initialEntry.id}/model`)
        .then(response => response.data as {fileEntry: DriveEntry});
    },
    {
      initialData: {fileEntry: initialEntry},
    }
  );

  const [activePanel, setActivePanel] =
    useState<ShareDialogActivePanel>('main');

  return (
    <Dialog size="lg">
      <AnimatePresence initial={false} mode="wait">
        {activePanel === 'linkSettings' ? (
          <LinkSettingsDialog
            key="one"
            setActivePanel={setActivePanel}
            entry={fileEntry}
          />
        ) : (
          <MainDialog
            key="two"
            setActivePanel={setActivePanel}
            entry={fileEntry}
            focusLinkInput={focusLinkInput}
          />
        )}
      </AnimatePresence>
    </Dialog>
  );
}

interface MainDialogProps {
  setActivePanel: (name: ShareDialogActivePanel) => void;
  entry: DriveEntry;
  focusLinkInput?: boolean;
}
function MainDialog({setActivePanel, entry, focusLinkInput}: MainDialogProps) {
  return (
    <Fragment>
      <DialogHeader>
        <Trans message="Share ‘:name’" values={{name: entry.name}} />
      </DialogHeader>
      <DialogBody className="relative">
        <m.div
          key="share-content"
          animate={{opacity: 1, y: 0}}
          initial={{opacity: 0, y: 20}}
          exit={{opacity: 0, y: -20}}
          transition={{duration: 0.1}}
        >
          <SharePanel className="border-b pb-30 mb-30" entry={entry} />
          <ShareableLinkPanel
            setActivePanel={setActivePanel}
            entry={entry}
            focusInput={!!focusLinkInput}
          />
        </m.div>
      </DialogBody>
    </Fragment>
  );
}

import React from 'react';
import {FileTypeIcon} from '@common/uploads/file-type-icon/file-type-icon';
import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {useShareableLinkPage} from '../queries/use-shareable-link-page';
import {ShareableLinkPageActionButtons} from './shareable-link-page-action-buttons';

export function ShareableLinkNavbar() {
  const {link} = useShareableLinkPage();
  return (
    <Navbar
      size="md"
      color="bg"
      className="flex-shrink-0"
      rightChildren={link?.entry && <ShareableLinkPageActionButtons />}
      menuPosition="shareable-link-page"
    >
      {link?.entry && link.entry.type !== 'folder' && (
        <div className="flex items-center gap-10">
          <FileTypeIcon className="flex-shrink-0" type={link.entry.type} />
          <div className="font-medium whitespace-nowrap overflow-hidden overflow-ellipsis flex-auto">
            {link.entry.name}
          </div>
        </div>
      )}
    </Navbar>
  );
}

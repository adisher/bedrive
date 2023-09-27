import React from 'react';
import {FilePreviewContainer} from '@common/uploads/preview/file-preview-container';
import {useShareableLinkPage} from '../queries/use-shareable-link-page';
import {ShareableLinkNavbar} from './shareable-link-navbar';
import {AdHost} from '@common/admin/ads/ad-host';

export function ShareableLinkPageFilePreview() {
  const {link} = useShareableLinkPage();

  if (!link?.entry) return null;

  return (
    <div className="flex flex-col w-full h-full bg-alt">
      <ShareableLinkNavbar />
      <AdHost slot="file-preview" className="mt-24" />
      <FilePreviewContainer
        entries={[link.entry]}
        showHeader={false}
        allowDownload={link.allow_download}
      />
    </div>
  );
}

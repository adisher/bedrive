import React, {ReactElement} from 'react';
import {useShareableLinkPage} from '../queries/use-shareable-link-page';
import {useLinkPageStore} from './link-page-store';
import {useTrans} from '@common/i18n/use-trans';
import {ProgressCircle} from '@common/ui/progress/progress-circle';
import {PasswordPage} from './password-page';
import {FolderPreview} from './folder-preview/folder-preview';
import {ShareableLinkPageFilePreview} from './shareable-link-page-file-preview';
import {FileEntryUrlsContext} from '@common/uploads/hooks/file-entry-urls';
import {NotFoundPage} from '@common/ui/not-found-page/not-found-page';

export function ShareableLinkPage() {
  const {status, link} = useShareableLinkPage();
  const {trans} = useTrans();
  const isPasswordProtected = useLinkPageStore(s => s.isPasswordProtected);
  const password = useLinkPageStore(s => s.password);

  let content: ReactElement;

  if (status === 'loading') {
    content = (
      <div className="flex-auto flex items-center justify-center w-full h-full">
        <ProgressCircle
          aria-label={trans({message: 'Loading link'})}
          isIndeterminate
        />
      </div>
    );
  } else if (!link && !isPasswordProtected) {
    return <NotFoundPage />;
  } else if (isPasswordProtected && !password) {
    content = <PasswordPage />;
  } else if (link?.entry?.type === 'folder') {
    content = <FolderPreview />;
  } else {
    content = <ShareableLinkPageFilePreview />;
  }

  return (
    <FileEntryUrlsContext.Provider value={{shareable_link: link?.id, password}}>
      {content}
    </FileEntryUrlsContext.Provider>
  );
}

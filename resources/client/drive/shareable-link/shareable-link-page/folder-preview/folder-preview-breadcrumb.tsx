import React, {ReactElement, ReactNode} from 'react';
import clsx from 'clsx';
import {DriveFolder} from '../../../files/drive-entry';
import {useFolderPath} from '../../../files/queries/use-folder-path';
import {Breadcrumb} from '@common/ui/breadcrumbs/breadcrumb';
import {ShareableLink} from '../../shareable-link';
import {useLinkPageStore} from '../link-page-store';
import {BreadcrumbItem} from '@common/ui/breadcrumbs/breadcrumb-item';
import {useNavigateToSubfolder} from './use-navigate-to-subfolder';

interface Props {
  className?: string;
  folder?: DriveFolder;
  link: ShareableLink;
}
export function FolderPreviewBreadcrumb({className, folder, link}: Props) {
  const navigateToSubfolder = useNavigateToSubfolder();
  const password = useLinkPageStore(s => s.password);
  const query = useFolderPath({
    hash: folder?.hash,
    params: {
      shareable_link: link.id,
      password,
    },
  });

  let content: ReactNode;

  if (query.isLoading) {
    content = null;
  } else {
    const items: {folder: DriveFolder; label: ReactElement}[] = [];
    if (query.data) {
      query.data.path.forEach(parent => {
        items.push({
          folder: parent,
          label: <>{parent.name}</>,
        });
      });
    }

    content = (
      <Breadcrumb size="lg" isNavigation>
        {items.map(item => {
          return (
            <BreadcrumbItem
              onSelected={() => {
                navigateToSubfolder(item.folder.hash);
              }}
              key={item.folder.hash}
            >
              {item.label}
            </BreadcrumbItem>
          );
        })}
      </Breadcrumb>
    );
  }

  return <div className={clsx('h-36 flex-shrink-0', className)}>{content}</div>;
}

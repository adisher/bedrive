import {AnimatePresence, m} from 'framer-motion';
import React, {Fragment, useEffect, useRef, useState} from 'react';
import {ProgressCircle} from '@common/ui/progress/progress-circle';
import {useShareableLinkPage} from '../../queries/use-shareable-link-page';
import {FilePreviewDialog} from '@common/uploads/preview/file-preview-dialog';
import {DialogTrigger} from '@common/ui/overlays/dialog/dialog-trigger';
import clsx from 'clsx';
import {DriveEntry} from '../../../files/drive-entry';
import {FolderPreviewFileGrid} from './folder-preview-file-grid';
import {useLinkPageStore} from '../link-page-store';
import {FolderPreviewFileTable} from './folder-preview-file-table';
import {useNavigateToSubfolder} from './use-navigate-to-subfolder';
import {useLocation} from 'react-router-dom';
import {opacityAnimation} from '@common/ui/animation/opacity-animation';
import {AdHost} from '@common/admin/ads/ad-host';

interface FolderPreviewChildrenProps {
  className?: string;
}
export function FolderPreviewFileView({className}: FolderPreviewChildrenProps) {
  const {pathname} = useLocation();
  const navigateToSubfolder = useNavigateToSubfolder();
  const [activePreviewIndex, setActivePreviewIndex] = useState<number>();
  const viewMode = useLinkPageStore(s => s.viewMode);
  const sentinelRef = useRef<HTMLDivElement>(null);
  const {
    link,
    entries,
    isFetchingNextPage,
    hasNextPage,
    fetchNextPage,
    isPreviousData,
  } = useShareableLinkPage();

  // close preview modal on back/forward navigation
  useEffect(() => {
    setActivePreviewIndex(undefined);
  }, [pathname]);

  useEffect(() => {
    const sentinelEl = sentinelRef.current;
    if (!sentinelEl) return;
    const observer = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting && hasNextPage) {
        fetchNextPage();
      }
    });
    observer.observe(sentinelEl);
    return () => {
      observer.unobserve(sentinelEl);
    };
  }, [hasNextPage, fetchNextPage]);

  if (!link || isPreviousData) {
    return (
      <div className={clsx('flex justify-center', className)}>
        <ProgressCircle isIndeterminate />
      </div>
    );
  }

  const handlePreview = (entry: DriveEntry, index: number) => {
    if (entry.type === 'folder') {
      navigateToSubfolder(entry.hash);
    } else {
      setActivePreviewIndex(index);
    }
  };

  const folderEntries = entries || [];

  return (
    <Fragment>
      <AdHost slot="file-preview" className="mb-40" />
      <div
        className={clsx(
          'px-14 pb-14 md:px-24 md:pb-24 flex-auto overflow-auto file-grid-container',
          className
        )}
      >
        {viewMode === 'grid' ? (
          <FolderPreviewFileGrid
            entries={folderEntries}
            onEntrySelected={handlePreview}
          />
        ) : (
          <FolderPreviewFileTable
            entries={folderEntries}
            onEntrySelected={handlePreview}
          />
        )}
        <span ref={sentinelRef} aria-hidden />
        <AnimatePresence>
          {isFetchingNextPage && (
            <m.div
              className="flex justify-center mt-24 w-full"
              {...opacityAnimation}
            >
              <ProgressCircle isIndeterminate aria-label="loading" />
            </m.div>
          )}
        </AnimatePresence>
      </div>
      <DialogTrigger
        type="modal"
        isOpen={activePreviewIndex != undefined}
        onClose={() => setActivePreviewIndex(undefined)}
      >
        <FilePreviewDialog
          entries={folderEntries}
          defaultActiveIndex={activePreviewIndex}
          allowDownload={link.allow_download}
        />
      </DialogTrigger>
    </Fragment>
  );
}

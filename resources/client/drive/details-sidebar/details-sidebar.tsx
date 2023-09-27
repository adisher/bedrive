import React, {Fragment} from 'react';
import {useSelectedEntry} from '../files/use-selected-entries';
import {IllustratedMessage} from '@common/ui/images/illustrated-message';
import {SvgImage} from '@common/ui/images/svg-image/svg-image';
import {Trans} from '@common/i18n/trans';
import detailedExamination from './detailed-examination.svg';
import clsx from 'clsx';
import {DetailsSidebarHeader} from '@app/drive/details-sidebar/details-sidebar-header';
import {DetailsSidebarProperties} from '@app/drive/details-sidebar/details-sidebar-properties';

interface DetailsSidebarProps {
  className?: string;
}
export function DetailsSidebar({className}: DetailsSidebarProps) {
  const selectedEntry = useSelectedEntry();
  return (
    <div
      className={clsx(
        className,
        'bg p-18 text-sm text-muted border-l h-full overflow-y-auto'
      )}
    >
      {selectedEntry ? (
        <DetailsSidebarProperties entry={selectedEntry} />
      ) : (
        <NothingSelected />
      )}
    </div>
  );
}

function NothingSelected() {
  return (
    <Fragment>
      <DetailsSidebarHeader
        entryType="folder"
        entryName={<Trans message="All files" />}
      />
      <IllustratedMessage
        image={<SvgImage src={detailedExamination} />}
        description={
          <Trans message="Select file or folder to see details here" />
        }
      />
    </Fragment>
  );
}

import React from 'react';
import {useStorageSummary} from './storage-summary';
import {StorageIcon} from '@common/icons/material/Storage';
import {Meter} from '@common/ui/progress/meter';
import {Trans} from '@common/i18n/trans';
import clsx from 'clsx';

export function StorageMeter() {
  const {isLoading, data} = useStorageSummary();

  // prevent translation placeholders from showing if summary is not loaded yet
  const label = (
    <span className={clsx('whitespace-nowrap', isLoading && 'invisible')}>
      <Trans
        message=":used of :available used"
        values={{
          used: data?.usedFormatted,
          available: data?.availableFormatted,
        }}
      />
    </span>
  );
  return (
    <div className="pl-24 pt-24 mt-24 border-t flex items-start gap-16">
      <StorageIcon className="icon-md -mt-4" />
      <Meter
        className="flex-auto max-w-144"
        size="xs"
        value={data?.percentage}
        label={label}
        showValueLabel={false}
        labelPosition="bottom"
      />
    </div>
  );
}

import {useLinkifiedString} from '@common/utils/hooks/use-linkified-string';
import {Fragment, useLayoutEffect, useRef, useState} from 'react';
import {Button} from '@common/ui/buttons/button';
import {Trans} from '@common/i18n/trans';
import clsx from 'clsx';

interface TruncatedDescriptionProps {
  description?: string;
  className?: string;
}

export function TruncatedDescription({
  description,
  className,
}: TruncatedDescriptionProps) {
  const linkifiedDescription = useLinkifiedString(description);
  const wrapperRef = useRef<HTMLDivElement>(null);
  const contentRef = useRef<HTMLDivElement>(null);
  const [isOverflowing, setIsOverflowing] = useState(false);
  const [isShowingAll, setIsShowingAll] = useState(false);

  useLayoutEffect(() => {
    const wrapperHeight =
      wrapperRef.current?.getBoundingClientRect().height || 0;
    const contentHeight = wrapperRef.current?.scrollHeight || 0;
    if (contentHeight > wrapperHeight) {
      setIsOverflowing(true);
    }
  }, []);

  if (!linkifiedDescription) return null;

  return (
    <Fragment>
      <div
        ref={wrapperRef}
        className={clsx(
          'relative',
          className,
          !isShowingAll && 'max-h-160 overflow-hidden',
          !isShowingAll &&
            isOverflowing &&
            'after:absolute after:bottom-0 after:left-0 after:w-full after:h-20 after:bg-gradient-to-b after:from-transparent after:to-background'
        )}
      >
        <div
          ref={contentRef}
          dangerouslySetInnerHTML={{__html: linkifiedDescription}}
        />
      </div>
      {isOverflowing && (
        <Button
          size="xs"
          className="mt-20"
          variant="outline"
          onClick={() => setIsShowingAll(!isShowingAll)}
        >
          {isShowingAll ? (
            <Trans message="Show less" />
          ) : (
            <Trans message="Show more" />
          )}
        </Button>
      )}
    </Fragment>
  );
}

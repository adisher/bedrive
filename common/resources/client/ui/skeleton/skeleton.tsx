import clsx from 'clsx';

interface SkeletonProps {
  variant?: 'avatar' | 'text' | 'rect' | 'icon';
  animation?: 'pulsate' | 'wave' | null; // disable animation completely with null
  className?: string;
  size?: string;
  display?: string;
  radius?: string;
}
export function Skeleton({
  variant = 'text',
  animation = 'wave',
  size,
  className,
  display = 'block',
  radius = 'rounded',
}: SkeletonProps) {
  return (
    <span
      className={clsx(
        'overflow-hidden relative bg-fg-base/4 bg-no-repeat will-change-transform',
        radius,
        skeletonSize({variant, size}),
        display,
        variant === 'text' &&
          'before:content-["\\00a0"] scale-y-[0.6] origin-[0_55%]',
        variant === 'avatar' && 'flex-shrink-0',
        variant === 'icon' && 'mx-8 flex-shrink-0',
        animation === 'wave' && 'skeleton-wave',
        animation === 'pulsate' && 'skeleton-pulsate',
        className
      )}
      aria-busy
      aria-live="polite"
    />
  );
}

interface SkeletonSizeProps {
  variant: SkeletonProps['variant'];
  size: SkeletonProps['size'];
}
function skeletonSize({variant, size}: SkeletonSizeProps): string | undefined {
  if (size) {
    return size;
  }

  switch (variant) {
    case 'avatar':
      return 'h-40 w-40';
    case 'icon':
      return 'h-24 h-24';
    case 'rect':
      return 'h-full w-full';
    default:
      return 'w-full';
  }
}

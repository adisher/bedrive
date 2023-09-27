import React, {ReactNode} from 'react';
import clsx from 'clsx';

interface Props {
  children: ReactNode;
  margin?: string;
}
export function DetailsSidebarSectionHeader({
  children,
  margin = 'mb-20',
}: Props) {
  return <div className={clsx('text-base text-main', margin)}>{children}</div>;
}

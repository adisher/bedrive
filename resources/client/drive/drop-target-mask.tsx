import {AnimatePresence, m} from 'framer-motion';
import {Trans} from '@common/i18n/trans';
import React from 'react';
import {opacityAnimation} from '@common/ui/animation/opacity-animation';

interface DropTargetMaskProps {
  isVisible: boolean;
}
export function DropTargetMask({isVisible}: DropTargetMaskProps) {
  const mask = (
    <m.div
      key="dragTargetMask"
      {...opacityAnimation}
      transition={{duration: 0.3}}
      className="absolute inset-0 w-full min-h-full bg-primary-light/30 border-2 border-dashed border-primary pointer-events-none"
    >
      <m.div
        initial={{y: '100%', opacity: 0}}
        animate={{y: '-10px', opacity: 1}}
        exit={{y: '100%', opacity: 0}}
        className="p-10 bg-primary text-on-primary fixed bottom-0 left-0 right-0 max-w-max mx-auto rounded"
      >
        <Trans message="Drop files to upload them to this folder." />
      </m.div>
    </m.div>
  );
  return <AnimatePresence>{isVisible ? mask : null}</AnimatePresence>;
}

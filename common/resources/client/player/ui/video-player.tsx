import {useEffect, useRef} from 'react';
import {PlayerContext} from '@common/player/player-context';
import {MediaItem} from '@common/player/media-item';
import {PlayerOutlet} from '@common/player/ui/player-outlet';
import {PlayButton} from '@common/player/ui/controls/play-button';
import {VolumeControls} from '@common/player/ui/controls/volume-controls';
import {Seekbar} from '@common/player/ui/controls/seeking/seekbar';
import {FormattedCurrentTime} from '@common/player/ui/controls/formatted-current-time';
import {FormattedPlayerDuration} from '@common/player/ui/controls/formatted-player-duration';
import {usePlayerActions} from '@common/player/hooks/use-player-actions';
import {usePlayerStore} from '@common/player/hooks/use-player-store';
import clsx from 'clsx';
import {NextButton} from '@common/player/ui/controls/next-button';
import {FullscreenButton} from '@common/player/ui/controls/fullscreen-button';
import {PipButton} from '@common/player/ui/controls/pip-button';
import {PlaybackOptionsButton} from '@common/player/ui/controls/playback-options-button';
import {ToggleCaptionsButton} from '@common/player/ui/controls/toggle-captions-button';
import {PlayerPoster} from '@common/player/ui/controls/player-poster';
import {usePlayerClickHandler} from '@common/player/hooks/use-player-click-handler';
import {IconButton} from '@common/ui/buttons/icon-button';
import {MediaPlayIcon} from '@common/icons/media/media-play';
import {BufferingSpinner} from '@common/player/ui/controls/buffering-spinner';
import {guessPlayerProvider} from '@common/player/utils/guess-player-provider';
import {usePrevious} from '@common/utils/hooks/use-previous';

interface Props {
  id: string;
  queue?: MediaItem[];
  cuedMediaId?: string;
  autoPlay?: boolean;
  src?: string;
}
export function VideoPlayer({id, queue, cuedMediaId, autoPlay, src}: Props) {
  return (
    <PlayerContext
      id={id}
      options={{
        autoPlay,
        initialData: {
          queue: queue ? queue : [mediaItemFromSrc(src!)],
          cuedMediaId,
        },
      }}
    >
      <QueueOverrider src={src} queue={queue} />
      <Player />
    </PlayerContext>
  );
}

function Player() {
  const timerRef = useRef<number | null>();
  const {setControlsVisible, getState} = usePlayerActions();

  const clickHandler = usePlayerClickHandler();

  return (
    <div
      className="relative isolate aspect-video bg-black fullscreen-host"
      onClick={clickHandler}
      onPointerEnter={() => {
        setControlsVisible(true);
        if (timerRef.current) {
          clearTimeout(timerRef.current);
          timerRef.current = null;
        }
      }}
      onPointerLeave={e => {
        if (timerRef.current) {
          clearTimeout(timerRef.current);
          timerRef.current = null;
        }
        if (!getState().isPlaying) {
          return;
        }
        timerRef.current = window.setTimeout(() => {
          setControlsVisible(false);
        }, 2500);
      }}
    >
      <PlayerOutlet className="w-full h-full z-50" />
      <Blocker />
      <PlayerPoster className="absolute inset-0 z-30" />
      <OverlayButtons />
      <BufferingSpinner
        className="absolute w-50 h-50 m-auto inset-0 pointer-events-none z-40 spinner"
        fillColor="border-white"
        trackColor="border-white/30"
        size="w-50 h-50"
      />
      <BottomGradient />
      <Controls />
    </div>
  );
}

function OverlayButtons() {
  const isPlaying = usePlayerStore(s => s.isPlaying);
  return (
    <div
      className={clsx(
        'absolute top-0 left-0 w-full h-full flex items-center justify-center transition-opacity z-40',
        !isPlaying ? 'opacity-100' : 'opacity-0 pointer-events-none'
      )}
    >
      <IconButton color="primary" variant="raised" size="lg">
        <MediaPlayIcon />
      </IconButton>
    </div>
  );
}

// required in order for "onPointerEnter" to fire consistently when player provider is iframe
function Blocker() {
  return <div className="absolute inset-0 z-20" />;
}

function BottomGradient() {
  const controlsVisible = usePlayerStore(s => s.controlsVisible);
  return (
    <div
      className={clsx(
        'player-bottom-gradient absolute w-full h-2/4 pt-36 bottom-0 bg-bottom bg-repeat-x transition-opacity duration-300 pointer-events-none z-30',
        controlsVisible ? 'opacity-100' : 'opacity-0'
      )}
    />
  );
}

function Controls() {
  const controlsVisible = usePlayerStore(s => s.controlsVisible);
  return (
    <div
      onClick={e => e.stopPropagation()}
      className={clsx(
        'player-bottom-text-shadow absolute bottom-0 left-0 right-0 text-white/87 p-8 transition-opacity duration-300 z-40',
        controlsVisible ? 'opacity-100' : 'opacity-0'
      )}
    >
      <Seekbar trackColor="bg-white/40" />
      <div className="flex items-center gap-4 w-full">
        <PlayButton color="white" />
        <NextButton color="white" />
        <VolumeControls
          fillColor="bg-white"
          trackColor="bg-white/20"
          buttonColor="white"
        />
        <span className="text-sm ml-10">
          <FormattedCurrentTime className="min-w-40 text-right" /> /{' '}
          <FormattedPlayerDuration className="min-w-40 text-right" />
        </span>
        <div className="flex items-center gap-4 flex-shrink-0 ml-auto">
          <ToggleCaptionsButton color="white" />
          <PlaybackOptionsButton color="white" />
          <FullscreenButton className="ml-auto" color="white" />
          <PipButton color="white" />
        </div>
      </div>
    </div>
  );
}

function mediaItemFromSrc(src: string): MediaItem {
  return {
    id: src,
    src,
    provider: guessPlayerProvider(src),
  };
}

interface QueueOverriderProps {
  src?: string;
  queue?: MediaItem[];
}
function QueueOverrider({src, queue}: QueueOverriderProps) {
  const {getState, overrideQueue} = usePlayerActions();

  const queueKey = queue?.map(item => item.id).join('-') ?? '';
  const previousKey = usePrevious(queueKey);

  // override queue when any of specified queue item id or order changes
  useEffect(() => {
    if (queue && previousKey && queueKey && previousKey !== queueKey) {
      overrideQueue(queue);
    }
  }, [queueKey, previousKey, queue, overrideQueue]);

  // override queue when src changes
  useEffect(() => {
    if (src && getState().cuedMedia?.src !== src) {
      overrideQueue([mediaItemFromSrc(src)]);
    }
  }, [src, getState, overrideQueue]);

  return null;
}

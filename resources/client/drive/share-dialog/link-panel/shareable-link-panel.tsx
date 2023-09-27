import useClipboard from 'react-use-clipboard';
import {useEntryShareableLink} from '../../shareable-link/queries/use-entry-shareable-link';
import {useCreateShareableLink} from '../../shareable-link/queries/create-shareable-link';
import {useDeleteShareableLink} from '../../shareable-link/queries/use-delete-shareable-link';
import {Button} from '@common/ui/buttons/button';
import {ShareableLink} from '../../shareable-link/shareable-link';
import {TextField} from '@common/ui/forms/input-field/text-field/text-field';
import type {ShareDialogActivePanel} from '../share-dialog';
import {Switch} from '@common/ui/forms/toggle/switch';
import {randomString} from '@common/utils/string/random-string';
import {Trans} from '@common/i18n/trans';
import {useTrans} from '@common/i18n/use-trans';
import {useActiveDialogEntry} from '../../drive-store';
import {DriveEntry} from '../../files/drive-entry';
import {useSettings} from '@common/core/settings/use-settings';

interface ShareableLinkPanelProps {
  setActivePanel: (name: ShareDialogActivePanel) => void;
  entry: DriveEntry;
  focusInput?: boolean;
}
export function ShareableLinkPanel({
  setActivePanel,
  entry,
  focusInput,
}: ShareableLinkPanelProps) {
  const query = useEntryShareableLink(entry.id);
  const linkExists = !!query.data?.link;
  const createLink = useCreateShareableLink();
  const deleteLink = useDeleteShareableLink();
  const isLoading =
    query.isLoading || createLink.isLoading || deleteLink.isLoading;
  return (
    <div>
      <div className="mb-10">
        <Trans message="Share link" />
      </div>
      <div className="flex items-center gap-14 justify-between pb-4 px-2">
        <Switch
          checked={linkExists}
          disabled={isLoading}
          onChange={() => {
            if (linkExists) {
              deleteLink.mutate({entryId: entry.id});
            } else {
              createLink.mutate({entryId: entry.id});
            }
          }}
        >
          {linkExists ? (
            <Trans message="Shareable link is created" />
          ) : (
            <Trans message="Create shareable link" />
          )}
        </Switch>
        {linkExists && (
          <Button
            variant="link"
            color="primary"
            onClick={() => {
              setActivePanel('linkSettings');
            }}
          >
            <Trans message="Link settings" />
          </Button>
        )}
      </div>
      <ShareableLinkInput autoFocus={focusInput} link={query.data?.link} />
    </div>
  );
}

interface ShareableLinkInputProps {
  link?: ShareableLink | null;
  autoFocus?: boolean;
}
function ShareableLinkInput({link, autoFocus}: ShareableLinkInputProps) {
  const {base_url} = useSettings();
  const {trans} = useTrans();
  const entry = useActiveDialogEntry();
  const hash = link?.hash || entry?.hash || randomString();
  const linkUrl = `${base_url}/drive/s/${hash}`;
  const [isCopied, setCopied] = useClipboard(linkUrl, {
    successDuration: 1000,
  });
  return (
    <TextField
      autoFocus={autoFocus}
      disabled={!link}
      className="mt-10"
      readOnly
      value={linkUrl}
      aria-label={trans({message: 'Shareable link'})}
      onFocus={e => {
        (e.target as HTMLInputElement).select();
      }}
      endAppend={
        <Button
          className="min-w-100"
          variant="flat"
          color="primary"
          onClick={setCopied}
        >
          {isCopied ? <Trans message="Copied!" /> : <Trans message="Copy" />}
        </Button>
      }
    />
  );
}

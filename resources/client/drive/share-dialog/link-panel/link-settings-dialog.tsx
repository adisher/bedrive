import {useForm} from 'react-hook-form';
import {Fragment, ReactElement, useState} from 'react';
import clsx from 'clsx';
import {m} from 'framer-motion';
import {getLocalTimeZone, now} from '@internationalized/date';
import {Button} from '@common/ui/buttons/button';
import {FormTextField} from '@common/ui/forms/input-field/text-field/text-field';
import {DialogFooter} from '@common/ui/overlays/dialog/dialog-footer';
import type {ShareDialogActivePanel} from '../share-dialog';
import {useEntryShareableLink} from '../../shareable-link/queries/use-entry-shareable-link';
import {Form} from '@common/ui/forms/form';
import {
  UpdateShareableLinkPayload,
  useUpdateShareableLink,
} from '../../shareable-link/queries/use-update-shareable-link';
import {FormSwitch, Switch} from '@common/ui/forms/toggle/switch';
import {toast} from '@common/ui/toast/toast';
import {useDialogContext} from '@common/ui/overlays/dialog/dialog-context';
import {DialogHeader} from '@common/ui/overlays/dialog/dialog-header';
import {DialogBody} from '@common/ui/overlays/dialog/dialog-body';
import {FormDatePicker} from '@common/ui/forms/input-field/date/date-picker/date-picker';
import {useTrans} from '@common/i18n/use-trans';
import {Trans} from '@common/i18n/trans';
import {message} from '@common/i18n/message';
import {FileEntry} from '@common/uploads/file-entry';

interface LinkSettingsDialogProps {
  className?: string;
  setActivePanel: (name: ShareDialogActivePanel) => void;
  entry: FileEntry;
}
export function LinkSettingsDialog({
  className,
  setActivePanel,
  entry,
}: LinkSettingsDialogProps) {
  const {formId} = useDialogContext();
  const {data} = useEntryShareableLink(entry.id);
  const link = data?.link;
  const form = useForm<UpdateShareableLinkPayload>({
    defaultValues: {
      allowDownload: link?.allow_download,
      allowEdit: link?.allow_edit,
      expiresAt: link?.expires_at,
      entryId: entry.id,
    },
  });
  const updateLink = useUpdateShareableLink(form);

  return (
    <Fragment>
      <DialogHeader
        onDismiss={() => {
          setActivePanel('main');
        }}
      >
        <Trans message="Shareable Link Settings" />
      </DialogHeader>
      <DialogBody>
        <m.div
          key="link-settings-content"
          className="min-h-[335px]"
          animate={{opacity: 1, y: 0}}
          initial={{opacity: 0, y: 20}}
          exit={{opacity: 0, y: -20}}
          transition={{duration: 0.1}}
        >
          <Form
            id={formId}
            className={className}
            form={form}
            onSubmit={value => {
              updateLink.mutate(value, {
                onSuccess: () => {
                  setActivePanel('main');
                  toast(message('Link settings saved'));
                },
              });
            }}
          >
            <LinkExpirationOption showField={!!link?.expires_at} />
            <LinkPasswordOption showField={!!link?.password} />
            <LinkOption>
              <Trans message="Allow download" />
              <FormSwitch name="allowDownload">
                <Trans message="Users with link can download this item" />
              </FormSwitch>
            </LinkOption>
            <LinkOption showBorder={false}>
              <Trans message="Allow import" />
              <FormSwitch name="allowEdit">
                <Trans message="Users with link can import this item into their own drive" />
              </FormSwitch>
            </LinkOption>
          </Form>
        </m.div>
      </DialogBody>
      <DialogFooter>
        <Button
          type="button"
          onClick={() => {
            setActivePanel('main');
          }}
        >
          <Trans message="Cancel" />
        </Button>
        <Button
          type="submit"
          form={formId}
          variant="flat"
          color="primary"
          disabled={updateLink.isLoading}
        >
          <Trans message="Save" />
        </Button>
      </DialogFooter>
    </Fragment>
  );
}

const minDate = now(getLocalTimeZone());

interface LinkExpirationOptionProps {
  showField: boolean;
}
function LinkExpirationOption({
  showField: showFieldDefault,
}: LinkExpirationOptionProps) {
  const {trans} = useTrans();
  const [showField, setShowField] = useState(showFieldDefault);
  return (
    <LinkOption>
      <Trans message="Link expiration" />
      <div>
        <Switch
          checked={showField}
          onChange={e => {
            setShowField(e.target.checked);
          }}
        >
          <Trans message="Link is valid until" />
        </Switch>
        {showField && (
          <FormDatePicker
            min={minDate}
            name="expiresAt"
            granularity="minute"
            className="mt-20"
            aria-label={trans({
              message: 'Link expiration date and time',
            })}
          />
        )}
      </div>
    </LinkOption>
  );
}

interface LinkPasswordOptionProps {
  showField: boolean;
}
function LinkPasswordOption({
  showField: showFieldDefault,
}: LinkPasswordOptionProps) {
  const {trans} = useTrans();
  const [showField, setShowField] = useState(showFieldDefault);
  return (
    <LinkOption>
      <Trans message="Password protect" />
      <div>
        <Switch
          checked={showField}
          onChange={e => {
            setShowField(e.target.checked);
          }}
        >
          <Trans message="Users will need to enter password in order to view this link" />
        </Switch>
        {showField && (
          <FormTextField
            type="password"
            autoFocus
            name="password"
            className="mt-20"
            aria-label={trans({message: 'Link password'})}
            description={
              <Trans message="Password will not be requested when viewing the link as file owner." />
            }
            placeholder={trans({
              message: 'Enter new password...',
            })}
          />
        )}
      </div>
    </LinkOption>
  );
}

interface LinkOptionProps {
  children: [ReactElement, ReactElement];
  showBorder?: boolean;
}
function LinkOption({children, showBorder = true}: LinkOptionProps) {
  const [title, content] = children;
  return (
    <div className={clsx(showBorder && 'border-b mb-20 pb-20')}>
      <div className="text-sm font-medium mb-8">{title}</div>
      {content}
    </div>
  );
}

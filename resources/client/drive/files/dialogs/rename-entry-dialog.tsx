import React from 'react';
import {useForm} from 'react-hook-form';
import {useRenameEntry} from '../queries/use-rename-entry';
import {FormTextField} from '@common/ui/forms/input-field/text-field/text-field';
import {Button} from '@common/ui/buttons/button';
import {Form} from '@common/ui/forms/form';
import {DialogFooter} from '@common/ui/overlays/dialog/dialog-footer';
import {useDialogContext} from '@common/ui/overlays/dialog/dialog-context';
import {Dialog} from '@common/ui/overlays/dialog/dialog';
import {DialogHeader} from '@common/ui/overlays/dialog/dialog-header';
import {DialogBody} from '@common/ui/overlays/dialog/dialog-body';
import {Trans} from '@common/i18n/trans';
import {DriveEntry} from '../drive-entry';

interface FormValue {
  name?: string;
}

interface RenameEntryDialogProps {
  entries: DriveEntry[];
}
export function RenameEntryDialog({entries}: RenameEntryDialogProps) {
  const {close, formId} = useDialogContext();
  const initialName = entries[0]?.name;
  const form = useForm({defaultValues: {name: initialName}});
  const renameEntry = useRenameEntry(form);

  const onSubmit = (e: Required<FormValue>) => {
    renameEntry.mutate(
      {
        entryId: entries[0].id,
        name: e.name,
        initialName,
      },
      {onSuccess: close}
    );
  };

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Rename" />
      </DialogHeader>
      <DialogBody>
        <Form onSubmit={onSubmit} form={form} id={formId}>
          <FormTextField
            placeholder="Enter a name..."
            aria-label="Entry name"
            autoFocus
            name="name"
            required
            minLength={3}
            maxLength={200}
          />
        </Form>
      </DialogBody>
      <DialogFooter>
        <Button variant="flat" onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
        <Button
          form={formId}
          type="submit"
          variant="flat"
          color="primary"
          disabled={renameEntry.isLoading || !form.formState.isDirty}
        >
          <Trans message="Save" />
        </Button>
      </DialogFooter>
    </Dialog>
  );
}

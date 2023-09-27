import {useForm} from 'react-hook-form';
import {Dialog} from '../../ui/overlays/dialog/dialog';
import {DialogHeader} from '../../ui/overlays/dialog/dialog-header';
import {Trans} from '../../i18n/trans';
import {DialogBody} from '../../ui/overlays/dialog/dialog-body';
import {useDialogContext} from '../../ui/overlays/dialog/dialog-context';
import {Form} from '../../ui/forms/form';
import {FormTextField} from '../../ui/forms/input-field/text-field/text-field';
import {useValueLists} from '../../http/value-lists';
import {FormSelect, Option} from '../../ui/forms/select/select';
import {DialogFooter} from '../../ui/overlays/dialog/dialog-footer';
import {Button} from '../../ui/buttons/button';
import {
  CreateLocalizationPayload,
  useCreateLocalization,
} from './create-localization';
import {message} from '@common/i18n/message';
import {useTrans} from '@common/i18n/use-trans';

export function CreateLocationDialog() {
  const {trans} = useTrans();
  const {formId, close} = useDialogContext();
  const form = useForm<CreateLocalizationPayload>({
    defaultValues: {
      language: 'en',
    },
  });

  const {data} = useValueLists(['languages']);
  const languages = data?.languages || [];

  const createLocalization = useCreateLocalization(form);

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Create localization" />
      </DialogHeader>
      <DialogBody>
        <Form
          form={form}
          id={formId}
          onSubmit={values => {
            createLocalization.mutate(values, {onSuccess: close});
          }}
        >
          <FormTextField
            autoFocus
            name="name"
            label={<Trans message="Name" />}
            className="mb-30"
            required
          />
          <FormSelect
            required
            name="language"
            label={<Trans message="Language" />}
            selectionMode="single"
            showSearchField
            searchPlaceholder={trans(message('Search languages'))}
          >
            {languages.map(language => (
              <Option value={language.code} key={language.code}>
                {language.name}
              </Option>
            ))}
          </FormSelect>
        </Form>
      </DialogBody>
      <DialogFooter>
        <Button onClick={close}>
          <Trans message="Cancel" />
        </Button>
        <Button
          variant="flat"
          color="primary"
          type="submit"
          form={formId}
          disabled={createLocalization.isLoading}
        >
          <Trans message="Save" />
        </Button>
      </DialogFooter>
    </Dialog>
  );
}

import {useForm} from 'react-hook-form';
import {useId} from 'react';
import {Form} from '@common/ui/forms/form';
import {AccountSettingsPanel} from './account-settings-panel';
import {useUpdateAccountDetails} from './basic-info-panel/update-account-details';
import {Button} from '@common/ui/buttons/button';
import {User} from '../../user';
import {useValueLists} from '@common/http/value-lists';
import {Option} from '../../../ui/forms/combobox/combobox';
import {FormSelect, OptionGroup} from '../../../ui/forms/select/select';
import {useChangeLocale} from '@common/i18n/change-locale';
import {Trans} from '@common/i18n/trans';
import {getLocalTimeZone} from '@internationalized/date';
import {AccountSettingsId} from '@common/auth/ui/account-settings/account-settings-sidenav';
import {message} from '@common/i18n/message';
import {useTrans} from '@common/i18n/use-trans';

interface Props {
  user: User;
}
export function LocalizationPanel({user}: Props) {
  const formId = useId();
  const {trans} = useTrans();
  const form = useForm<Partial<User>>({
    defaultValues: {
      language: user.language || '',
      country: user.country || '',
      timezone: user.timezone || getLocalTimeZone(),
    },
  });
  const updateDetails = useUpdateAccountDetails(form);
  const changeLocale = useChangeLocale();
  const {data} = useValueLists(['timezones', 'countries', 'localizations']);

  const countries = data?.countries || [];
  const localizations = data?.localizations || [];
  const timezones = data?.timezones || {};

  return (
    <AccountSettingsPanel
      id={AccountSettingsId.LocationAndLanguage}
      title={<Trans message="Date, time and language" />}
      actions={
        <Button
          type="submit"
          variant="flat"
          color="primary"
          form={formId}
          disabled={updateDetails.isLoading || !form.formState.isValid}
        >
          <Trans message="Save" />
        </Button>
      }
    >
      <Form
        form={form}
        onSubmit={newDetails => {
          updateDetails.mutate(newDetails);
          changeLocale.mutate({locale: newDetails.language});
        }}
        id={formId}
      >
        <FormSelect
          className="mb-24"
          selectionMode="single"
          name="language"
          label={<Trans message="Language" />}
        >
          {localizations.map(localization => (
            <Option key={localization.language} value={localization.language}>
              {localization.name}
            </Option>
          ))}
        </FormSelect>
        <FormSelect
          className="mb-24"
          selectionMode="single"
          name="country"
          label={<Trans message="Country" />}
          showSearchField
          searchPlaceholder={trans(message('Search countries'))}
        >
          {countries.map(country => (
            <Option key={country.code} value={country.code}>
              {country.name}
            </Option>
          ))}
        </FormSelect>
        <FormSelect
          selectionMode="single"
          name="timezone"
          label={<Trans message="Timezone" />}
          showSearchField
          searchPlaceholder={trans(message('Search timezones'))}
        >
          {Object.entries(timezones).map(([sectionName, sectionItems]) => (
            <OptionGroup label={sectionName} key={sectionName}>
              {sectionItems.map(timezone => (
                <Option key={timezone.value} value={timezone.value}>
                  {timezone.text}
                </Option>
              ))}
            </OptionGroup>
          ))}
        </FormSelect>
      </Form>
    </AccountSettingsPanel>
  );
}

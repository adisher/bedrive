import {SettingsPanel} from '@common/admin/settings/settings-panel';
import {FormRadioGroup} from '@common/ui/forms/radio-group/radio-group';
import {FormRadio} from '@common/ui/forms/radio-group/radio';
import {FormSwitch} from '@common/ui/forms/toggle/switch';
import {Trans} from '@common/i18n/trans';

export function DriveSettings() {
  return (
    <SettingsPanel
      title={<Trans message="Drive" />}
      description={
        <Trans message="Configure defaults for drive user dashboard." />
      }
    >
      <FormRadioGroup
        required
        className="mb-30"
        size="md"
        name="client.drive.default_view"
        orientation="vertical"
        label={<Trans message="Default view mode" />}
        description={
          <Trans message="Which view mode should user drive use by default." />
        }
      >
        <FormRadio value="list">
          <Trans message="List" />
        </FormRadio>
        <FormRadio value="grid">
          <Trans message="Grid" />
        </FormRadio>
      </FormRadioGroup>
      <FormSwitch
        className="mb-30"
        name="client.drive.send_share_notification"
        description={
          <Trans message="Send a notification to user when a file or folder is shared with them." />
        }
      >
        <Trans message="Share notifications" />
      </FormSwitch>
      <FormSwitch
        name="client.share.suggest_emails"
        description={
          <Trans message="Suggest email address of existing users when sharing a file or folder." />
        }
      >
        <Trans message="Suggest emails" />
      </FormSwitch>
    </SettingsPanel>
  );
}

import {LandingPageContent} from '@app/landing/landing-page-content';
import {useFormContext} from 'react-hook-form';
import {
  appearanceState,
  AppearanceValues,
  useAppearanceStore,
} from '@common/admin/appearance/appearance-store';
import {Fragment, ReactNode} from 'react';
import {FormTextField} from '@common/ui/forms/input-field/text-field/text-field';
import {Trans} from '@common/i18n/trans';
import {FormSlider} from '@common/ui/forms/slider/slider';
import {DialogTrigger} from '@common/ui/overlays/dialog/dialog-trigger';
import {AppearanceButton} from '@common/admin/appearance/appearance-button';
import {ColorIcon} from '@common/admin/appearance/sections/themes/color-icon';
import {ColorPickerDialog} from '@common/ui/color-picker/color-picker-dialog';
import {Link} from 'react-router-dom';
import {FormImageSelector} from '@common/ui/images/image-selector';

export function LandingPageSectionGeneral() {
  return (
    <Fragment>
      <HeaderSection />
      <div className="my-24 py-24 border-y">
        <AppearanceButton
          to="action-buttons"
          elementType={Link}
          className="mb-20"
        >
          <Trans message="Action buttons" />
        </AppearanceButton>
        <AppearanceButton to="primary-features" elementType={Link}>
          <Trans message="Primary features" />
        </AppearanceButton>
        <AppearanceButton to="secondary-features" elementType={Link}>
          <Trans message="Secondary features" />
        </AppearanceButton>
      </div>
      <FooterSection />
    </Fragment>
  );
}

function HeaderSection() {
  const defaultImage = useAppearanceStore(
    s =>
      (s.defaults?.settings.homepage.appearance as LandingPageContent)
        ?.headerImage
  );

  return (
    <>
      <FormTextField
        label={<Trans message="Header title" />}
        className="mb-20"
        name="settings.homepage.appearance.headerTitle"
        onFocus={() => {
          appearanceState().preview.setHighlight('[data-testid="headerTitle"]');
        }}
      />
      <FormTextField
        label={<Trans message="Header subtitle" />}
        className="mb-30"
        inputElementType="textarea"
        rows={4}
        name="settings.homepage.appearance.headerSubtitle"
        onFocus={() => {
          appearanceState().preview.setHighlight(
            '[data-testid="headerSubtitle"]'
          );
        }}
      />
      <FormImageSelector
        name="settings.homepage.appearance.headerImage"
        className="mb-30"
        label={<Trans message="Header image" />}
        defaultValue={defaultImage}
        diskPrefix="homepage"
      />
      <FormSlider
        name="settings.homepage.appearance.headerImageOpacity"
        label={<Trans message="Header image opacity" />}
        minValue={0}
        step={0.1}
        maxValue={1}
        formatOptions={{style: 'percent'}}
      />
      <div className="text-muted text-xs mb-20">
        <Trans message="In order for overlay colors to appear, header image opacity will need to be less then 100%" />
      </div>
      <ColorPickerTrigger
        formKey="settings.homepage.appearance.headerOverlayColor1"
        label={<Trans message="Header overlay color 1" />}
      />
      <ColorPickerTrigger
        formKey="settings.homepage.appearance.headerOverlayColor2"
        label={<Trans message="Header overlay color 2" />}
      />
    </>
  );
}

function FooterSection() {
  const defaultImage = useAppearanceStore(
    s =>
      (s.defaults?.settings.homepage.appearance as LandingPageContent)
        ?.footerImage
  );
  return (
    <Fragment>
      <FormTextField
        label={<Trans message="Footer title" />}
        className="mb-20"
        name="settings.homepage.appearance.footerTitle"
        onFocus={() => {
          appearanceState().preview.setHighlight('[data-testid="footerTitle"]');
        }}
      />
      <FormTextField
        label={<Trans message="Footer subtitle" />}
        className="mb-20"
        name="settings.homepage.appearance.footerSubtitle"
        onFocus={() => {
          appearanceState().preview.setHighlight(
            '[data-testid="footerSubtitle"]'
          );
        }}
      />
      <FormImageSelector
        name="settings.homepage.appearance.footerImage"
        className="mb-30"
        label={<Trans message="Footer background image" />}
        defaultValue={defaultImage}
        diskPrefix="homepage"
      />
    </Fragment>
  );
}

interface ColorPickerTriggerProps {
  formKey: string;
  label: ReactNode;
}
function ColorPickerTrigger({label, formKey}: ColorPickerTriggerProps) {
  const key = formKey as 'settings.homepage.appearance.headerOverlayColor1';
  const {watch, setValue} = useFormContext<AppearanceValues>();

  const formValue = watch(key);

  const setColor = (value: string | null) => {
    setValue(formKey as any, value, {
      shouldDirty: true,
    });
  };

  return (
    <DialogTrigger
      type="popover"
      onClose={value => {
        setColor(value);
      }}
    >
      <AppearanceButton
        className="capitalize"
        startIcon={
          <ColorIcon
            viewBox="0 0 48 48"
            className="icon-lg"
            style={{fill: formValue}}
          />
        }
      >
        {label}
      </AppearanceButton>
      <ColorPickerDialog
        defaultValue={formValue}
        onChange={newValue => {
          setColor(newValue);
        }}
      />
    </DialogTrigger>
  );
}

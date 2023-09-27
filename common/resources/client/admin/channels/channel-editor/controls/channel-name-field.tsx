import {FormTextField} from '@common/ui/forms/input-field/text-field/text-field';
import {Trans} from '@common/i18n/trans';
import React, {Fragment} from 'react';
import {useFormContext} from 'react-hook-form';
import {UpdateChannelPayload} from '@common/admin/channels/requests/use-update-channel';
import {SlugEditor} from '@common/ui/slug-editor';
import {useTrans} from '@common/i18n/use-trans';
import {message} from '@common/i18n/message';

export function ChannelNameField() {
  return (
    <Fragment>
      <FormTextField
        name="name"
        label={<Trans message="Name" />}
        className="mb-10"
        required
        autoFocus
      />
      <FormSlugField />
    </Fragment>
  );
}

function FormSlugField() {
  const {watch, setValue} = useFormContext<UpdateChannelPayload>();
  const value = watch('slug');
  const name = watch('name');
  const disableSlugEditing = watch('config.lockSlug');
  const restriction = watch('config.restriction');
  const restrictionId = watch('config.restrictionModelId');
  const {trans} = useTrans();
  return (
    <SlugEditor
      hideButton={disableSlugEditing}
      placeholder={name}
      suffix={
        restriction && restrictionId === 'urlParam'
          ? trans(message(':restriction_name', {values: {restriction}}))
          : undefined
      }
      className="text-sm"
      pattern="[A-Za-z0-9_-]+"
      minLength={3}
      maxLength={20}
      value={value}
      onChange={newSlug => {
        setValue('slug', newSlug);
      }}
    />
  );
}

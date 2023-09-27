import {MessageDescriptor} from '@common/i18n/message-descriptor';
import {Channel} from '@common/channels/channel';
import {ReactElement} from 'react';
import {SvgIconProps} from '@common/icons/svg-icon';

export interface ChannelContentConfig {
  models: Record<
    string,
    {
      label: MessageDescriptor;
      sortMethods: string[];
      layoutMethods: string[];
      autoUpdateMethods?: string[];
    }
  >;
  sortingMethods: Record<
    string,
    {
      label: MessageDescriptor;
      contentTypes?: Channel['config']['contentType'][];
    }
  >;
  layoutMethods: Record<
    string,
    {
      label: MessageDescriptor;
      icon?: ReactElement<SvgIconProps>;
    }
  >;
  autoUpdateMethods: Record<
    string,
    {
      label: MessageDescriptor;
      localOnly?: boolean;
    }
  >;
  userSelectableLayouts: string[];
}

import {ComponentType} from 'react';
import {SvgIconProps} from '@common/icons/svg-icon';
import {MessageDescriptor} from '@common/i18n/message-descriptor';

export interface EntryAction {
  label: MessageDescriptor;
  icon: ComponentType<SvgIconProps>;
  key: string;
  execute: () => void;
}

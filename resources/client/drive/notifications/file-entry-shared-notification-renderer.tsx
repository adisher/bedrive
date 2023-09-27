import {
  NotificationListItem,
  NotificationListItemProps,
} from '@common/notifications/notification-list';
import {FileTypeIcon} from '@common/uploads/file-type-icon/file-type-icon';

export function FileEntrySharedNotificationRenderer(
  props: NotificationListItemProps
) {
  return <NotificationListItem lineIconRenderer={IconRenderer} {...props} />;
}

interface IconRendererProps {
  icon: string;
}
function IconRenderer({icon}: IconRendererProps) {
  return <FileTypeIcon className="w-16 h-16" type={icon} />;
}

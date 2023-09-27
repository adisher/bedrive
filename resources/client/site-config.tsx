import {SiteConfigContextValue} from '@common/core/settings/site-config-context';
import {message} from '@common/i18n/message';
import filePreviewSrc from './admin/verts/file-preview.png';
import driveSrc from './admin/verts/drive.png';
import landingTopSrc from './admin/verts/landing-top.png';
import {FileEntrySharedNotificationRenderer} from './drive/notifications/file-entry-shared-notification-renderer';

const fileEntrySharedNotif = 'App\\Notifications\\FileEntrySharedNotif';

export const SiteConfig: Partial<SiteConfigContextValue> = {
  notifications: {
    renderMap: {
      [fileEntrySharedNotif]: FileEntrySharedNotificationRenderer,
    },
  },
  homepage: {
    options: [{label: message('Landing page'), value: 'landingPage'}],
  },
  auth: {
    redirectUri: '/drive',
    adminRedirectUri: '/drive',
  },
  tags: {
    types: [{name: 'label', system: true}],
  },
  admin: {
    ads: [
      {
        slot: 'ads.file-preview',
        description: message(
          'This ad will appear on shared file preview page.'
        ),
        image: filePreviewSrc,
      },
      {
        slot: 'ads.drive',
        description: message('This ad will appear on user drive page.'),
        image: driveSrc,
      },
      {
        slot: 'ads.landing-top',
        description: message(
          'This ad will appear at the top of the landing page.'
        ),
        image: landingTopSrc,
      },
    ],
  },
  demo: {
    loginPageDefaults: 'randomAccount',
  },
};

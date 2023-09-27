import './App.css';
import React from 'react';
import {BrowserRouter, Route, Routes} from 'react-router-dom';
import {createRoot} from 'react-dom/client';
import {AppearanceListener} from '@common/admin/appearance/commands/appearance-listener';
import {LandingPage} from './landing/landing-page';
import {CommonProvider} from '@common/core/common-provider';
import {ActiveWorkspaceProvider} from '@common/workspace/active-workspace-id-context';
import {GuestRoute} from '@common/auth/guards/guest-route';
import {AuthRoutes} from '@common/auth/auth-routes';
import {NotFoundPage} from '@common/ui/not-found-page/not-found-page';
import {AuthRoute} from '@common/auth/guards/auth-route';
import {FullPageLoader} from '@common/ui/progress/full-page-loader';
import {BillingRoutes} from '@common/billing/billing-routes';
import {NotificationRoutes} from '@common/notifications/notification-routes';
import {DynamicHomepage} from '@common/ui/dynamic-homepage';
import {CookieNotice} from '@common/ui/cookie-notice/cookie-notice';
import {ContactUsPage} from '@common/contact/contact-us-page';
import {CustomPageLayout} from '@common/custom-page/custom-page-layout';
import {ToastContainer} from '@common/ui/toast/toast-container';
import {LandingPageContent} from './landing/landing-page-content';
import {useAuth} from '@common/auth/use-auth';
import {EmailVerificationPage} from '@common/auth/ui/email-verification-page/email-verification-page';
import * as Sentry from '@sentry/react';
import {BrowserTracing} from '@sentry/tracing';
import {rootEl} from '@common/core/root-el';
import {useSettings} from '@common/core/settings/use-settings';
import {getBootstrapData} from '@common/core/bootstrap-data/use-backend-bootstrap-data';
import {ignoredSentryErrors} from '@common/errors/ignored-sentry-errors';
import {DialogStoreOutlet} from '@common/ui/overlays/store/dialog-store-outlet';

const AdminRoutes = React.lazy(() => import('@common/admin/admin-routes'));
const DriveRoutes = React.lazy(() => import('./drive/drive-routes'));
const SwaggerApiDocs = React.lazy(
  () => import('@common/swagger/swagger-api-docs-page')
);

declare module '@common/core/settings/settings' {
  interface Settings {
    homepage: {
      appearance: LandingPageContent;
      type: 'loginPage' | 'registerPage' | string;
      value?: any;
    };
    drive: {
      details_default_visibility: boolean;
      default_view: 'list' | 'grid';
      send_share_notification: boolean;
    };
    share: {
      suggest_emails: boolean;
    };
    ads?: {
      drive?: string;
      'file-preview'?: string;
      'landing-top'?: string;
      disable?: boolean;
    };
  }
}

const sentryDsn = getBootstrapData().settings.logging.sentry_public;
if (sentryDsn && import.meta.env.PROD) {
  Sentry.init({
    dsn: sentryDsn,
    integrations: [new BrowserTracing()],
    tracesSampleRate: 1.0,
    ignoreErrors: ignoredSentryErrors,
  });
}

const root = createRoot(rootEl);
root.render(
  <CommonProvider>
    <Router />
  </CommonProvider>
);

function Router() {
  const {
    billing,
    notifications,
    require_email_confirmation,
    api,
    html_base_uri,
  } = useSettings();
  const {user, hasPermission} = useAuth();

  if (user != null && require_email_confirmation && !user.email_verified_at) {
    return (
      <BrowserRouter>
        <ToastContainer />
        <Routes>
          <Route path="*" element={<EmailVerificationPage />} />
        </Routes>
        <DialogStoreOutlet />
      </BrowserRouter>
    );
  }

  return (
    <BrowserRouter basename={html_base_uri}>
      <AppearanceListener />
      <CookieNotice />
      <ToastContainer />
      <Routes>
        <Route
          path="/"
          element={
            <DynamicHomepage
              homepageResolver={() => (
                <GuestRoute>
                  <LandingPage />
                </GuestRoute>
              )}
            />
          }
        />
        <Route
          path="/drive/*"
          element={
            <React.Suspense fallback={<FullPageLoader />}>
              <ActiveWorkspaceProvider>
                <DriveRoutes />
              </ActiveWorkspaceProvider>
            </React.Suspense>
          }
        />
        <Route
          path="/admin/*"
          element={
            <AuthRoute permission="admin.access">
              <React.Suspense fallback={<FullPageLoader />}>
                <AdminRoutes />
              </React.Suspense>
            </AuthRoute>
          }
        />
        {AuthRoutes}
        {billing.enable && BillingRoutes}
        {notifications.integrated && NotificationRoutes}
        {api?.integrated && hasPermission('api.access') && (
          <Route
            path="api-docs"
            element={
              <React.Suspense fallback={<FullPageLoader />}>
                <SwaggerApiDocs />
              </React.Suspense>
            }
          ></Route>
        )}
        <Route path="contact" element={<ContactUsPage />}></Route>
        <Route path="pages/:pageSlug" element={<CustomPageLayout />}></Route>
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
      <DialogStoreOutlet />
    </BrowserRouter>
  );
}

import {RouteObject, useRoutes} from 'react-router-dom';
import React from 'react';
import {ShareableLinkPage} from './shareable-link/shareable-link-page/shareable-link-page';
import {DriveLayout} from './layout/drive-layout';
import {AuthRoute} from '@common/auth/guards/auth-route';
import {NotFoundPage} from '@common/ui/not-found-page/not-found-page';

const DriveRouteConfig: RouteObject[] = [
  {
    path: '/',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {
    path: '/folders/:hash',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {
    path: '/shares',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {
    path: '/recent',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {
    path: '/starred',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {
    path: '/trash',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {
    path: '/search',
    element: (
      <AuthRoute>
        <DriveLayout />
      </AuthRoute>
    ),
  },
  {path: 's/:hash', element: <ShareableLinkPage />},
  {path: '*', element: <NotFoundPage />},
];

export default function DriveRoutes() {
  return useRoutes(DriveRouteConfig);
}

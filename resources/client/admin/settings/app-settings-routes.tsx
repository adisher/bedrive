import {RouteObject} from 'react-router-dom';
import {DriveSettings} from './drive-settings';

export const AppSettingsRoutes: RouteObject[] = [
  {path: 'drive', element: <DriveSettings />},
];

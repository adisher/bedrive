import './../resources/client/App.css';
import './light-theme.css';
import {BrowserRouter} from 'react-router-dom';
import {CommonProvider} from '../resources/client/common/core/common-provider';

export const parameters = {
  actions: {argTypesRegex: '^on[A-Z].*'},
  controls: {},
};

// const DefaultLocale = {
//   id: 0,
//   name: 'lt',
//   language: 'lt',
// };
//
// mergeBootstrapData({
//   settings: {},
//   i18n: {
//     model: DefaultLocale,
//     lines: {},
//   },
// });

export const decorators = [
  Story => (
    <CommonProvider>
      <BrowserRouter>
        <Story />
      </BrowserRouter>
    </CommonProvider>
  ),
];

const {mergeConfig} = require('vite');
const react = require('@vitejs/plugin-react');

module.exports = {
  async viteFinal(config, {configType}) {
    config.plugins = config.plugins.filter(
      plugin =>
        !(Array.isArray(plugin) && plugin[0]?.name.includes('vite:react'))
    );

    return mergeConfig(config, {
      resolve: {
        preserveSymlinks: true,
      },
      plugins: [react()],
    });
  },
  followSymlinks: true,
  stories: ['./../resources/client/common/**/*.stories.@(js|jsx|ts|tsx)'],
  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@storybook/addon-interactions',
  ],
  framework: '@storybook/react',
  core: {
    builder: '@storybook/builder-vite',
  },
};

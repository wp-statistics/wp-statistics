import { addons } from 'storybook/manager-api'
import { create } from 'storybook/theming/create'

const wpStatisticsTheme = create({
  base: 'dark',

  // Branding
  brandTitle: 'WP Statistics',
  brandUrl: 'https://ui.wp-statistics.com/',
  brandImage: './logo.svg',
  brandTarget: '_blank',

  // Color palette
  colorPrimary: '#663399',
  colorSecondary: '#663399',

  // UI colors
  appBg: '#1a1a2e',
  appContentBg: '#16213e',
  appBorderColor: '#ffffff1a',
  appBorderRadius: 10,

  // Typography
  fontBase: 'Roboto, sans-serif',
  fontCode: 'Roboto Mono, monospace',

  // Text colors
  textColor: '#f8f9fa',
  textInverseColor: '#1a1a2e',

  // Toolbar colors
  barTextColor: '#f8f9fa',
  barSelectedColor: '#663399',
  barBg: '#0f3460',

  // Form colors
  inputBg: '#16213e',
  inputBorder: '#ffffff1a',
  inputTextColor: '#f8f9fa',
  inputBorderRadius: 8,
})

addons.setConfig({
  theme: wpStatisticsTheme,
  showToolbar: true,
  enableShortcuts: true,
  sidebar: {
    showRoots: true,
    collapsedRoots: ['other'],
  },
})

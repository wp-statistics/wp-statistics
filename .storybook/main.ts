import type { StorybookConfig } from '@storybook/react-vite'
import { resolve } from 'path'
import { fileURLToPath } from 'url'
import tailwindcss from '@tailwindcss/vite'

const __filename = fileURLToPath(import.meta.url)
const __dirname = resolve(__filename, '..')

const config: StorybookConfig = {
  stories: ['../resources/react/src/**/*.mdx', '../resources/react/src/**/*.stories.@(js|jsx|mjs|ts|tsx)'],
  addons: [
    '@storybook/addon-a11y',
    '@storybook/addon-interactions',
    'storybook-addon-rtl',
  ],
  framework: {
    name: '@storybook/react-vite',
    options: {},
  },
  staticDirs: [
    './public',
    { from: '../resources/images', to: '/public/images' },
  ],
  viteFinal: async (config) => {
    // Merge custom Vite configuration
    return {
      ...config,
      plugins: [...(config.plugins || []), tailwindcss()],
      resolve: {
        ...config.resolve,
        alias: {
          ...config.resolve?.alias,
          '@': resolve(__dirname, '../resources/react/src'),
          '@components': resolve(__dirname, '../resources/react/src/components'),
          '@hooks': resolve(__dirname, '../resources/react/src/hooks'),
          '@lib': resolve(__dirname, '../resources/react/src/lib'),
          '@services': resolve(__dirname, '../resources/react/src/services'),
          '@types': resolve(__dirname, '../resources/react/src/types'),
          '@stores': resolve(__dirname, '../resources/react/src/stores'),
          '@pages': resolve(__dirname, '../resources/react/src/pages'),
          '@routes': resolve(__dirname, '../resources/react/src/routes'),
        },
      },
    }
  },
}

export default config

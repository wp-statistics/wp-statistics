import type { Preview } from '@storybook/react'
import { withRTLSupport } from './decorators/with-rtl-support'
import { withQueryClient } from './decorators/with-query-client'
import '../resources/react/src/globals.css'

const preview: Preview = {
  decorators: [withQueryClient, withRTLSupport],
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
    a11y: {
      config: {
        rules: [
          { id: 'color-contrast', enabled: true },
        ],
      },
      manual: false,
      context: '#storybook-root',
    },
  },
  globalTypes: {
    direction: {
      name: 'Direction',
      description: 'Text direction for internationalization',
      defaultValue: 'ltr',
      toolbar: {
        icon: 'globe',
        items: [
          { value: 'ltr', title: 'LTR (Left to Right)' },
          { value: 'rtl', title: 'RTL (Right to Left)' },
        ],
        showName: true,
      },
    },
  },
}

export default preview

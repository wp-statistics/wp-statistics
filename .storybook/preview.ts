import type { Preview } from '@storybook/react'
import { withRTLSupport } from './decorators/with-rtl-support'
import { withQueryClient } from './decorators/with-query-client'
import '../resources/react/src/globals.css'

// Initialize MSW
const initMSW = async () => {
  if (typeof window !== 'undefined') {
    const { worker } = await import('./mocks/browser')
    await worker.start({
      onUnhandledRequest: 'bypass',
      serviceWorker: {
        url: './mockServiceWorker.js',
      },
    })
  }
}

// Start MSW before rendering stories
initMSW()

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
          // Color and contrast
          { id: 'color-contrast', enabled: true },

          // Keyboard navigation
          { id: 'focus-order-semantics', enabled: true },
          { id: 'focusable-content', enabled: true },

          // Form and interactive elements
          { id: 'label', enabled: true },
          { id: 'select-name', enabled: true },
          { id: 'autocomplete-valid', enabled: true },

          // ARIA rules
          { id: 'aria-allowed-attr', enabled: true },
          { id: 'aria-hidden-focus', enabled: true },
          { id: 'aria-required-children', enabled: true },
          { id: 'aria-required-parent', enabled: true },
          { id: 'aria-valid-attr-value', enabled: true },
          { id: 'aria-valid-attr', enabled: true },
          { id: 'role-img-alt', enabled: true },

          // Structure and semantics
          { id: 'button-name', enabled: true },
          { id: 'link-name', enabled: true },
          { id: 'image-alt', enabled: true },
          { id: 'input-button-name', enabled: true },
          { id: 'empty-heading', enabled: true },
          { id: 'heading-order', enabled: true },

          // Disabled for component isolation (not applicable to isolated components)
          { id: 'landmark-one-main', enabled: false },
          { id: 'region', enabled: false },

          // Table accessibility
          { id: 'table-fake-caption', enabled: true },
          { id: 'td-has-header', enabled: true },
          { id: 'th-has-data-cells', enabled: true },

          // Timing and motion
          { id: 'meta-refresh', enabled: true },
          { id: 'no-autoplay-audio', enabled: true },
        ],
      },
      manual: false,
      context: '#storybook-root',
      options: {
        restoreScroll: true,
      },
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

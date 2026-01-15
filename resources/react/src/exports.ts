/**
 * WP Statistics - Exports Bridge
 *
 * Exposes React hooks and components on window for premium plugin access.
 * The premium plugin's components are rendered within the free plugin's
 * React tree via PremiumSlot, so they have access to contexts.
 *
 * @package WP_Statistics
 */

import type { ComponentType, ReactNode } from 'react'
import React from 'react'
import * as ReactDOM from 'react-dom'

// Options Drawer components
import {
  OptionsDetailView,
  OptionsMenuItem,
  OptionsToggleItem,
  type OptionsView,
  useOptionsDrawer,
} from '@/components/custom/options-drawer/options-drawer'
import { useGlobalFilters } from '@/hooks/use-global-filters'
// Hooks
import { usePageOptions } from '@/hooks/use-page-options'

// Expose React globally for premium plugin to use as external
// @ts-expect-error - Setting global React
window.React = React
// @ts-expect-error - Setting global ReactDOM
window.ReactDOM = ReactDOM

// Type definition for exports
interface WpsExports {
  hooks: {
    usePageOptions: typeof usePageOptions
    useGlobalFilters: typeof useGlobalFilters
    useOptionsDrawer: typeof useOptionsDrawer
  }
  components: {
    OptionsMenuItem: typeof OptionsMenuItem
    OptionsDetailView: typeof OptionsDetailView
    OptionsToggleItem: typeof OptionsToggleItem
  }
}

// Expose on window
declare global {
  interface Window {
    wps_exports?: WpsExports
  }
}

// Initialize exports
window.wps_exports = {
  hooks: {
    usePageOptions,
    useGlobalFilters,
    useOptionsDrawer,
  },
  components: {
    OptionsMenuItem,
    OptionsDetailView,
    OptionsToggleItem,
  },
}

// Dispatch event to notify premium plugin that exports are ready
window.dispatchEvent(new CustomEvent('wps:exports-ready', {
  detail: { version: '1.0.0' }
}))

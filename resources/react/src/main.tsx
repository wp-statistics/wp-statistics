import './globals.css'

import React from 'react'
import { createRoot } from 'react-dom/client'

import { App } from './app'

// Set CSS variable for WordPress admin menu height to ensure React app fills properly
const setAdminMenuHeight = () => {
  const adminBar = document.getElementById('wpadminbar')
  const adminBarHeight = adminBar ? adminBar.offsetHeight : 32 // Default 32px
  const availableHeight = window.innerHeight - adminBarHeight
  document.documentElement.style.setProperty('--wp-admin-menu-height', `${availableHeight}px`)
}

// Set initial height and observe changes
setAdminMenuHeight()
window.addEventListener('resize', setAdminMenuHeight)

/**
 * Sync WordPress admin menu with React Router hash
 *
 * Updates the 'current' class on submenu items based on hash route
 * and intercepts submenu link clicks for SPA navigation.
 */
const syncAdminMenu = () => {
  const hash = window.location.hash
  const isSettingsRoute = hash.startsWith('#/settings')

  // Find WP Statistics submenu
  const wpStatsMenu = document.querySelector('#toplevel_page_wp-statistics')
  if (!wpStatsMenu) return

  const submenuItems = wpStatsMenu.querySelectorAll('.wp-submenu li')

  submenuItems.forEach((item) => {
    const link = item.querySelector('a')
    if (!link) return

    const href = link.getAttribute('href') || ''
    const isSettingsLink = href.includes('#/settings')
    const isDashboardLink = href.includes('page=wp-statistics') && !isSettingsLink

    // Update current class
    if (isSettingsRoute && isSettingsLink) {
      item.classList.add('current')
      link.classList.add('current')
      link.setAttribute('aria-current', 'page')
    } else if (!isSettingsRoute && isDashboardLink) {
      item.classList.add('current')
      link.classList.add('current')
      link.setAttribute('aria-current', 'page')
    } else {
      item.classList.remove('current')
      link.classList.remove('current')
      link.removeAttribute('aria-current')
    }
  })
}

/**
 * Intercept clicks on WP Statistics submenu for SPA navigation
 * Prevents full page reload when navigating between Dashboard and Settings
 */
const setupSubmenuNavigation = () => {
  const wpStatsMenu = document.querySelector('#toplevel_page_wp-statistics')
  if (!wpStatsMenu) return

  const submenuLinks = wpStatsMenu.querySelectorAll('.wp-submenu a')

  submenuLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href') || ''

      // Only intercept if we're already on wp-statistics page
      if (window.location.search.includes('page=wp-statistics')) {
        // Extract the hash from the href
        const hashIndex = href.indexOf('#')
        if (hashIndex !== -1) {
          e.preventDefault()
          const newHash = href.substring(hashIndex)
          window.location.hash = newHash
        } else if (href.includes('page=wp-statistics') && !href.includes('#')) {
          // Dashboard link without hash - navigate to overview
          e.preventDefault()
          window.location.hash = '#/overview'
        }
      }
    })
  })
}

// Initialize menu sync after DOM is ready and React loads
// Use setTimeout to ensure WordPress menu DOM is fully rendered
const initMenuSync = () => {
  syncAdminMenu()
  setupSubmenuNavigation()
  window.addEventListener('hashchange', syncAdminMenu)
}

// Run sync after a short delay to ensure menu DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => setTimeout(initMenuSync, 0))
} else {
  setTimeout(initMenuSync, 0)
}

// Render the app
const rootElement = document.getElementById('wp-statistics-app')!

if (rootElement) {
  const root = createRoot(rootElement)
  root.render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  )
}

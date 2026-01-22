import './globals.css'

import React from 'react'
import { createRoot } from 'react-dom/client'

import { App } from './app'

// Set CSS variables for WordPress admin layout to ensure React app integrates properly
const setAdminLayoutVars = () => {
  const adminBar = document.getElementById('wpadminbar')
  const adminBarHeight = adminBar ? adminBar.offsetHeight : 32 // Default 32px
  const viewportAvailableHeight = window.innerHeight - adminBarHeight

  // Get the WordPress admin menu height and width
  const adminMenuWrap = document.getElementById('adminmenuwrap')
  const adminMenuHeight = adminMenuWrap ? adminMenuWrap.scrollHeight : 0
  const adminMenuWidth = document.getElementById('adminmenu')?.offsetWidth ?? 160 // Default 160px

  // Use the maximum of viewport height and admin menu height to prevent gaps
  const appHeight = Math.max(viewportAvailableHeight, adminMenuHeight)
  document.documentElement.style.setProperty('--wp-admin-menu-height', `${appHeight}px`)
  document.documentElement.style.setProperty('--wp-admin-sidebar-width', `${adminMenuWidth}px`)
}

// Set initial layout vars and observe changes
setAdminLayoutVars()
window.addEventListener('resize', setAdminLayoutVars)

/**
 * Sync WordPress admin menu with React Router hash
 *
 * Updates the 'current' class on submenu items based on hash route
 * and intercepts submenu link clicks for SPA navigation.
 */
const syncAdminMenu = () => {
  const hash = window.location.hash
  const isSettingsRoute = hash.startsWith('#/settings')
  const isToolsRoute = hash.startsWith('#/tools')

  // Find WP Statistics submenu (works for both single site and network admin)
  const wpStatsMenu =
    document.querySelector('#toplevel_page_wp-statistics-network') ||
    document.querySelector('#toplevel_page_wp-statistics')
  if (!wpStatsMenu) return

  const submenuItems = wpStatsMenu.querySelectorAll('.wp-submenu li')

  submenuItems.forEach((item) => {
    const link = item.querySelector('a')
    if (!link) return

    const href = link.getAttribute('href') || ''
    const isSettingsLink = href.includes('#/settings')
    const isToolsLink = href.includes('#/tools')
    const isDashboardLink = href.includes('page=wp-statistics') && !isSettingsLink && !isToolsLink

    // Update current class based on current route
    let shouldBeActive = false
    if (isSettingsRoute && isSettingsLink) {
      shouldBeActive = true
    } else if (isToolsRoute && isToolsLink) {
      shouldBeActive = true
    } else if (!isSettingsRoute && !isToolsRoute && isDashboardLink) {
      shouldBeActive = true
    }

    if (shouldBeActive) {
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
  // Find WP Statistics submenu (works for both single site and network admin)
  const wpStatsMenu =
    document.querySelector('#toplevel_page_wp-statistics-network') ||
    document.querySelector('#toplevel_page_wp-statistics')
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

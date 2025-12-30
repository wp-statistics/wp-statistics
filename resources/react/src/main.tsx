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

// Render the app
const rootElement = document.getElementById('wp-statistics-root')!

if (rootElement) {
  const root = createRoot(rootElement)
  root.render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  )
}

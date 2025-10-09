import './globals.css'

import React from 'react'
import { createRoot } from 'react-dom/client'

import { App } from './app'

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

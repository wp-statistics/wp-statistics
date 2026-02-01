/**
 * Shared types for data table column cells
 */

// Visitor Information
export interface VisitorInfoData {
  country: {
    code: string
    name: string
    region?: string
    city?: string
  }
  os: {
    icon: string
    name: string
  }
  browser: {
    icon: string
    name: string
    version?: string
  }
  user?: {
    id: number
    username: string
    email?: string
    role?: string
  }
  identifier?: string // IP or hash
}

export interface VisitorInfoConfig {
  pluginUrl: string
  trackLoggedInEnabled: boolean
  hashEnabled: boolean
}

// Page data (for page, entry page, exit page columns)
export interface PageData {
  title: string
  url: string
  hasQueryString?: boolean
  queryString?: string
  utmCampaign?: string
  // Optional routing fields — when present, cells auto-resolve internal links
  pageType?: string
  pageWpId?: number | string | null
  resourceId?: number | string | null
}

// Referrer data
export interface ReferrerData {
  domain?: string
  category: string
}

// Common cell props
export interface CellConfig {
  truncateLength?: number
  className?: string
}

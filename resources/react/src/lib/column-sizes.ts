/**
 * Centralized column size constants for data tables.
 * All sizes are in pixels.
 *
 * Using consistent sizes across tables ensures:
 * 1. Visual consistency across the application
 * 2. Easy maintenance - change once, applies everywhere
 * 3. Prevents the TanStack Table default of 150px
 */

export const COLUMN_SIZES = {
  // Date/Time columns
  lastVisit: 80,
  onlineFor: 75,

  // Visitor info (icons + identifier)
  visitorInfo: 100,

  // Page/URL columns
  page: 140,
  entryPage: 140,
  exitPage: 140,
  referrer: 120,
  journey: 160, // Combined entry → exit flow
  location: 140, // Flag + "City, Country" format

  // Numeric columns (right-aligned)
  views: 70,
  totalViews: 80, // Slightly wider for "Total Views" header
  sessions: 80,
  duration: 85,
  viewsPerSession: 90,
  bounceRate: 70,

  // Status/Badge columns
  status: 80,
} as const

export type ColumnSizeKey = keyof typeof COLUMN_SIZES

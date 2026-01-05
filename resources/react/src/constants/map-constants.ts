/**
 * Map-related constants for GlobalMap component
 * Centralizes magic numbers and configuration values
 */

// Animation settings
export const MAP_ANIMATION = {
  /** Duration for zoom/pan animations in milliseconds */
  DURATION_MS: 400,
  /** Duration for scroll hint display in milliseconds */
  SCROLL_HINT_TIMEOUT_MS: 2000,
  /** Delay before hiding provinces loading state */
  PROVINCES_LOADING_DELAY_MS: 100,
} as const

// Zoom configuration
export const MAP_ZOOM = {
  /** Minimum zoom level (world view) */
  MIN: 1,
  /** Maximum zoom level */
  MAX: 50,
  /** Zoom multiplier for in/out buttons */
  STEP_MULTIPLIER: 1.5,
  /** Default zoom when resetting to world view */
  DEFAULT: 1,
  /** Default fallback zoom for countries */
  FALLBACK: 6,
} as const

/**
 * Country size thresholds for dynamic zoom calculation
 * Based on bounding box maximum dimension (degrees)
 */
export const COUNTRY_SIZE_ZOOM_MAP = [
  { maxDimension: 60, zoom: 3.5 },   // Very large (Russia, Canada, USA, China)
  { maxDimension: 40, zoom: 4.5 },   // Large (Brazil, Australia)
  { maxDimension: 25, zoom: 6 },     // Medium-large (Iran, Algeria, Saudi Arabia)
  { maxDimension: 15, zoom: 7.5 },   // Medium (France, Spain, Turkey)
  { maxDimension: 8, zoom: 9 },      // Small (UK, Germany, Japan)
  { maxDimension: 4, zoom: 11 },     // Very small (Netherlands, Belgium)
  { maxDimension: 0, zoom: 14 },     // Tiny (Singapore, Luxembourg)
] as const

// Color scale configuration
export const COLOR_SCALE_THRESHOLDS = {
  /** Normalized value thresholds for color scale */
  THRESHOLDS: [0.2, 0.4, 0.6, 0.8, 0.9] as const,
  /** Color for countries/regions with no data */
  NO_DATA_COLOR: '#e5e7eb',
  /** Hover color for no-data countries */
  NO_DATA_HOVER_COLOR: '#d1d5db',
  /** Highlight color on hover */
  HIGHLIGHT_COLOR: '#4338ca',
} as const

// Region view colors
export const REGION_COLORS = {
  /** Fill color for regions with data */
  HAS_DATA: '#c7d2fe',
  /** Fill color for regions without data */
  NO_DATA: '#f3f4f6',
  /** Hover color for regions with data */
  HAS_DATA_HOVER: '#a5b4fc',
  /** Hover color for regions without data */
  NO_DATA_HOVER: '#e5e7eb',
  /** Border color */
  STROKE: '#d1d5db',
  /** Border color on hover */
  STROKE_HOVER: '#9ca3af',
} as const

// Stroke widths
export const MAP_STROKES = {
  /** Country border width in world view */
  COUNTRY_DEFAULT: 0.5,
  /** Country border width in region view */
  COUNTRY_REGION_VIEW: 0.3,
  /** Country border width on hover */
  COUNTRY_HOVER: 1,
  /** Province border width */
  PROVINCE_DEFAULT: 0.15,
  /** Province border width on hover */
  PROVINCE_HOVER: 0.25,
} as const

// Cache times for queries
export const QUERY_CACHE = {
  /** Stale time for region queries (5 minutes) */
  REGIONS_STALE_TIME_MS: 5 * 60 * 1000,
  /** Stale time for cities queries (5 minutes) */
  CITIES_STALE_TIME_MS: 5 * 60 * 1000,
} as const

// Map projection configuration
export const MAP_PROJECTION = {
  /** Map projection type */
  TYPE: 'geoEqualEarth',
  /** Rotation for projection */
  ROTATE: [0, 0, 0] as [number, number, number],
  /** Center point for projection */
  CENTER: [15, 15] as [number, number],
  /** Scale for projection */
  SCALE: 160,
  /** Map width */
  WIDTH: 800,
  /** Map height */
  HEIGHT: 400,
} as const

// Default map center (world view)
export const WORLD_CENTER: [number, number] = [0, 0]

/**
 * Calculate zoom level based on country's bounding box dimension
 */
export function calculateZoomFromDimension(maxDimension: number): number {
  for (const { maxDimension: threshold, zoom } of COUNTRY_SIZE_ZOOM_MAP) {
    if (maxDimension > threshold) {
      return zoom
    }
  }
  return MAP_ZOOM.FALLBACK
}

/**
 * Get color for a normalized value (0-1) using the color scale
 */
export function getColorFromScale(normalizedValue: number, colorScale: readonly string[]): string {
  if (normalizedValue <= 0) return COLOR_SCALE_THRESHOLDS.NO_DATA_COLOR

  const thresholds = COLOR_SCALE_THRESHOLDS.THRESHOLDS
  for (let i = 0; i < thresholds.length; i++) {
    if (normalizedValue < thresholds[i]) {
      return colorScale[i]
    }
  }
  return colorScale[colorScale.length - 1]
}

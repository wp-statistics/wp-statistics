/**
 * GlobalMap Component Data Constants
 *
 * This file contains configuration used by the GlobalMap component.
 *
 * DATA SOURCE OVERVIEW:
 * ====================
 *
 * ✅ FROM ANALYTICS API (Dynamic, Real Data):
 *    - Country names, codes, visitor counts, view counts (from batch request)
 *    - Region names, visitor counts, view counts (fetched on country click)
 *    - All metrics are fetched from wp_statistics_analytics endpoint
 *
 * ✅ HARDCODED (Permanent - OK to keep):
 *    - COLOR_SCALE: UI configuration, doesn't change
 *    - MAP_PATHS: Local GeoJSON paths (built from resources/geojson during Vite build)
 *    - These are frontend-only configuration, not data
 *
 * MAP IMPLEMENTATION:
 * ===================
 * - World view: Shows countries colored by visitor/view count
 * - Country drilldown: Shows province/region boundaries from GeoJSON
 * - Region data is fetched from API and matched to province names in GeoJSON
 *
 * GeoJSON SOURCE:
 * ===============
 * - Original data from Natural Earth Vector (https://github.com/nvkelso/natural-earth-vector)
 * - Files stored in resources/geojson/ and minified to public/geojson/ during build
 * - Served locally for faster load times (vs GitHub raw CDN)
 */

/**
 * Color scale for map visualization (Tailwind CSS Indigo scale)
 * Used to color countries/regions based on visitor count
 *
 * ✅ OK to keep hardcoded - This is UI configuration, not data
 */
export const COLOR_SCALE = [
  '#e0e7ff', // indigo-100
  '#c7d2fe', // indigo-200
  '#a5b4fc', // indigo-300
  '#818cf8', // indigo-400
  '#6366f1', // indigo-500
  '#4f46e5', // indigo-600
]

/**
 * Local GeoJSON paths (relative to plugin URL)
 * Files are minified during Vite build from resources/geojson to public/geojson
 *
 * ✅ OK to keep hardcoded - Static asset paths, built from source
 */
export const MAP_PATHS = {
  /** World country boundaries (110m resolution - low detail for performance) */
  countries: 'public/geojson/countries.min.geojson',

  /** Province/state boundaries (50m resolution - medium detail for country zoom) */
  provinces: 'public/geojson/provinces.min.geojson',
}

/**
 * Helper function to get full URL for a map GeoJSON file
 * Combines the WordPress plugin URL with the relative path
 *
 * @param pluginUrl - The base URL of the WP Statistics plugin (from wpStatistics.pluginUrl)
 * @param mapType - The type of map to load ('countries' or 'provinces')
 * @returns Full URL to the GeoJSON file
 */
export const getMapUrl = (
  pluginUrl: string,
  mapType: keyof typeof MAP_PATHS
): string => {
  // Ensure pluginUrl ends with a slash
  const baseUrl = pluginUrl.endsWith('/') ? pluginUrl : `${pluginUrl}/`
  return `${baseUrl}${MAP_PATHS[mapType]}`
}

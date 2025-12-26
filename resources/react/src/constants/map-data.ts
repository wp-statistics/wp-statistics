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
 *    - MAP_URLS: External GeoJSON/TopoJSON sources (Natural Earth Vector)
 *    - These are frontend-only configuration, not data
 *
 * MAP IMPLEMENTATION:
 * ===================
 * - World view: Shows countries colored by visitor/view count
 * - Country drilldown: Shows province/region boundaries from TopoJSON
 * - Region data is fetched from API and matched to province names in TopoJSON
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
 * GeoJSON URLs for map rendering (Natural Earth Vector data)
 *
 * ✅ OK to keep hardcoded - External data sources don't change often
 */
export const MAP_URLS = {
  /** World country boundaries (low resolution for performance) */
  countries:
    'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_110m_admin_0_countries.geojson',

  /** Province/state boundaries (high resolution for country zoom) */
  provinces:
    'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_10m_admin_1_states_provinces.geojson',
}

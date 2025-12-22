/**
 * GlobalMap Component Data Constants
 *
 * This file contains hardcoded data used by the GlobalMap component.
 *
 * DATA SOURCE OVERVIEW:
 * ====================
 *
 * ✅ FROM ANALYTICS API (Dynamic, Real Data):
 *    - Country names, codes, visitor counts, view counts
 *    - City names, region names, visitor counts, view counts
 *    - All metrics are fetched from wp_statistics_analytics endpoint
 *
 * ⚠️  HARDCODED (Temporary - Will be removed):
 *    - FAKE_CITY_DATA: Demo city data for fallback/Storybook
 *    - Reason: Safety net when API fails or for development
 *    - Remove when: API is stable and well-tested
 *
 * ✅ HARDCODED (Permanent - OK to keep):
 *    - COLOR_SCALE: UI configuration, doesn't change
 *    - MAP_URLS: External GeoJSON sources (Natural Earth Vector)
 *    - These are frontend-only configuration, not data
 *
 * DATABASE SCHEMA (Current):
 * ==========================
 * wp_statistics_countries: ID, code, name, continent_code, continent
 * wp_statistics_cities: ID, city_name, region_code, region_name, country_id
 * ❌ NO latitude/longitude columns exist yet
 *
 * FUTURE MIGRATION PATH:
 * ======================
 * When backend adds latitude/longitude to wp_statistics_cities:
 * 1. API will return coordinates in city data response
 * 2. Remove city-coordinates.ts lookup file
 * 3. Remove FAKE_CITY_DATA constant
 * 4. Component will use API coordinates directly
 */

// ========================================================================
// TEMPORARY: Fake City Data (Fallback for API failures & Storybook)
// ========================================================================

interface FakeCityDataItem {
  city_id: number
  city_name: string
  city_region_name: string
  country_code: string
  country_name: string
  visitors: number
  views: number
}

/**
 * TEMPORARY: Fake city data used as fallback when Analytics API fails
 * or for Storybook stories when real API is unavailable.
 *
 * Contains 51 cities across 16 countries with made-up visitor/view counts.
 *
 * ⚠️  This should be REMOVED when:
 * - Analytics API is stable and well-tested
 * - Backend adds latitude/longitude to database
 * - All real data comes from API
 */
export const FAKE_CITY_DATA: Record<string, FakeCityDataItem[]> = {
  US: [
    {
      city_id: 1,
      city_name: 'New York',
      city_region_name: 'New York',
      country_code: 'US',
      country_name: 'United States',
      visitors: 5000,
      views: 12000,
    },
    {
      city_id: 2,
      city_name: 'Los Angeles',
      city_region_name: 'California',
      country_code: 'US',
      country_name: 'United States',
      visitors: 4500,
      views: 11000,
    },
    {
      city_id: 3,
      city_name: 'Chicago',
      city_region_name: 'Illinois',
      country_code: 'US',
      country_name: 'United States',
      visitors: 3500,
      views: 9000,
    },
    {
      city_id: 4,
      city_name: 'Houston',
      city_region_name: 'Texas',
      country_code: 'US',
      country_name: 'United States',
      visitors: 3000,
      views: 7500,
    },
    {
      city_id: 5,
      city_name: 'San Francisco',
      city_region_name: 'California',
      country_code: 'US',
      country_name: 'United States',
      visitors: 2800,
      views: 7000,
    },
  ],
  GB: [
    {
      city_id: 6,
      city_name: 'London',
      city_region_name: 'England',
      country_code: 'GB',
      country_name: 'United Kingdom',
      visitors: 8000,
      views: 18000,
    },
    {
      city_id: 7,
      city_name: 'Manchester',
      city_region_name: 'England',
      country_code: 'GB',
      country_name: 'United Kingdom',
      visitors: 2000,
      views: 5000,
    },
    {
      city_id: 8,
      city_name: 'Birmingham',
      city_region_name: 'England',
      country_code: 'GB',
      country_name: 'United Kingdom',
      visitors: 1500,
      views: 3500,
    },
  ],
  DE: [
    {
      city_id: 9,
      city_name: 'Berlin',
      city_region_name: 'Berlin',
      country_code: 'DE',
      country_name: 'Germany',
      visitors: 4000,
      views: 9000,
    },
    {
      city_id: 10,
      city_name: 'Munich',
      city_region_name: 'Bavaria',
      country_code: 'DE',
      country_name: 'Germany',
      visitors: 3500,
      views: 8000,
    },
    {
      city_id: 11,
      city_name: 'Hamburg',
      city_region_name: 'Hamburg',
      country_code: 'DE',
      country_name: 'Germany',
      visitors: 2500,
      views: 6000,
    },
  ],
  FR: [
    {
      city_id: 12,
      city_name: 'Paris',
      city_region_name: 'Île-de-France',
      country_code: 'FR',
      country_name: 'France',
      visitors: 7000,
      views: 15000,
    },
    {
      city_id: 13,
      city_name: 'Lyon',
      city_region_name: 'Auvergne-Rhône-Alpes',
      country_code: 'FR',
      country_name: 'France',
      visitors: 2000,
      views: 4500,
    },
    {
      city_id: 14,
      city_name: 'Marseille',
      city_region_name: "Provence-Alpes-Côte d'Azur",
      country_code: 'FR',
      country_name: 'France',
      visitors: 1800,
      views: 4000,
    },
  ],
  CA: [
    {
      city_id: 15,
      city_name: 'Toronto',
      city_region_name: 'Ontario',
      country_code: 'CA',
      country_name: 'Canada',
      visitors: 4500,
      views: 10000,
    },
    {
      city_id: 16,
      city_name: 'Vancouver',
      city_region_name: 'British Columbia',
      country_code: 'CA',
      country_name: 'Canada',
      visitors: 3000,
      views: 7000,
    },
    {
      city_id: 17,
      city_name: 'Montreal',
      city_region_name: 'Quebec',
      country_code: 'CA',
      country_name: 'Canada',
      visitors: 2500,
      views: 6000,
    },
  ],
  AU: [
    {
      city_id: 18,
      city_name: 'Sydney',
      city_region_name: 'New South Wales',
      country_code: 'AU',
      country_name: 'Australia',
      visitors: 5000,
      views: 11000,
    },
    {
      city_id: 19,
      city_name: 'Melbourne',
      city_region_name: 'Victoria',
      country_code: 'AU',
      country_name: 'Australia',
      visitors: 3500,
      views: 8000,
    },
    {
      city_id: 20,
      city_name: 'Brisbane',
      city_region_name: 'Queensland',
      country_code: 'AU',
      country_name: 'Australia',
      visitors: 1500,
      views: 3500,
    },
  ],
  JP: [
    {
      city_id: 21,
      city_name: 'Tokyo',
      city_region_name: 'Tokyo',
      country_code: 'JP',
      country_name: 'Japan',
      visitors: 4000,
      views: 9500,
    },
    {
      city_id: 22,
      city_name: 'Osaka',
      city_region_name: 'Osaka',
      country_code: 'JP',
      country_name: 'Japan',
      visitors: 2500,
      views: 6000,
    },
    {
      city_id: 23,
      city_name: 'Kyoto',
      city_region_name: 'Kyoto',
      country_code: 'JP',
      country_name: 'Japan',
      visitors: 1500,
      views: 3500,
    },
  ],
  IN: [
    {
      city_id: 24,
      city_name: 'Mumbai',
      city_region_name: 'Maharashtra',
      country_code: 'IN',
      country_name: 'India',
      visitors: 3000,
      views: 7000,
    },
    {
      city_id: 25,
      city_name: 'Delhi',
      city_region_name: 'Delhi',
      country_code: 'IN',
      country_name: 'India',
      visitors: 2800,
      views: 6500,
    },
    {
      city_id: 26,
      city_name: 'Bangalore',
      city_region_name: 'Karnataka',
      country_code: 'IN',
      country_name: 'India',
      visitors: 2000,
      views: 4500,
    },
  ],
  BR: [
    {
      city_id: 27,
      city_name: 'São Paulo',
      city_region_name: 'São Paulo',
      country_code: 'BR',
      country_name: 'Brazil',
      visitors: 3500,
      views: 8000,
    },
    {
      city_id: 28,
      city_name: 'Rio de Janeiro',
      city_region_name: 'Rio de Janeiro',
      country_code: 'BR',
      country_name: 'Brazil',
      visitors: 2500,
      views: 6000,
    },
    {
      city_id: 29,
      city_name: 'Brasília',
      city_region_name: 'Federal District',
      country_code: 'BR',
      country_name: 'Brazil',
      visitors: 1500,
      views: 3500,
    },
  ],
  IT: [
    {
      city_id: 30,
      city_name: 'Rome',
      city_region_name: 'Lazio',
      country_code: 'IT',
      country_name: 'Italy',
      visitors: 3000,
      views: 7500,
    },
    {
      city_id: 31,
      city_name: 'Milan',
      city_region_name: 'Lombardy',
      country_code: 'IT',
      country_name: 'Italy',
      visitors: 2500,
      views: 6000,
    },
    {
      city_id: 32,
      city_name: 'Florence',
      city_region_name: 'Tuscany',
      country_code: 'IT',
      country_name: 'Italy',
      visitors: 1500,
      views: 3500,
    },
  ],
  ES: [
    {
      city_id: 33,
      city_name: 'Madrid',
      city_region_name: 'Community of Madrid',
      country_code: 'ES',
      country_name: 'Spain',
      visitors: 2800,
      views: 6500,
    },
    {
      city_id: 34,
      city_name: 'Barcelona',
      city_region_name: 'Catalonia',
      country_code: 'ES',
      country_name: 'Spain',
      visitors: 2500,
      views: 6000,
    },
    {
      city_id: 35,
      city_name: 'Valencia',
      city_region_name: 'Valencian Community',
      country_code: 'ES',
      country_name: 'Spain',
      visitors: 1200,
      views: 2800,
    },
  ],
  MX: [
    {
      city_id: 36,
      city_name: 'Mexico City',
      city_region_name: 'Mexico City',
      country_code: 'MX',
      country_name: 'Mexico',
      visitors: 3000,
      views: 7000,
    },
    {
      city_id: 37,
      city_name: 'Guadalajara',
      city_region_name: 'Jalisco',
      country_code: 'MX',
      country_name: 'Mexico',
      visitors: 1800,
      views: 4200,
    },
    {
      city_id: 38,
      city_name: 'Monterrey',
      city_region_name: 'Nuevo León',
      country_code: 'MX',
      country_name: 'Mexico',
      visitors: 1200,
      views: 2800,
    },
  ],
  NL: [
    {
      city_id: 39,
      city_name: 'Amsterdam',
      city_region_name: 'North Holland',
      country_code: 'NL',
      country_name: 'Netherlands',
      visitors: 3000,
      views: 7000,
    },
    {
      city_id: 40,
      city_name: 'Rotterdam',
      city_region_name: 'South Holland',
      country_code: 'NL',
      country_name: 'Netherlands',
      visitors: 1500,
      views: 3500,
    },
    {
      city_id: 41,
      city_name: 'The Hague',
      city_region_name: 'South Holland',
      country_code: 'NL',
      country_name: 'Netherlands',
      visitors: 1000,
      views: 2300,
    },
  ],
  SE: [
    {
      city_id: 42,
      city_name: 'Stockholm',
      city_region_name: 'Stockholm County',
      country_code: 'SE',
      country_name: 'Sweden',
      visitors: 2500,
      views: 5800,
    },
    {
      city_id: 43,
      city_name: 'Gothenburg',
      city_region_name: 'Västra Götaland',
      country_code: 'SE',
      country_name: 'Sweden',
      visitors: 1500,
      views: 3500,
    },
    {
      city_id: 44,
      city_name: 'Malmö',
      city_region_name: 'Skåne',
      country_code: 'SE',
      country_name: 'Sweden',
      visitors: 1000,
      views: 2300,
    },
  ],
  CH: [
    {
      city_id: 45,
      city_name: 'Zurich',
      city_region_name: 'Zurich',
      country_code: 'CH',
      country_name: 'Switzerland',
      visitors: 2000,
      views: 4800,
    },
    {
      city_id: 46,
      city_name: 'Geneva',
      city_region_name: 'Geneva',
      country_code: 'CH',
      country_name: 'Switzerland',
      visitors: 1500,
      views: 3500,
    },
    {
      city_id: 47,
      city_name: 'Basel',
      city_region_name: 'Basel-Stadt',
      country_code: 'CH',
      country_name: 'Switzerland',
      visitors: 1000,
      views: 2300,
    },
  ],
  IR: [
    {
      city_id: 48,
      city_name: 'Tehran',
      city_region_name: 'Tehran',
      country_code: 'IR',
      country_name: 'Iran',
      visitors: 4500,
      views: 10500,
    },
    {
      city_id: 49,
      city_name: 'Isfahan',
      city_region_name: 'Isfahan',
      country_code: 'IR',
      country_name: 'Iran',
      visitors: 2000,
      views: 4800,
    },
    {
      city_id: 50,
      city_name: 'Shiraz',
      city_region_name: 'Fars',
      country_code: 'IR',
      country_name: 'Iran',
      visitors: 1500,
      views: 3500,
    },
    {
      city_id: 51,
      city_name: 'Mashhad',
      city_region_name: 'Razavi Khorasan',
      country_code: 'IR',
      country_name: 'Iran',
      visitors: 1800,
      views: 4200,
    },
  ],
}

// ========================================================================
// PERMANENT: UI Configuration (OK to keep hardcoded)
// ========================================================================

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

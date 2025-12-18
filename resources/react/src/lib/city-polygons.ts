/**
 * City Polygon Generation Utility
 *
 * Generates circular regions around city coordinates for visualization.
 * Creates small, contained polygons that don't extend beyond country borders.
 */

import type { CityData } from '@/types/geographic'

export interface CityPolygonFeature {
  type: 'Feature'
  geometry: {
    type: 'Polygon' | 'MultiPolygon'
    coordinates: number[][][] | number[][][][]
  }
  properties: CityData
}

export interface CityPolygonCollection {
  type: 'FeatureCollection'
  features: CityPolygonFeature[]
}

/**
 * Generates circular polygon regions around city coordinates
 *
 * @param cities - Array of city data with coordinates
 * @param countryCode - Country code for adjusting region size
 * @returns GeoJSON FeatureCollection of city polygons
 */
export function generateCityPolygons(
  cities: CityData[],
  countryCode?: string
): CityPolygonCollection {
  // Validate input
  if (!cities || cities.length === 0) {
    return {
      type: 'FeatureCollection',
      features: [],
    }
  }

  // Use circular buffers for clean, contained city regions
  return generateCircularBuffers(cities, countryCode)
}

/**
 * Generates circular buffer polygons around city points
 * Creates small, contained regions that stay within country borders
 *
 * @param cities - Array of city data with coordinates
 * @param countryCode - Country code for adjusting region size
 * @returns GeoJSON FeatureCollection of circular polygons
 */
function generateCircularBuffers(cities: CityData[], countryCode?: string): CityPolygonCollection {
  // Adjust radius based on country size - use VERY small values for point-like markers
  const smallCountries = ['NL', 'BE', 'CH', 'AT', 'LU', 'SG', 'HK', 'IL', 'LB', 'AE', 'KW', 'QA', 'BH']
  const mediumCountries = ['GB', 'DE', 'FR', 'IT', 'ES', 'PL', 'JP', 'KR', 'TH', 'VN', 'MY', 'PH', 'IR', 'TR', 'EG', 'PK']
  const largeCountries = ['US', 'CA', 'RU', 'CN', 'BR', 'AU', 'IN', 'AR', 'KZ', 'SA', 'MX']

  let baseRadius = 0.15 // Default radius in degrees (~17km) - small point-like markers

  if (countryCode) {
    const code = countryCode.toUpperCase()
    if (smallCountries.includes(code)) {
      baseRadius = 0.08 // ~9km for small countries
    } else if (mediumCountries.includes(code)) {
      baseRadius = 0.12 // ~13km for medium countries
    } else if (largeCountries.includes(code)) {
      baseRadius = 0.25 // ~28km for large countries
    }
  }

  const features: CityPolygonFeature[] = cities.map((city) => {
    const [lon, lat] = city.coordinates!

    // Create a circular polygon
    const segments = 32 // Number of points in circle
    const coordinates: number[][] = []

    for (let i = 0; i <= segments; i++) {
      const angle = (i / segments) * 2 * Math.PI
      // Adjust for latitude distortion (circles get wider near equator)
      const latAdjust = Math.cos((lat * Math.PI) / 180)
      const dx = (baseRadius / latAdjust) * Math.cos(angle)
      const dy = baseRadius * Math.sin(angle)

      coordinates.push([lon + dx, lat + dy])
    }

    return {
      type: 'Feature',
      geometry: {
        type: 'Polygon',
        coordinates: [coordinates],
      },
      properties: city,
    }
  })

  return {
    type: 'FeatureCollection',
    features,
  }
}

/**
 * Validates city coordinates
 *
 * @param city - City data to validate
 * @returns true if coordinates are valid
 */
export function hasValidCoordinates(city: CityData): boolean {
  if (!city.coordinates || !Array.isArray(city.coordinates)) {
    return false
  }

  const [lon, lat] = city.coordinates

  // Check if coordinates are numbers and within valid ranges
  return (
    typeof lon === 'number' &&
    typeof lat === 'number' &&
    lon >= -180 &&
    lon <= 180 &&
    lat >= -90 &&
    lat <= 90
  )
}

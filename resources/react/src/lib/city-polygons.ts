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
 * Filters cities to only those with valid coordinates from API
 *
 * @param cities - Array of city data with coordinates (from Analytics API)
 * @param countryCode - Country code for adjusting region size
 * @returns GeoJSON FeatureCollection of city polygons
 */
export function generateCityPolygons(cities: CityData[], countryCode?: string): CityPolygonCollection {
  // Validate input
  if (!cities || cities.length === 0) {
    return {
      type: 'FeatureCollection',
      features: [],
    }
  }

  // Filter to only cities with valid coordinates from API
  const citiesWithCoords = cities.filter((city) => hasValidCoordinates(city))

  if (citiesWithCoords.length === 0) {
    return {
      type: 'FeatureCollection',
      features: [],
    }
  }

  // Use circular buffers for clean, contained city regions
  return generateCircularBuffers(citiesWithCoords, countryCode)
}

/**
 * Validates city coordinates
 * Supports both tuple format [lon, lat] and separate latitude/longitude fields from API
 *
 * @param city - City data to validate
 * @returns true if coordinates are valid
 */
export function hasValidCoordinates(city: CityData): boolean {
  // Support coordinates tuple format
  if (city.coordinates && Array.isArray(city.coordinates)) {
    const [lon, lat] = city.coordinates
    return typeof lon === 'number' && typeof lat === 'number' && lon >= -180 && lon <= 180 && lat >= -90 && lat <= 90
  }

  // Support separate latitude/longitude from Analytics API
  if (city.latitude !== undefined && city.longitude !== undefined) {
    return (
      typeof city.latitude === 'number' &&
      typeof city.longitude === 'number' &&
      city.longitude >= -180 &&
      city.longitude <= 180 &&
      city.latitude >= -90 &&
      city.latitude <= 90
    )
  }

  return false
}

/**
 * Gets coordinates from city data, supporting both formats
 * Returns [longitude, latitude] tuple for map rendering
 *
 * @param city - City data with coordinates (from Analytics API or pre-processed)
 * @returns Coordinate tuple [lon, lat] or null if invalid
 */
export function getCityCoords(city: CityData): [number, number] | null {
  // Prefer coordinates tuple if available
  if (city.coordinates && Array.isArray(city.coordinates) && hasValidCoordinates(city)) {
    return city.coordinates
  }

  // Fall back to separate latitude/longitude from Analytics API
  if (city.latitude !== undefined && city.longitude !== undefined && hasValidCoordinates(city)) {
    return [city.longitude, city.latitude]
  }

  return null
}

/**
 * Generates circular buffer polygons around city points
 * Creates small, contained regions that stay within country borders
 *
 * @param cities - Array of city data with valid coordinates
 * @param countryCode - Country code for adjusting region size
 * @returns GeoJSON FeatureCollection of circular polygons
 */
function generateCircularBuffers(cities: CityData[], countryCode?: string): CityPolygonCollection {
  // Adjust radius based on country size - use VERY small values for point-like markers
  const smallCountries = ['NL', 'BE', 'CH', 'AT', 'LU', 'SG', 'HK', 'IL', 'LB', 'AE', 'KW', 'QA', 'BH']
  const mediumCountries = [
    'GB',
    'DE',
    'FR',
    'IT',
    'ES',
    'PL',
    'JP',
    'KR',
    'TH',
    'VN',
    'MY',
    'PH',
    'IR',
    'TR',
    'EG',
    'PK',
  ]
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

  const features: CityPolygonFeature[] = cities
    .map((city) => {
      const coords = getCityCoords(city)
      if (!coords) return null

      const [lon, lat] = coords

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
        type: 'Feature' as const,
        geometry: {
          type: 'Polygon' as const,
          coordinates: [coordinates],
        },
        properties: city,
      }
    })
    .filter((feature): feature is CityPolygonFeature => feature !== null)

  return {
    type: 'FeatureCollection',
    features,
  }
}

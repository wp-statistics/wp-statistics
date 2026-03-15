/**
 * Region Matcher Utility
 *
 * Provides O(n) region matching using pre-built lookup maps
 * instead of O(n²) nested iteration.
 */

export interface RegionData {
  name: string
  visitors: number
  views: number
}

export interface RegionItem {
  region_name?: string
  region_code?: string
  country_code?: string
  country_name?: string
  visitors?: number
  views?: number
}

/**
 * Creates an optimized region matcher with O(n) lookup complexity.
 *
 * Instead of iterating through all regions for each province (O(n²)),
 * this builds multiple lookup maps for different matching strategies:
 * 1. Exact match by name
 * 2. Case-insensitive match by lowercase name
 * 3. Partial match via word index
 *
 * @param regionItems - Array of region items from API
 * @returns Matcher function and data map
 */
export function createRegionMatcher(regionItems: RegionItem[]): {
  match: (provinceName: string) => RegionData | null
  dataMap: Map<string, RegionData>
  total: (metric: 'visitors' | 'views') => number
} {
  // Primary lookup: exact name match
  const exactMap = new Map<string, RegionData>()

  // Secondary lookup: lowercase name match
  const lowercaseMap = new Map<string, RegionData>()

  // Tertiary lookup: word-based partial match index
  // Maps significant words (3+ chars) to region data
  const wordIndex = new Map<string, RegionData>()

  // Build all lookup maps in a single pass - O(n)
  for (const region of regionItems) {
    const regionName = region.region_name || 'Unknown'
    const data: RegionData = {
      name: regionName,
      visitors: Number(region.visitors) || 0,
      views: Number(region.views) || 0,
    }

    // Exact match
    exactMap.set(regionName, data)

    // Case-insensitive match
    const lowerName = regionName.toLowerCase()
    lowercaseMap.set(lowerName, data)

    // Word index for partial matching
    // Extract significant words (3+ characters, alphanumeric)
    const words = lowerName.split(/\s+/)
    for (const word of words) {
      const cleanWord = word.replace(/[^a-z0-9]/gi, '')
      if (cleanWord.length >= 3) {
        // Only index if not already present (first match wins)
        if (!wordIndex.has(cleanWord)) {
          wordIndex.set(cleanWord, data)
        }
      }
    }
  }

  /**
   * Match a province name to region data - O(1) average case
   */
  function match(provinceName: string): RegionData | null {
    // 1. Try exact match first - O(1)
    if (exactMap.has(provinceName)) {
      return exactMap.get(provinceName)!
    }

    // 2. Try case-insensitive match - O(1)
    const lowerProvince = provinceName.toLowerCase()
    if (lowercaseMap.has(lowerProvince)) {
      return lowercaseMap.get(lowerProvince)!
    }

    // 3. Try word-based partial match - O(w) where w is words in provinceName
    const provinceWords = lowerProvince.split(/\s+/)
    for (const word of provinceWords) {
      const cleanWord = word.replace(/[^a-z0-9]/gi, '')
      if (cleanWord.length >= 3 && wordIndex.has(cleanWord)) {
        return wordIndex.get(cleanWord)!
      }
    }

    // 4. Try substring matching for edge cases - O(n) worst case, but rare
    // This handles cases like "New York Region" matching "New York"
    for (const [key, value] of lowercaseMap.entries()) {
      if (lowerProvince.includes(key) || key.includes(lowerProvince)) {
        return value
      }
    }

    return null
  }

  /**
   * Calculate total for a metric
   */
  function total(metric: 'visitors' | 'views'): number {
    let sum = 0
    for (const data of exactMap.values()) {
      sum += data[metric]
    }
    return sum || 1 // Avoid division by zero
  }

  return {
    match,
    dataMap: exactMap,
    total,
  }
}

/**
 * Hook-friendly wrapper that returns a stable matcher instance
 * when regionItems reference changes
 */
export function buildRegionDataMap(regionItems: RegionItem[]): Map<string, RegionData> {
  const map = new Map<string, RegionData>()

  for (const region of regionItems) {
    const regionName = region.region_name || 'Unknown'
    map.set(regionName, {
      name: regionName,
      visitors: Number(region.visitors) || 0,
      views: Number(region.views) || 0,
    })
  }

  return map
}

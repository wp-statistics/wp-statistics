/**
 * Helper functions for data table column definitions.
 * Reduces duplication in column files by providing common data transformations.
 */

import type { LocationData } from './cells/location-cell'
import type { VisitorInfoData } from './types'

/**
 * Base interface for visitor-like data (shared fields across all tables).
 * Used by createVisitorInfoData to transform row data into VisitorInfoCell format.
 */
export interface BaseVisitorFields {
  countryCode: string
  country: string
  region: string
  city: string
  os: string
  osName: string
  browser: string
  browserName: string
  browserVersion: string
  userId?: string
  username?: string
  email?: string
  userRole?: string
  hash?: string
  ipAddress?: string
}

/**
 * Creates the data object for VisitorInfoCell from visitor fields.
 * Reduces 12 lines of inline mapping to a single function call.
 *
 * @example
 * // Before (12 lines):
 * <VisitorInfoCell
 *   data={{
 *     country: { code: visitor.countryCode, name: visitor.country, ... },
 *     os: { icon: visitor.os, name: visitor.osName },
 *     browser: { icon: visitor.browser, name: visitor.browserName, version: visitor.browserVersion },
 *     user: visitor.userId && visitor.username ? { ... } : undefined,
 *     identifier: visitor.hash || visitor.ipAddress,
 *   }}
 *   config={config}
 * />
 *
 * // After (1 line):
 * <VisitorInfoCell data={createVisitorInfoData(visitor)} config={config} />
 */
export function createVisitorInfoData(visitor: BaseVisitorFields): VisitorInfoData {
  return {
    country: {
      code: visitor.countryCode,
      name: visitor.country,
      region: visitor.region,
      city: visitor.city,
    },
    os: {
      icon: visitor.os,
      name: visitor.osName,
    },
    browser: {
      icon: visitor.browser,
      name: visitor.browserName,
      version: visitor.browserVersion,
    },
    user:
      visitor.userId && visitor.username
        ? {
            id: Number(visitor.userId),
            username: visitor.username,
            email: visitor.email,
            role: visitor.userRole,
          }
        : undefined,
    identifier: visitor.hash || visitor.ipAddress,
    // Include hash and IP for single visitor page linking
    visitorHash: visitor.hash,
    ipAddress: visitor.ipAddress,
  }
}

/**
 * Creates the data object for LocationCell from visitor fields.
 * Uses smart fallback: city > region > country only.
 */
export function createLocationData(visitor: BaseVisitorFields): LocationData {
  return {
    countryCode: visitor.countryCode,
    countryName: visitor.country,
    regionName: visitor.region || undefined,
    cityName: visitor.city || undefined,
  }
}

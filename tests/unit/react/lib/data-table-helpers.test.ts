import { describe, expect, it } from 'vitest'

import {
  type BaseVisitorFields,
  createLocationData,
  createVisitorInfoData,
} from '@components/data-table-columns/helpers'

describe('data-table-helpers', () => {
  // Sample visitor data for tests
  const fullVisitor: BaseVisitorFields = {
    countryCode: 'US',
    country: 'United States',
    region: 'California',
    city: 'San Francisco',
    os: 'macos',
    osName: 'macOS',
    browser: 'chrome',
    browserName: 'Chrome',
    browserVersion: '120.0.1',
    userId: '123',
    username: 'johndoe',
    email: 'john@example.com',
    userRole: 'subscriber',
    hash: 'abc123hash',
    ipAddress: '192.168.1.1',
  }

  const anonymousVisitor: BaseVisitorFields = {
    countryCode: 'DE',
    country: 'Germany',
    region: 'Bavaria',
    city: 'Munich',
    os: 'windows',
    osName: 'Windows',
    browser: 'firefox',
    browserName: 'Firefox',
    browserVersion: '121.0',
    // No user info
    hash: 'xyz789hash',
  }

  const minimalVisitor: BaseVisitorFields = {
    countryCode: 'JP',
    country: 'Japan',
    region: '',
    city: '',
    os: 'ios',
    osName: 'iOS',
    browser: 'safari',
    browserName: 'Safari',
    browserVersion: '17.0',
    ipAddress: '10.0.0.1',
  }

  describe('createVisitorInfoData', () => {
    it('creates complete visitor info data with all fields', () => {
      const result = createVisitorInfoData(fullVisitor)

      expect(result.country).toEqual({
        code: 'US',
        name: 'United States',
        region: 'California',
        city: 'San Francisco',
      })

      expect(result.os).toEqual({
        icon: 'macos',
        name: 'macOS',
      })

      expect(result.browser).toEqual({
        icon: 'chrome',
        name: 'Chrome',
        version: '120.0.1',
      })

      expect(result.user).toEqual({
        id: 123,
        username: 'johndoe',
        email: 'john@example.com',
        role: 'subscriber',
      })

      expect(result.identifier).toBe('abc123hash')
    })

    it('sets user to undefined when no userId', () => {
      const result = createVisitorInfoData(anonymousVisitor)
      expect(result.user).toBeUndefined()
    })

    it('sets user to undefined when no username', () => {
      const visitorNoUsername: BaseVisitorFields = {
        ...fullVisitor,
        username: undefined,
      }
      const result = createVisitorInfoData(visitorNoUsername)
      expect(result.user).toBeUndefined()
    })

    it('converts userId to number', () => {
      const result = createVisitorInfoData(fullVisitor)
      expect(result.user?.id).toBe(123)
      expect(typeof result.user?.id).toBe('number')
    })

    it('uses hash as identifier when available', () => {
      const result = createVisitorInfoData(fullVisitor)
      expect(result.identifier).toBe('abc123hash')
    })

    it('falls back to ipAddress when no hash', () => {
      const result = createVisitorInfoData(minimalVisitor)
      expect(result.identifier).toBe('10.0.0.1')
    })

    it('prefers hash over ipAddress', () => {
      const result = createVisitorInfoData(fullVisitor)
      // fullVisitor has both hash and ipAddress, should use hash
      expect(result.identifier).toBe('abc123hash')
      expect(result.identifier).not.toBe('192.168.1.1')
    })

    it('handles empty strings in location fields', () => {
      const result = createVisitorInfoData(minimalVisitor)
      expect(result.country.region).toBe('')
      expect(result.country.city).toBe('')
    })

    it('handles visitor with no identifier', () => {
      const noIdVisitor: BaseVisitorFields = {
        ...minimalVisitor,
        hash: undefined,
        ipAddress: undefined,
      }
      const result = createVisitorInfoData(noIdVisitor)
      expect(result.identifier).toBeUndefined()
    })
  })

  describe('createLocationData', () => {
    it('creates location data with all fields', () => {
      const result = createLocationData(fullVisitor)

      expect(result).toEqual({
        countryCode: 'US',
        countryName: 'United States',
        regionName: 'California',
        cityName: 'San Francisco',
      })
    })

    it('sets regionName to undefined when empty string', () => {
      const result = createLocationData(minimalVisitor)
      expect(result.regionName).toBeUndefined()
    })

    it('sets cityName to undefined when empty string', () => {
      const result = createLocationData(minimalVisitor)
      expect(result.cityName).toBeUndefined()
    })

    it('handles location with only country', () => {
      const countryOnlyVisitor: BaseVisitorFields = {
        ...minimalVisitor,
        region: '',
        city: '',
      }
      const result = createLocationData(countryOnlyVisitor)
      expect(result.countryCode).toBe('JP')
      expect(result.countryName).toBe('Japan')
      expect(result.regionName).toBeUndefined()
      expect(result.cityName).toBeUndefined()
    })

    it('preserves region when city is empty', () => {
      const regionOnlyVisitor: BaseVisitorFields = {
        ...fullVisitor,
        city: '',
      }
      const result = createLocationData(regionOnlyVisitor)
      expect(result.regionName).toBe('California')
      expect(result.cityName).toBeUndefined()
    })

    it('preserves city when region is empty', () => {
      const cityOnlyVisitor: BaseVisitorFields = {
        ...fullVisitor,
        region: '',
      }
      const result = createLocationData(cityOnlyVisitor)
      expect(result.regionName).toBeUndefined()
      expect(result.cityName).toBe('San Francisco')
    })
  })
})

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import { getPresetRange, isValidPreset, PRESETS } from '@components/custom/date-range-picker'

// Mock WordPress singleton for getPresetRange tests
vi.mock('@/lib/wordpress', () => ({
  WordPress: {
    getInstance: () => ({
      getStartOfWeek: () => 1, // Monday = 1
    }),
  },
}))

describe('date-range-picker utilities', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  describe('PRESETS', () => {
    it('should contain all expected preset names', () => {
      const presetNames = PRESETS.map((p) => p.name)
      expect(presetNames).toContain('today')
      expect(presetNames).toContain('yesterday')
      expect(presetNames).toContain('thisWeek')
      expect(presetNames).toContain('last7')
      expect(presetNames).toContain('lastWeek')
      expect(presetNames).toContain('last28')
      expect(presetNames).toContain('last30')
      expect(presetNames).toContain('thisMonth')
      expect(presetNames).toContain('lastMonth')
      expect(presetNames).toContain('last90')
      expect(presetNames).toContain('quarterToDate')
      expect(presetNames).toContain('thisYear')
    })

    it('should have labels for all presets', () => {
      PRESETS.forEach((preset) => {
        expect(preset.label).toBeDefined()
        expect(preset.label.length).toBeGreaterThan(0)
      })
    })

    it('should have exactly 12 presets', () => {
      expect(PRESETS).toHaveLength(12)
    })
  })

  describe('isValidPreset', () => {
    it('should return true for valid preset names', () => {
      expect(isValidPreset('today')).toBe(true)
      expect(isValidPreset('yesterday')).toBe(true)
      expect(isValidPreset('thisWeek')).toBe(true)
      expect(isValidPreset('last7')).toBe(true)
      expect(isValidPreset('lastWeek')).toBe(true)
      expect(isValidPreset('last28')).toBe(true)
      expect(isValidPreset('last30')).toBe(true)
      expect(isValidPreset('thisMonth')).toBe(true)
      expect(isValidPreset('lastMonth')).toBe(true)
      expect(isValidPreset('last90')).toBe(true)
      expect(isValidPreset('quarterToDate')).toBe(true)
      expect(isValidPreset('thisYear')).toBe(true)
    })

    it('should return false for invalid preset names', () => {
      expect(isValidPreset('invalid')).toBe(false)
      expect(isValidPreset('custom')).toBe(false)
      expect(isValidPreset('last_month')).toBe(false)
      expect(isValidPreset('YESTERDAY')).toBe(false)
      expect(isValidPreset('')).toBe(false)
      // These presets no longer exist
      expect(isValidPreset('last14')).toBe(false)
      expect(isValidPreset('last3months')).toBe(false)
      expect(isValidPreset('last6months')).toBe(false)
      expect(isValidPreset('lastYear')).toBe(false)
    })

    it('should return false for undefined', () => {
      expect(isValidPreset(undefined)).toBe(false)
    })
  })

  describe('getPresetRange', () => {
    describe('today preset', () => {
      it("should return today's date for both from and to", () => {
        const now = new Date()
        const result = getPresetRange('today')

        expect(result.from.getFullYear()).toBe(now.getFullYear())
        expect(result.from.getMonth()).toBe(now.getMonth())
        expect(result.from.getDate()).toBe(now.getDate())
        expect(result.to?.getFullYear()).toBe(now.getFullYear())
        expect(result.to?.getMonth()).toBe(now.getMonth())
        expect(result.to?.getDate()).toBe(now.getDate())
      })
    })

    describe('yesterday preset', () => {
      it("should return yesterday's date for both from and to", () => {
        const now = new Date()
        const yesterday = new Date(now)
        yesterday.setDate(yesterday.getDate() - 1)

        const result = getPresetRange('yesterday')

        expect(result.from.getFullYear()).toBe(yesterday.getFullYear())
        expect(result.from.getMonth()).toBe(yesterday.getMonth())
        expect(result.from.getDate()).toBe(yesterday.getDate())
        expect(result.to?.getFullYear()).toBe(yesterday.getFullYear())
        expect(result.to?.getMonth()).toBe(yesterday.getMonth())
        expect(result.to?.getDate()).toBe(yesterday.getDate())
      })
    })

    describe('last7 preset', () => {
      it('should return 7 day range ending today', () => {
        const now = new Date()
        const expectedFrom = new Date(now)
        expectedFrom.setDate(now.getDate() - 6)

        const result = getPresetRange('last7')

        expect(result.from.getFullYear()).toBe(expectedFrom.getFullYear())
        expect(result.from.getMonth()).toBe(expectedFrom.getMonth())
        expect(result.from.getDate()).toBe(expectedFrom.getDate())
        expect(result.to?.getDate()).toBe(now.getDate())
      })
    })

    describe('last30 preset', () => {
      it('should return 30 day range ending today', () => {
        const now = new Date()
        const expectedFrom = new Date(now)
        expectedFrom.setDate(now.getDate() - 29)

        const result = getPresetRange('last30')

        expect(result.from.getFullYear()).toBe(expectedFrom.getFullYear())
        expect(result.from.getMonth()).toBe(expectedFrom.getMonth())
        expect(result.from.getDate()).toBe(expectedFrom.getDate())
        expect(result.to?.getDate()).toBe(now.getDate())
      })
    })

    describe('last90 preset', () => {
      it('should return 90 day range ending today', () => {
        const now = new Date()
        const expectedFrom = new Date(now)
        expectedFrom.setDate(now.getDate() - 89)

        const result = getPresetRange('last90')

        expect(result.from.getFullYear()).toBe(expectedFrom.getFullYear())
        expect(result.from.getMonth()).toBe(expectedFrom.getMonth())
        expect(result.from.getDate()).toBe(expectedFrom.getDate())
        expect(result.to?.getDate()).toBe(now.getDate())
      })
    })

    describe('lastMonth preset', () => {
      it('should return the entire previous month', () => {
        const now = new Date()
        const expectedMonth = new Date(now)
        expectedMonth.setMonth(expectedMonth.getMonth() - 1)

        const result = getPresetRange('lastMonth')

        expect(result.from.getFullYear()).toBe(expectedMonth.getFullYear())
        expect(result.from.getMonth()).toBe(expectedMonth.getMonth())
        expect(result.from.getDate()).toBe(1)
      })
    })

    describe('thisYear preset', () => {
      it('should return from Jan 1 of current year to today', () => {
        const now = new Date()
        const result = getPresetRange('thisYear')

        expect(result.from.getFullYear()).toBe(now.getFullYear())
        expect(result.from.getMonth()).toBe(0) // January
        expect(result.from.getDate()).toBe(1)
        expect(result.to?.getFullYear()).toBe(now.getFullYear())
        expect(result.to?.getMonth()).toBe(now.getMonth())
        expect(result.to?.getDate()).toBe(now.getDate())
      })
    })

    it('should throw error for invalid preset name', () => {
      expect(() => getPresetRange('invalid')).toThrow('Unknown date range preset: invalid')
    })
  })

  describe('period resolution - verifies presets resolve dynamically', () => {
    it('yesterday should resolve differently as time passes', () => {
      const now = new Date()
      const tomorrow = new Date(now)
      tomorrow.setDate(now.getDate() + 1)

      vi.useFakeTimers()

      vi.setSystemTime(now)
      const yesterdayToday = getPresetRange('yesterday')

      vi.setSystemTime(tomorrow)
      const yesterdayTomorrow = getPresetRange('yesterday')

      // The dates should differ by exactly 1 day
      const diffMs = yesterdayTomorrow.from.getTime() - yesterdayToday.from.getTime()
      const diffDays = diffMs / (1000 * 60 * 60 * 24)
      expect(diffDays).toBe(1)
    })

    it('today should always return the current system date', () => {
      const now = new Date()
      const fiveDaysLater = new Date(now)
      fiveDaysLater.setDate(now.getDate() + 5)

      vi.useFakeTimers()

      vi.setSystemTime(now)
      const todayNow = getPresetRange('today')
      expect(todayNow.from.getDate()).toBe(now.getDate())

      vi.setSystemTime(fiveDaysLater)
      const todayLater = getPresetRange('today')
      expect(todayLater.from.getDate()).toBe(fiveDaysLater.getDate())
    })

    it('last30 should shift as days pass', () => {
      const now = new Date()
      const tomorrow = new Date(now)
      tomorrow.setDate(now.getDate() + 1)

      vi.useFakeTimers()

      vi.setSystemTime(now)
      const last30Today = getPresetRange('last30')

      vi.setSystemTime(tomorrow)
      const last30Tomorrow = getPresetRange('last30')

      // The from date should shift by 1 day
      const diffMs = last30Tomorrow.from.getTime() - last30Today.from.getTime()
      const diffDays = diffMs / (1000 * 60 * 60 * 24)
      expect(diffDays).toBe(1)
    })
  })
})

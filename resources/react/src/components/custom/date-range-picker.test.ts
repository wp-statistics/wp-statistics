import { beforeEach, describe, expect, it, vi } from 'vitest'

import { getPresetRange, isValidPreset, PRESETS } from './date-range-picker'

describe('date-range-picker utilities', () => {
  // Mock the current date to ensure consistent test results
  const mockDate = new Date('2026-01-09T12:00:00.000Z')

  beforeEach(() => {
    vi.useFakeTimers()
    vi.setSystemTime(mockDate)
  })

  describe('PRESETS', () => {
    it('should contain all expected preset names', () => {
      const presetNames = PRESETS.map((p) => p.name)
      expect(presetNames).toContain('today')
      expect(presetNames).toContain('yesterday')
      expect(presetNames).toContain('lastWeek')
      expect(presetNames).toContain('last14')
      expect(presetNames).toContain('last30')
      expect(presetNames).toContain('lastMonth')
      expect(presetNames).toContain('last3months')
      expect(presetNames).toContain('last6months')
      expect(presetNames).toContain('lastYear')
    })

    it('should have labels for all presets', () => {
      PRESETS.forEach((preset) => {
        expect(preset.label).toBeDefined()
        expect(preset.label.length).toBeGreaterThan(0)
      })
    })
  })

  describe('isValidPreset', () => {
    it('should return true for valid preset names', () => {
      expect(isValidPreset('today')).toBe(true)
      expect(isValidPreset('yesterday')).toBe(true)
      expect(isValidPreset('lastWeek')).toBe(true)
      expect(isValidPreset('last14')).toBe(true)
      expect(isValidPreset('last30')).toBe(true)
      expect(isValidPreset('lastMonth')).toBe(true)
      expect(isValidPreset('last3months')).toBe(true)
      expect(isValidPreset('last6months')).toBe(true)
      expect(isValidPreset('lastYear')).toBe(true)
    })

    it('should return false for invalid preset names', () => {
      expect(isValidPreset('invalid')).toBe(false)
      expect(isValidPreset('custom')).toBe(false)
      expect(isValidPreset('last_month')).toBe(false) // underscore vs camelCase
      expect(isValidPreset('YESTERDAY')).toBe(false) // case sensitive
      expect(isValidPreset('')).toBe(false)
    })

    it('should return false for undefined', () => {
      expect(isValidPreset(undefined)).toBe(false)
    })
  })

  describe('getPresetRange', () => {
    describe('today preset', () => {
      it('should return today\'s date for both from and to', () => {
        const result = getPresetRange('today')
        expect(result.from.getFullYear()).toBe(2026)
        expect(result.from.getMonth()).toBe(0) // January
        expect(result.from.getDate()).toBe(9)
        expect(result.to?.getFullYear()).toBe(2026)
        expect(result.to?.getMonth()).toBe(0)
        expect(result.to?.getDate()).toBe(9)
      })
    })

    describe('yesterday preset', () => {
      it('should return yesterday\'s date for both from and to', () => {
        const result = getPresetRange('yesterday')
        expect(result.from.getFullYear()).toBe(2026)
        expect(result.from.getMonth()).toBe(0) // January
        expect(result.from.getDate()).toBe(8) // Yesterday
        expect(result.to?.getFullYear()).toBe(2026)
        expect(result.to?.getMonth()).toBe(0)
        expect(result.to?.getDate()).toBe(8)
      })
    })

    describe('last14 preset', () => {
      it('should return 14 day range ending today', () => {
        const result = getPresetRange('last14')
        // From should be 13 days ago (14 days including today)
        // Jan 9 - 13 days = Dec 27, 2025
        expect(result.from.getFullYear()).toBe(2025)
        expect(result.from.getMonth()).toBe(11) // December
        expect(result.from.getDate()).toBe(27)
        expect(result.to?.getDate()).toBe(9) // Today
      })
    })

    describe('last30 preset', () => {
      it('should return 30 day range ending today', () => {
        const result = getPresetRange('last30')
        // From should be 29 days ago (30 days including today)
        const expectedFrom = new Date(2026, 0, 9 - 29) // Dec 11, 2025
        expect(result.from.getMonth()).toBe(expectedFrom.getMonth())
        expect(result.from.getDate()).toBe(expectedFrom.getDate())
        expect(result.to?.getDate()).toBe(9) // Today
      })
    })

    describe('lastMonth preset', () => {
      it('should return the entire previous month', () => {
        const result = getPresetRange('lastMonth')
        // December 2025
        expect(result.from.getFullYear()).toBe(2025)
        expect(result.from.getMonth()).toBe(11) // December
        expect(result.from.getDate()).toBe(1) // First day
      })
    })

    describe('lastYear preset', () => {
      it('should return dates from one year ago', () => {
        const result = getPresetRange('lastYear')
        expect(result.from.getFullYear()).toBe(2025)
      })
    })

    it('should throw error for invalid preset name', () => {
      expect(() => getPresetRange('invalid')).toThrow('Unknown date range preset: invalid')
    })
  })

  describe('period resolution - dynamic dates', () => {
    it('yesterday should resolve differently on different days', () => {
      // First, get yesterday for Jan 9, 2026
      vi.setSystemTime(new Date('2026-01-09T12:00:00.000Z'))
      const yesterdayOnJan9 = getPresetRange('yesterday')
      expect(yesterdayOnJan9.from.getDate()).toBe(8)

      // Now simulate the next day (Jan 10, 2026)
      vi.setSystemTime(new Date('2026-01-10T12:00:00.000Z'))
      const yesterdayOnJan10 = getPresetRange('yesterday')
      expect(yesterdayOnJan10.from.getDate()).toBe(9)

      // The dates should be different
      expect(yesterdayOnJan9.from.getTime()).not.toBe(yesterdayOnJan10.from.getTime())
    })

    it('today should always return the current date', () => {
      // Jan 9
      vi.setSystemTime(new Date('2026-01-09T12:00:00.000Z'))
      const todayOnJan9 = getPresetRange('today')
      expect(todayOnJan9.from.getDate()).toBe(9)

      // Jan 15
      vi.setSystemTime(new Date('2026-01-15T12:00:00.000Z'))
      const todayOnJan15 = getPresetRange('today')
      expect(todayOnJan15.from.getDate()).toBe(15)
    })

    it('last30 should shift as days pass', () => {
      // On Jan 9, last30 should start Dec 11
      vi.setSystemTime(new Date('2026-01-09T12:00:00.000Z'))
      const last30OnJan9 = getPresetRange('last30')

      // On Jan 10, last30 should start Dec 12
      vi.setSystemTime(new Date('2026-01-10T12:00:00.000Z'))
      const last30OnJan10 = getPresetRange('last30')

      // The from date should shift by 1 day
      const diffMs = last30OnJan10.from.getTime() - last30OnJan9.from.getTime()
      const diffDays = diffMs / (1000 * 60 * 60 * 24)
      expect(diffDays).toBe(1)
    })
  })
})

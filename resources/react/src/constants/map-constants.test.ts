import { describe, it, expect } from 'vitest'
import {
  calculateZoomFromDimension,
  getColorFromScale,
  MAP_ANIMATION,
  MAP_ZOOM,
  COUNTRY_SIZE_ZOOM_MAP,
  COLOR_SCALE_THRESHOLDS,
} from './map-constants'

describe('map-constants', () => {
  describe('calculateZoomFromDimension', () => {
    it('should return correct zoom for very large countries (> 60)', () => {
      expect(calculateZoomFromDimension(70)).toBe(3.5) // Russia, Canada
      expect(calculateZoomFromDimension(65)).toBe(3.5) // USA, China
    })

    it('should return correct zoom for large countries (40-60)', () => {
      expect(calculateZoomFromDimension(50)).toBe(4.5) // Brazil
      expect(calculateZoomFromDimension(45)).toBe(4.5) // Australia
    })

    it('should return correct zoom for medium-large countries (25-40)', () => {
      expect(calculateZoomFromDimension(35)).toBe(6) // Iran
      expect(calculateZoomFromDimension(30)).toBe(6) // Algeria
    })

    it('should return correct zoom for medium countries (15-25)', () => {
      expect(calculateZoomFromDimension(20)).toBe(7.5) // France
      expect(calculateZoomFromDimension(18)).toBe(7.5) // Spain
    })

    it('should return correct zoom for small countries (8-15)', () => {
      expect(calculateZoomFromDimension(12)).toBe(9) // UK
      expect(calculateZoomFromDimension(10)).toBe(9) // Germany
    })

    it('should return correct zoom for very small countries (4-8)', () => {
      expect(calculateZoomFromDimension(6)).toBe(11) // Netherlands
      expect(calculateZoomFromDimension(5)).toBe(11) // Belgium
    })

    it('should return correct zoom for tiny countries (< 4)', () => {
      expect(calculateZoomFromDimension(3)).toBe(14) // Singapore
      expect(calculateZoomFromDimension(1)).toBe(14) // Luxembourg
    })

    it('should return fallback for edge case of 0', () => {
      // 0 is not > any threshold, so returns MAP_ZOOM.FALLBACK (6)
      expect(calculateZoomFromDimension(0)).toBe(6)
    })

    it('should handle negative values gracefully', () => {
      // Negative values are not > any threshold, so returns fallback
      expect(calculateZoomFromDimension(-10)).toBe(6)
    })
  })

  describe('getColorFromScale', () => {
    const testColorScale = ['#color0', '#color1', '#color2', '#color3', '#color4', '#color5']

    it('should return no-data color for zero value', () => {
      expect(getColorFromScale(0, testColorScale)).toBe(COLOR_SCALE_THRESHOLDS.NO_DATA_COLOR)
    })

    it('should return no-data color for negative value', () => {
      expect(getColorFromScale(-0.5, testColorScale)).toBe(COLOR_SCALE_THRESHOLDS.NO_DATA_COLOR)
    })

    it('should return first color for values < 0.2', () => {
      expect(getColorFromScale(0.1, testColorScale)).toBe('#color0')
      expect(getColorFromScale(0.19, testColorScale)).toBe('#color0')
    })

    it('should return second color for values 0.2-0.4', () => {
      expect(getColorFromScale(0.2, testColorScale)).toBe('#color1')
      expect(getColorFromScale(0.35, testColorScale)).toBe('#color1')
    })

    it('should return third color for values 0.4-0.6', () => {
      expect(getColorFromScale(0.4, testColorScale)).toBe('#color2')
      expect(getColorFromScale(0.55, testColorScale)).toBe('#color2')
    })

    it('should return fourth color for values 0.6-0.8', () => {
      expect(getColorFromScale(0.6, testColorScale)).toBe('#color3')
      expect(getColorFromScale(0.75, testColorScale)).toBe('#color3')
    })

    it('should return fifth color for values 0.8-0.9', () => {
      expect(getColorFromScale(0.8, testColorScale)).toBe('#color4')
      expect(getColorFromScale(0.85, testColorScale)).toBe('#color4')
    })

    it('should return last color for values >= 0.9', () => {
      expect(getColorFromScale(0.9, testColorScale)).toBe('#color5')
      expect(getColorFromScale(1.0, testColorScale)).toBe('#color5')
      expect(getColorFromScale(1.5, testColorScale)).toBe('#color5') // Over 1.0
    })
  })

  describe('constant values', () => {
    it('should have valid animation timing values', () => {
      expect(MAP_ANIMATION.DURATION_MS).toBeGreaterThan(0)
      expect(MAP_ANIMATION.SCROLL_HINT_TIMEOUT_MS).toBeGreaterThan(0)
      expect(MAP_ANIMATION.PROVINCES_LOADING_DELAY_MS).toBeGreaterThanOrEqual(0)
    })

    it('should have valid zoom configuration', () => {
      expect(MAP_ZOOM.MIN).toBe(1)
      expect(MAP_ZOOM.MAX).toBeGreaterThan(MAP_ZOOM.MIN)
      expect(MAP_ZOOM.STEP_MULTIPLIER).toBeGreaterThan(1)
      expect(MAP_ZOOM.DEFAULT).toBe(1)
      expect(MAP_ZOOM.FALLBACK).toBeGreaterThan(0)
    })

    it('should have country size zoom map in descending order', () => {
      let prevDimension = Infinity
      for (const { maxDimension } of COUNTRY_SIZE_ZOOM_MAP) {
        expect(maxDimension).toBeLessThan(prevDimension)
        prevDimension = maxDimension
      }
    })

    it('should have valid color thresholds in ascending order', () => {
      const thresholds = COLOR_SCALE_THRESHOLDS.THRESHOLDS
      let prevThreshold = 0
      for (const threshold of thresholds) {
        expect(threshold).toBeGreaterThan(prevThreshold)
        expect(threshold).toBeLessThanOrEqual(1)
        prevThreshold = threshold
      }
    })
  })
})

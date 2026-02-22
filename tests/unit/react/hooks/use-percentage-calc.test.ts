import { renderHook } from '@testing-library/react'
import { describe, expect, it, vi } from 'vitest'

import { usePercentageCalc, calcPercentage } from '@hooks/use-percentage-calc'

// Mock formatDecimal from utils
vi.mock('@/lib/utils', () => ({
  formatDecimal: vi.fn((value: number) => {
    // Simple mock that rounds to 2 decimal places
    const rounded = Math.round(value * 100) / 100
    return rounded.toString()
  }),
}))

describe('usePercentageCalc', () => {
  describe('Basic Calculations', () => {
    it('should calculate positive percentage change', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(150, 100)

      expect(output.percentage).toBe('50')
      expect(output.isNegative).toBe(false)
    })

    it('should calculate negative percentage change', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(50, 100)

      expect(output.percentage).toBe('50')
      expect(output.isNegative).toBe(true)
    })

    it('should calculate 100% increase', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(200, 100)

      expect(output.percentage).toBe('100')
      expect(output.isNegative).toBe(false)
    })

    it('should calculate 50% decrease', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(75, 150)

      expect(output.percentage).toBe('50')
      expect(output.isNegative).toBe(true)
    })
  })

  describe('Edge Cases', () => {
    it('should return 0% when both values are 0', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(0, 0)

      expect(output.percentage).toBe('0')
      expect(output.isNegative).toBe(false)
    })

    it('should return 100% when previous is 0 and current is positive', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(100, 0)

      expect(output.percentage).toBe('100')
      expect(output.isNegative).toBe(false)
    })

    it('should return 100% when previous is 0 and current is any positive value', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current

      expect(calc(1, 0).percentage).toBe('100')
      expect(calc(1000, 0).percentage).toBe('100')
      expect(calc(0.5, 0).percentage).toBe('100')
    })

    it('should handle no change correctly', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(100, 100)

      expect(output.percentage).toBe('0')
      expect(output.isNegative).toBe(false)
    })
  })

  describe('Decimal Values', () => {
    it('should handle decimal current values', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(10.5, 10)

      expect(output.percentage).toBe('5')
      expect(output.isNegative).toBe(false)
    })

    it('should handle decimal previous values', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(10, 8)

      expect(output.percentage).toBe('25')
      expect(output.isNegative).toBe(false)
    })

    it('should handle small decimal changes', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(100.1, 100)

      expect(output.percentage).toBe('0.1')
      expect(output.isNegative).toBe(false)
    })
  })

  describe('Large Numbers', () => {
    it('should handle large numbers', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(1000000, 500000)

      expect(output.percentage).toBe('100')
      expect(output.isNegative).toBe(false)
    })

    it('should handle very small percentage changes with large numbers', () => {
      const { result } = renderHook(() => usePercentageCalc())

      const calc = result.current
      const output = calc(1000001, 1000000)

      expect(output.percentage).toBe('0')
      expect(output.isNegative).toBe(false)
    })
  })

  describe('Hook Memoization', () => {
    it('should return memoized function', () => {
      const { result, rerender } = renderHook(() => usePercentageCalc())

      const firstCalc = result.current

      rerender()

      const secondCalc = result.current

      // The function should be the same reference due to useCallback
      expect(firstCalc).toBe(secondCalc)
    })
  })
})

describe('calcPercentage (non-hook version)', () => {
  describe('Basic Calculations', () => {
    it('should calculate positive percentage change', () => {
      const output = calcPercentage(150, 100)

      expect(output.percentage).toBe('50')
      expect(output.isNegative).toBe(false)
    })

    it('should calculate negative percentage change', () => {
      const output = calcPercentage(50, 100)

      expect(output.percentage).toBe('50')
      expect(output.isNegative).toBe(true)
    })
  })

  describe('Edge Cases', () => {
    it('should return 0% when both values are 0', () => {
      const output = calcPercentage(0, 0)

      expect(output.percentage).toBe('0')
      expect(output.isNegative).toBe(false)
    })

    it('should return 100% when previous is 0 and current is positive', () => {
      const output = calcPercentage(100, 0)

      expect(output.percentage).toBe('100')
      expect(output.isNegative).toBe(false)
    })

    it('should handle no change correctly', () => {
      const output = calcPercentage(100, 100)

      expect(output.percentage).toBe('0')
      expect(output.isNegative).toBe(false)
    })
  })

  describe('Usage Outside React Components', () => {
    it('should work without React context', () => {
      // This test verifies that calcPercentage can be used in non-React code
      const results = [
        calcPercentage(200, 100),
        calcPercentage(50, 100),
        calcPercentage(100, 100),
      ]

      expect(results[0]).toEqual({ percentage: '100', isNegative: false })
      expect(results[1]).toEqual({ percentage: '50', isNegative: true })
      expect(results[2]).toEqual({ percentage: '0', isNegative: false })
    })
  })
})

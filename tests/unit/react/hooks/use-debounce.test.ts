import { act, renderHook } from '@testing-library/react'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import { useDebounce } from '@hooks/use-debounce'

describe('useDebounce', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  describe('Basic Functionality', () => {
    it('should return initial value immediately', () => {
      const { result } = renderHook(() => useDebounce('initial', 500))
      expect(result.current).toBe('initial')
    })

    it('should not update value before delay', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 500 } }
      )

      rerender({ value: 'second', delay: 500 })

      // Advance time but not past delay
      act(() => {
        vi.advanceTimersByTime(300)
      })

      expect(result.current).toBe('first')
    })

    it('should update value after delay', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 500 } }
      )

      rerender({ value: 'second', delay: 500 })

      act(() => {
        vi.advanceTimersByTime(500)
      })

      expect(result.current).toBe('second')
    })

    it('should reset timer on new value', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 500 } }
      )

      // First change
      rerender({ value: 'second', delay: 500 })
      act(() => {
        vi.advanceTimersByTime(300)
      })

      // Second change before delay - should reset timer
      rerender({ value: 'third', delay: 500 })
      act(() => {
        vi.advanceTimersByTime(300)
      })

      // Still showing first value because timer was reset
      expect(result.current).toBe('first')

      // Complete the delay for 'third'
      act(() => {
        vi.advanceTimersByTime(200)
      })

      expect(result.current).toBe('third')
    })
  })

  describe('Different Value Types', () => {
    it('should work with numbers', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 0, delay: 100 } }
      )

      rerender({ value: 42, delay: 100 })

      act(() => {
        vi.advanceTimersByTime(100)
      })

      expect(result.current).toBe(42)
    })

    it('should work with objects', () => {
      const initialObj = { name: 'initial' }
      const newObj = { name: 'updated' }

      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: initialObj, delay: 100 } }
      )

      rerender({ value: newObj, delay: 100 })

      act(() => {
        vi.advanceTimersByTime(100)
      })

      expect(result.current).toEqual(newObj)
    })

    it('should work with arrays', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: [1, 2], delay: 100 } }
      )

      rerender({ value: [3, 4, 5], delay: 100 })

      act(() => {
        vi.advanceTimersByTime(100)
      })

      expect(result.current).toEqual([3, 4, 5])
    })

    it('should work with null and undefined', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce<string | null | undefined>(value, delay),
        { initialProps: { value: 'initial' as string | null | undefined, delay: 100 } }
      )

      rerender({ value: null, delay: 100 })
      act(() => {
        vi.advanceTimersByTime(100)
      })
      expect(result.current).toBeNull()

      rerender({ value: undefined, delay: 100 })
      act(() => {
        vi.advanceTimersByTime(100)
      })
      expect(result.current).toBeUndefined()
    })

    it('should work with boolean', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: false, delay: 100 } }
      )

      rerender({ value: true, delay: 100 })

      act(() => {
        vi.advanceTimersByTime(100)
      })

      expect(result.current).toBe(true)
    })
  })

  describe('Delay Changes', () => {
    it('should respect new delay value', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 500 } }
      )

      // Change both value and delay
      rerender({ value: 'second', delay: 200 })

      act(() => {
        vi.advanceTimersByTime(200)
      })

      expect(result.current).toBe('second')
    })

    it('should handle zero delay', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 0 } }
      )

      rerender({ value: 'second', delay: 0 })

      act(() => {
        vi.advanceTimersByTime(0)
      })

      expect(result.current).toBe('second')
    })

    it('should handle very long delay', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 10000 } }
      )

      rerender({ value: 'second', delay: 10000 })

      act(() => {
        vi.advanceTimersByTime(9999)
      })

      expect(result.current).toBe('first')

      act(() => {
        vi.advanceTimersByTime(1)
      })

      expect(result.current).toBe('second')
    })
  })

  describe('Cleanup', () => {
    it('should clear timeout on unmount', () => {
      const clearTimeoutSpy = vi.spyOn(global, 'clearTimeout')

      const { unmount, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 500 } }
      )

      rerender({ value: 'second', delay: 500 })
      unmount()

      expect(clearTimeoutSpy).toHaveBeenCalled()
    })

    it('should not update state after unmount', () => {
      const { result, unmount, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'first', delay: 500 } }
      )

      rerender({ value: 'second', delay: 500 })
      unmount()

      // This should not cause any errors
      act(() => {
        vi.advanceTimersByTime(500)
      })

      // Value should still be 'first' (but we can't really check after unmount)
      // The main thing is that no error was thrown
    })
  })

  describe('Rapid Updates', () => {
    it('should only emit final value after rapid updates', () => {
      const { result, rerender } = renderHook(
        ({ value, delay }) => useDebounce(value, delay),
        { initialProps: { value: 'a', delay: 100 } }
      )

      // Rapid updates
      rerender({ value: 'b', delay: 100 })
      act(() => { vi.advanceTimersByTime(20) })

      rerender({ value: 'c', delay: 100 })
      act(() => { vi.advanceTimersByTime(20) })

      rerender({ value: 'd', delay: 100 })
      act(() => { vi.advanceTimersByTime(20) })

      rerender({ value: 'e', delay: 100 })

      // Still showing initial value
      expect(result.current).toBe('a')

      // Complete the delay
      act(() => {
        vi.advanceTimersByTime(100)
      })

      // Should show final value
      expect(result.current).toBe('e')
    })
  })
})

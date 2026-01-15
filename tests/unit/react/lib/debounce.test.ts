import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import { debounce, createDebouncedRef } from '@lib/debounce'

describe('debounce', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  describe('basic functionality', () => {
    it('should delay function execution', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn()
      expect(fn).not.toHaveBeenCalled()

      vi.advanceTimersByTime(100)
      expect(fn).toHaveBeenCalledTimes(1)
    })

    it('should pass arguments to the debounced function', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn('arg1', 'arg2')
      vi.advanceTimersByTime(100)

      expect(fn).toHaveBeenCalledWith('arg1', 'arg2')
    })

    it('should only execute once when called multiple times within delay', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn('first')
      debouncedFn('second')
      debouncedFn('third')

      vi.advanceTimersByTime(100)

      expect(fn).toHaveBeenCalledTimes(1)
      expect(fn).toHaveBeenCalledWith('third')
    })

    it('should reset delay on each call', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn()
      vi.advanceTimersByTime(50)
      expect(fn).not.toHaveBeenCalled()

      debouncedFn()
      vi.advanceTimersByTime(50)
      expect(fn).not.toHaveBeenCalled()

      vi.advanceTimersByTime(50)
      expect(fn).toHaveBeenCalledTimes(1)
    })

    it('should allow multiple separate executions', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn('first')
      vi.advanceTimersByTime(100)
      expect(fn).toHaveBeenCalledTimes(1)
      expect(fn).toHaveBeenLastCalledWith('first')

      debouncedFn('second')
      vi.advanceTimersByTime(100)
      expect(fn).toHaveBeenCalledTimes(2)
      expect(fn).toHaveBeenLastCalledWith('second')
    })
  })

  describe('cancel', () => {
    it('should have a cancel method', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      expect(typeof debouncedFn.cancel).toBe('function')
    })

    it('should cancel pending execution', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn()
      debouncedFn.cancel()
      vi.advanceTimersByTime(100)

      expect(fn).not.toHaveBeenCalled()
    })

    it('should be safe to call cancel multiple times', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn()
      debouncedFn.cancel()
      debouncedFn.cancel()
      debouncedFn.cancel()

      vi.advanceTimersByTime(100)
      expect(fn).not.toHaveBeenCalled()
    })

    it('should be safe to call cancel when no pending execution', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      // Should not throw
      debouncedFn.cancel()
      expect(fn).not.toHaveBeenCalled()
    })

    it('should allow new calls after cancel', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn('first')
      debouncedFn.cancel()

      debouncedFn('second')
      vi.advanceTimersByTime(100)

      expect(fn).toHaveBeenCalledTimes(1)
      expect(fn).toHaveBeenCalledWith('second')
    })
  })

  describe('edge cases', () => {
    it('should work with zero delay', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 0)

      debouncedFn()
      vi.advanceTimersByTime(0)

      expect(fn).toHaveBeenCalledTimes(1)
    })

    it('should handle functions with no arguments', () => {
      const fn = vi.fn()
      const debouncedFn = debounce(fn, 100)

      debouncedFn()
      vi.advanceTimersByTime(100)

      expect(fn).toHaveBeenCalledTimes(1)
      expect(fn).toHaveBeenCalledWith()
    })

    it('should preserve this context when using arrow functions', () => {
      const obj = {
        value: 42,
        method: vi.fn(function (this: { value: number }) {
          return this.value
        }),
      }

      const debouncedMethod = debounce(() => obj.method(), 100)
      debouncedMethod()
      vi.advanceTimersByTime(100)

      expect(obj.method).toHaveBeenCalled()
    })
  })
})

describe('createDebouncedRef', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  it('should return a debounced function and ref', () => {
    const fn = vi.fn()
    const { debouncedFn, ref } = createDebouncedRef(fn, 100)

    expect(typeof debouncedFn).toBe('function')
    expect(typeof debouncedFn.cancel).toBe('function')
    expect(ref.current).toBe(debouncedFn)
  })

  it('should debounce calls through ref.current', () => {
    const fn = vi.fn()
    const { ref } = createDebouncedRef(fn, 100)

    ref.current('test')
    expect(fn).not.toHaveBeenCalled()

    vi.advanceTimersByTime(100)
    expect(fn).toHaveBeenCalledWith('test')
  })

  it('should allow cancellation through ref.current', () => {
    const fn = vi.fn()
    const { ref } = createDebouncedRef(fn, 100)

    ref.current('test')
    ref.current.cancel()
    vi.advanceTimersByTime(100)

    expect(fn).not.toHaveBeenCalled()
  })
})

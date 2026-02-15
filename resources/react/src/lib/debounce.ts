/**
 * Debounce Utility
 *
 * Creates a debounced version of a function that delays invoking the function
 * until after a specified delay has elapsed since the last time it was invoked.
 *
 * Useful for performance optimization in scenarios like:
 * - Search input handling
 * - Window resize handlers
 * - Scroll event handlers
 * - Saving user preferences
 *
 * @example
 * const debouncedSave = debounce((value: string) => {
 *   saveToServer(value)
 * }, 300)
 *
 * // Call multiple times rapidly
 * debouncedSave('a')
 * debouncedSave('ab')
 * debouncedSave('abc') // Only this call will execute after 300ms
 *
 * // Cancel pending execution if needed
 * debouncedSave.cancel()
 */

/**
 * Creates a debounced version of a function.
 *
 * @param fn - The function to debounce
 * @param delay - The delay in milliseconds
 * @returns A debounced version of the function with a cancel method
 */
export function debounce<T extends (...args: Parameters<T>) => void>(
  fn: T,
  delay: number
): T & { cancel: () => void } {
  let timeoutId: ReturnType<typeof setTimeout> | null = null

  const debounced = ((...args: Parameters<T>) => {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      fn(...args)
      timeoutId = null
    }, delay)
  }) as T & { cancel: () => void }

  /**
   * Cancels any pending debounced execution.
   * Call this when cleaning up (e.g., in useEffect cleanup).
   */
  debounced.cancel = () => {
    if (timeoutId) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
  }

  return debounced
}

/**
 * Creates a debounced function that can be used as a React ref.
 * Returns both the debounced function and a stable reference to it.
 *
 * @example
 * const { debouncedFn, ref } = createDebouncedRef((value: string) => {
 *   saveToServer(value)
 * }, 300)
 *
 * // Use ref.current to access the debounced function
 * ref.current('test')
 */
export function createDebouncedRef<T extends (...args: Parameters<T>) => void>(
  fn: T,
  delay: number
): { debouncedFn: T & { cancel: () => void }; ref: { current: T & { cancel: () => void } } } {
  const debouncedFn = debounce(fn, delay)
  return {
    debouncedFn,
    ref: { current: debouncedFn },
  }
}

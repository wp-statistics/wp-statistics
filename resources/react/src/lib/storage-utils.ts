/**
 * Safe localStorage utilities that handle errors gracefully.
 * Prevents crashes when localStorage is unavailable (private browsing, storage quota exceeded, etc.)
 */

/**
 * Safely get an item from localStorage
 * @param key - The storage key
 * @returns The stored value or null if not found/error
 */
export function safeGetItem(key: string): string | null {
  try {
    return localStorage.getItem(key)
  } catch {
    // localStorage may be unavailable (private browsing, security restrictions, etc.)
    return null
  }
}

/**
 * Safely set an item in localStorage
 * @param key - The storage key
 * @param value - The value to store
 * @returns true if successful, false if failed
 */
export function safeSetItem(key: string, value: string): boolean {
  try {
    localStorage.setItem(key, value)
    return true
  } catch {
    // localStorage may be unavailable or quota exceeded
    return false
  }
}

/**
 * Safely remove an item from localStorage
 * @param key - The storage key
 * @returns true if successful, false if failed
 */
export function safeRemoveItem(key: string): boolean {
  try {
    localStorage.removeItem(key)
    return true
  } catch {
    return false
  }
}

/**
 * Safely get and parse a JSON value from localStorage
 * @param key - The storage key
 * @returns The parsed value or null if not found/invalid/error
 */
export function safeGetJSON<T>(key: string): T | null {
  try {
    const item = localStorage.getItem(key)
    if (item === null) return null
    return JSON.parse(item) as T
  } catch {
    // localStorage unavailable or invalid JSON
    return null
  }
}

/**
 * Safely stringify and set a JSON value in localStorage
 * @param key - The storage key
 * @param value - The value to stringify and store
 * @returns true if successful, false if failed
 */
export function safeSetJSON<T>(key: string, value: T): boolean {
  try {
    localStorage.setItem(key, JSON.stringify(value))
    return true
  } catch {
    // localStorage unavailable, quota exceeded, or value can't be stringified
    return false
  }
}

/**
 * Check if localStorage is available
 * @returns true if localStorage is available and writable
 */
export function isStorageAvailable(): boolean {
  try {
    const testKey = '__storage_test__'
    localStorage.setItem(testKey, testKey)
    localStorage.removeItem(testKey)
    return true
  } catch {
    return false
  }
}

/**
 * Safe localStorage object with methods matching the Storage interface
 */
export const safeLocalStorage = {
  getItem: safeGetItem,
  setItem: safeSetItem,
  removeItem: safeRemoveItem,
  getJSON: safeGetJSON,
  setJSON: safeSetJSON,
  isAvailable: isStorageAvailable,
} as const

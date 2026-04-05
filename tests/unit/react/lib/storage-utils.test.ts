import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import {
  isStorageAvailable,
  safeGetItem,
  safeGetJSON,
  safeLocalStorage,
  safeRemoveItem,
  safeSetItem,
  safeSetJSON,
} from '@lib/storage-utils'

describe('storage-utils', () => {
  // Mock localStorage for testing
  const mockLocalStorage = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
    key: vi.fn(),
    length: 0,
  }

  const originalLocalStorage = global.localStorage

  beforeEach(() => {
    Object.defineProperty(global, 'localStorage', {
      value: mockLocalStorage,
      writable: true,
    })
    vi.clearAllMocks()
  })

  afterEach(() => {
    Object.defineProperty(global, 'localStorage', {
      value: originalLocalStorage,
      writable: true,
    })
  })

  describe('safeGetItem', () => {
    it('should return value when key exists', () => {
      mockLocalStorage.getItem.mockReturnValue('test-value')

      const result = safeGetItem('test-key')

      expect(result).toBe('test-value')
      expect(mockLocalStorage.getItem).toHaveBeenCalledWith('test-key')
    })

    it('should return null when key does not exist', () => {
      mockLocalStorage.getItem.mockReturnValue(null)

      const result = safeGetItem('non-existent')

      expect(result).toBeNull()
    })

    it('should return null when localStorage throws', () => {
      mockLocalStorage.getItem.mockImplementation(() => {
        throw new Error('Storage unavailable')
      })

      const result = safeGetItem('test-key')

      expect(result).toBeNull()
    })
  })

  describe('safeSetItem', () => {
    it('should return true when set succeeds', () => {
      mockLocalStorage.setItem.mockImplementation(() => {})

      const result = safeSetItem('test-key', 'test-value')

      expect(result).toBe(true)
      expect(mockLocalStorage.setItem).toHaveBeenCalledWith('test-key', 'test-value')
    })

    it('should return false when localStorage throws', () => {
      mockLocalStorage.setItem.mockImplementation(() => {
        throw new Error('Quota exceeded')
      })

      const result = safeSetItem('test-key', 'test-value')

      expect(result).toBe(false)
    })
  })

  describe('safeRemoveItem', () => {
    it('should return true when remove succeeds', () => {
      mockLocalStorage.removeItem.mockImplementation(() => {})

      const result = safeRemoveItem('test-key')

      expect(result).toBe(true)
      expect(mockLocalStorage.removeItem).toHaveBeenCalledWith('test-key')
    })

    it('should return false when localStorage throws', () => {
      mockLocalStorage.removeItem.mockImplementation(() => {
        throw new Error('Storage unavailable')
      })

      const result = safeRemoveItem('test-key')

      expect(result).toBe(false)
    })
  })

  describe('safeGetJSON', () => {
    it('should return parsed JSON when value exists', () => {
      const testData = { foo: 'bar', count: 42 }
      mockLocalStorage.getItem.mockReturnValue(JSON.stringify(testData))

      const result = safeGetJSON<{ foo: string; count: number }>('test-key')

      expect(result).toEqual(testData)
    })

    it('should return null when key does not exist', () => {
      mockLocalStorage.getItem.mockReturnValue(null)

      const result = safeGetJSON('non-existent')

      expect(result).toBeNull()
    })

    it('should return null when JSON is invalid', () => {
      mockLocalStorage.getItem.mockReturnValue('not valid json')

      const result = safeGetJSON('test-key')

      expect(result).toBeNull()
    })

    it('should return null when localStorage throws', () => {
      mockLocalStorage.getItem.mockImplementation(() => {
        throw new Error('Storage unavailable')
      })

      const result = safeGetJSON('test-key')

      expect(result).toBeNull()
    })
  })

  describe('safeSetJSON', () => {
    it('should return true when set succeeds', () => {
      mockLocalStorage.setItem.mockImplementation(() => {})
      const testData = { foo: 'bar', count: 42 }

      const result = safeSetJSON('test-key', testData)

      expect(result).toBe(true)
      expect(mockLocalStorage.setItem).toHaveBeenCalledWith('test-key', JSON.stringify(testData))
    })

    it('should return false when localStorage throws', () => {
      mockLocalStorage.setItem.mockImplementation(() => {
        throw new Error('Quota exceeded')
      })

      const result = safeSetJSON('test-key', { foo: 'bar' })

      expect(result).toBe(false)
    })

    it('should handle arrays', () => {
      mockLocalStorage.setItem.mockImplementation(() => {})
      const testArray = [1, 2, 3]

      const result = safeSetJSON('test-key', testArray)

      expect(result).toBe(true)
      expect(mockLocalStorage.setItem).toHaveBeenCalledWith('test-key', JSON.stringify(testArray))
    })
  })

  describe('isStorageAvailable', () => {
    it('should return true when localStorage is available', () => {
      mockLocalStorage.setItem.mockImplementation(() => {})
      mockLocalStorage.removeItem.mockImplementation(() => {})

      const result = isStorageAvailable()

      expect(result).toBe(true)
    })

    it('should return false when setItem throws', () => {
      mockLocalStorage.setItem.mockImplementation(() => {
        throw new Error('Storage unavailable')
      })

      const result = isStorageAvailable()

      expect(result).toBe(false)
    })

    it('should return false when removeItem throws', () => {
      mockLocalStorage.setItem.mockImplementation(() => {})
      mockLocalStorage.removeItem.mockImplementation(() => {
        throw new Error('Storage unavailable')
      })

      const result = isStorageAvailable()

      expect(result).toBe(false)
    })
  })

  describe('safeLocalStorage object', () => {
    it('should expose all methods', () => {
      expect(typeof safeLocalStorage.getItem).toBe('function')
      expect(typeof safeLocalStorage.setItem).toBe('function')
      expect(typeof safeLocalStorage.removeItem).toBe('function')
      expect(typeof safeLocalStorage.getJSON).toBe('function')
      expect(typeof safeLocalStorage.setJSON).toBe('function')
      expect(typeof safeLocalStorage.isAvailable).toBe('function')
    })

    it('should use the same implementations', () => {
      expect(safeLocalStorage.getItem).toBe(safeGetItem)
      expect(safeLocalStorage.setItem).toBe(safeSetItem)
      expect(safeLocalStorage.removeItem).toBe(safeRemoveItem)
      expect(safeLocalStorage.getJSON).toBe(safeGetJSON)
      expect(safeLocalStorage.setJSON).toBe(safeSetJSON)
      expect(safeLocalStorage.isAvailable).toBe(isStorageAvailable)
    })
  })
})

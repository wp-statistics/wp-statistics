import { act, renderHook, waitFor } from '@testing-library/react'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { useSettings, useSetting } from '@hooks/use-settings'

// Mock the settings service
vi.mock('@/services/settings', () => ({
  getTabSettings: vi.fn(),
  saveTabSettings: vi.fn(),
}))

import { getTabSettings, saveTabSettings } from '@/services/settings'

const mockGetTabSettings = vi.mocked(getTabSettings)
const mockSaveTabSettings = vi.mocked(saveTabSettings)

describe('useSettings', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('Initialization', () => {
    it('should load settings on mount', async () => {
      const mockSettings = { option1: 'value1', option2: true }
      mockGetTabSettings.mockResolvedValue(mockSettings)

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      // Initially loading
      expect(result.current.isLoading).toBe(true)

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      expect(mockGetTabSettings).toHaveBeenCalledWith('general')
      expect(result.current.settings).toEqual(mockSettings)
      expect(result.current.error).toBeNull()
    })

    it('should handle load error gracefully', async () => {
      mockGetTabSettings.mockRejectedValue(new Error('Failed to fetch'))

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      expect(result.current.error).toBe('Failed to fetch')
      expect(result.current.settings).toEqual({})
    })

    it('should handle non-Error rejection', async () => {
      mockGetTabSettings.mockRejectedValue('Unknown error')

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      expect(result.current.error).toBe('Failed to load settings')
    })

    it('should reload settings when tab changes', async () => {
      const generalSettings = { mode: 'standard' }
      const advancedSettings = { debug: true }

      mockGetTabSettings
        .mockResolvedValueOnce(generalSettings)
        .mockResolvedValueOnce(advancedSettings)

      const { result, rerender } = renderHook(({ tab }) => useSettings({ tab }), {
        initialProps: { tab: 'general' as const },
      })

      await waitFor(() => {
        expect(result.current.settings).toEqual(generalSettings)
      })

      rerender({ tab: 'advanced' as const })

      await waitFor(() => {
        expect(result.current.settings).toEqual(advancedSettings)
      })

      expect(mockGetTabSettings).toHaveBeenCalledTimes(2)
    })
  })

  describe('getValue', () => {
    it('should return setting value when it exists', async () => {
      mockGetTabSettings.mockResolvedValue({ username: 'admin', enabled: true })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      expect(result.current.getValue('username')).toBe('admin')
      expect(result.current.getValue('enabled')).toBe(true)
    })

    it('should return default value when setting is undefined', async () => {
      mockGetTabSettings.mockResolvedValue({})

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      expect(result.current.getValue('nonexistent', 'default')).toBe('default')
    })

    it('should return default value when setting is null', async () => {
      mockGetTabSettings.mockResolvedValue({ nullValue: null })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      expect(result.current.getValue('nullValue', 'fallback')).toBe('fallback')
    })

    it('should return typed value with generic', async () => {
      mockGetTabSettings.mockResolvedValue({ count: 42 })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      const count = result.current.getValue<number>('count', 0)
      expect(count).toBe(42)
    })
  })

  describe('setValue', () => {
    it('should update local state without saving', async () => {
      mockGetTabSettings.mockResolvedValue({ name: 'original' })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      act(() => {
        result.current.setValue('name', 'updated')
      })

      expect(result.current.settings).toEqual({ name: 'updated' })
      expect(mockSaveTabSettings).not.toHaveBeenCalled()
    })

    it('should add new setting to state', async () => {
      mockGetTabSettings.mockResolvedValue({ existing: 'value' })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      act(() => {
        result.current.setValue('newKey', 'newValue')
      })

      expect(result.current.settings).toEqual({
        existing: 'value',
        newKey: 'newValue',
      })
    })
  })

  describe('save', () => {
    it('should save settings successfully', async () => {
      mockGetTabSettings.mockResolvedValue({ key: 'value' })
      mockSaveTabSettings.mockResolvedValue({ success: true })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      let saveResult: boolean
      await act(async () => {
        saveResult = await result.current.save()
      })

      expect(saveResult!).toBe(true)
      expect(mockSaveTabSettings).toHaveBeenCalledWith('general', { key: 'value' })
      expect(result.current.error).toBeNull()
    })

    it('should handle save failure with message', async () => {
      mockGetTabSettings.mockResolvedValue({})
      mockSaveTabSettings.mockResolvedValue({ success: false, message: 'Permission denied' })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      let saveResult: boolean
      await act(async () => {
        saveResult = await result.current.save()
      })

      expect(saveResult!).toBe(false)
      expect(result.current.error).toBe('Permission denied')
    })

    it('should handle save failure without message', async () => {
      mockGetTabSettings.mockResolvedValue({})
      mockSaveTabSettings.mockResolvedValue({ success: false })

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      let saveResult: boolean
      await act(async () => {
        saveResult = await result.current.save()
      })

      expect(saveResult!).toBe(false)
      expect(result.current.error).toBe('Failed to save settings')
    })

    it('should handle save exception', async () => {
      mockGetTabSettings.mockResolvedValue({})
      mockSaveTabSettings.mockRejectedValue(new Error('Network error'))

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      let saveResult: boolean
      await act(async () => {
        saveResult = await result.current.save()
      })

      expect(saveResult!).toBe(false)
      expect(result.current.error).toBe('Network error')
    })

    it('should set isSaving during save operation', async () => {
      mockGetTabSettings.mockResolvedValue({})

      let resolvePromise: (value: { success: boolean }) => void
      mockSaveTabSettings.mockImplementation(
        () => new Promise((resolve) => {
          resolvePromise = resolve
        })
      )

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.isLoading).toBe(false)
      })

      // Start save
      let savePromise: Promise<boolean>
      act(() => {
        savePromise = result.current.save()
      })

      // Should be saving
      expect(result.current.isSaving).toBe(true)

      // Complete save
      await act(async () => {
        resolvePromise!({ success: true })
        await savePromise
      })

      expect(result.current.isSaving).toBe(false)
    })
  })

  describe('reload', () => {
    it('should reload settings from server', async () => {
      const initialSettings = { version: '1.0' }
      const updatedSettings = { version: '2.0' }

      mockGetTabSettings
        .mockResolvedValueOnce(initialSettings)
        .mockResolvedValueOnce(updatedSettings)

      const { result } = renderHook(() => useSettings({ tab: 'general' }))

      await waitFor(() => {
        expect(result.current.settings).toEqual(initialSettings)
      })

      await act(async () => {
        await result.current.reload()
      })

      expect(result.current.settings).toEqual(updatedSettings)
      expect(mockGetTabSettings).toHaveBeenCalledTimes(2)
    })
  })
})

describe('useSetting', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('should return value from settings', async () => {
    mockGetTabSettings.mockResolvedValue({ enabled: true })

    const { result } = renderHook(() => {
      const settings = useSettings({ tab: 'general' })
      const [enabled, setEnabled] = useSetting(settings, 'enabled', false)
      return { settings, enabled, setEnabled }
    })

    await waitFor(() => {
      expect(result.current.settings.isLoading).toBe(false)
    })

    expect(result.current.enabled).toBe(true)
  })

  it('should return default value when setting not present', async () => {
    mockGetTabSettings.mockResolvedValue({})

    const { result } = renderHook(() => {
      const settings = useSettings({ tab: 'general' })
      const [theme, setTheme] = useSetting(settings, 'theme', 'light')
      return { settings, theme, setTheme }
    })

    await waitFor(() => {
      expect(result.current.settings.isLoading).toBe(false)
    })

    expect(result.current.theme).toBe('light')
  })

  it('should update setting via setter', async () => {
    mockGetTabSettings.mockResolvedValue({ count: 0 })

    const { result } = renderHook(() => {
      const settings = useSettings({ tab: 'general' })
      const [count, setCount] = useSetting(settings, 'count', 0)
      return { settings, count, setCount }
    })

    await waitFor(() => {
      expect(result.current.settings.isLoading).toBe(false)
    })

    act(() => {
      result.current.setCount(5)
    })

    expect(result.current.count).toBe(5)
    expect(result.current.settings.settings.count).toBe(5)
  })
})

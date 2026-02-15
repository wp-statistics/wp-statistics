import { describe, expect, it } from 'vitest'

import { evaluateVisibleWhen } from '@/components/settings-ui/visible-when'
import type { UseSettingsReturn } from '@/hooks/use-settings'

/**
 * Create a minimal mock of UseSettingsReturn with only getValue.
 */
function mockSettings(values: Record<string, unknown>): UseSettingsReturn {
  return {
    getValue: (key: string) => values[key],
  } as UseSettingsReturn
}

describe('evaluateVisibleWhen', () => {
  it('returns true when no conditions', () => {
    const settings = mockSettings({})
    expect(evaluateVisibleWhen(undefined, settings)).toBe(true)
  })

  it('equality check passes when value matches', () => {
    const settings = mockSettings({ theme: 'dark' })
    expect(evaluateVisibleWhen({ theme: 'dark' }, settings)).toBe(true)
  })

  it('equality check fails when value differs', () => {
    const settings = mockSettings({ theme: 'light' })
    expect(evaluateVisibleWhen({ theme: 'dark' }, settings)).toBe(false)
  })

  it('truthy check passes for truthy value', () => {
    const settings = mockSettings({ enabled: 'yes' })
    expect(evaluateVisibleWhen({ enabled: true }, settings)).toBe(true)
  })

  it('truthy check fails for falsy value', () => {
    const settings = mockSettings({ enabled: '' })
    expect(evaluateVisibleWhen({ enabled: true }, settings)).toBe(false)
  })

  it('truthy check fails for undefined value', () => {
    const settings = mockSettings({})
    expect(evaluateVisibleWhen({ enabled: true }, settings)).toBe(false)
  })

  it('not-equals operator passes', () => {
    const settings = mockSettings({ consent: 'wp_consent_api' })
    expect(evaluateVisibleWhen({ consent: ['!=', 'none'] }, settings)).toBe(true)
  })

  it('not-equals operator fails when equal', () => {
    const settings = mockSettings({ consent: 'none' })
    expect(evaluateVisibleWhen({ consent: ['!=', 'none'] }, settings)).toBe(false)
  })

  it('in-array operator passes when value in list', () => {
    const settings = mockSettings({ role: 'admin' })
    expect(evaluateVisibleWhen({ role: ['in', ['admin', 'editor']] }, settings)).toBe(true)
  })

  it('in-array operator fails when value not in list', () => {
    const settings = mockSettings({ role: 'subscriber' })
    expect(evaluateVisibleWhen({ role: ['in', ['admin', 'editor']] }, settings)).toBe(false)
  })

  it('multiple conditions all must pass (AND logic)', () => {
    const settings = mockSettings({ enabled: 'yes', mode: 'advanced' })

    // Both pass
    expect(evaluateVisibleWhen({ enabled: true, mode: 'advanced' }, settings)).toBe(true)

    // One fails
    expect(evaluateVisibleWhen({ enabled: true, mode: 'basic' }, settings)).toBe(false)
  })
})

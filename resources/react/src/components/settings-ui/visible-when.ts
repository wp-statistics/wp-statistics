import type { UseSettingsReturn } from '@/hooks/use-settings'

/**
 * Evaluate a `visible_when` condition against the current settings state.
 *
 * Supported operators:
 *   - Equals:      `{ field: 'value' }`
 *   - Truthy:      `{ field: true }`
 *   - Not equals:  `{ field: ['!=', 'value'] }`
 *   - In array:    `{ field: ['in', ['a', 'b']] }`
 */
export function evaluateVisibleWhen(
  conditions: Record<string, unknown> | undefined,
  settings: UseSettingsReturn
): boolean {
  if (!conditions) return true

  return Object.entries(conditions).every(([key, condition]) => {
    const current = settings.getValue(key)

    // Array-based operators: ['!=', value] or ['in', [...]]
    if (Array.isArray(condition) && condition.length === 2) {
      const [op, operand] = condition
      if (op === '!=') return current !== operand
      if (op === 'in') return Array.isArray(operand) && operand.includes(current)
      return current === condition
    }

    // Truthy check
    if (condition === true) return !!current

    // Equality check
    return current === condition
  })
}

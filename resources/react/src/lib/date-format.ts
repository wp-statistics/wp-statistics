/**
 * Date format parsing utilities
 * Parses PHP/WordPress date format strings to determine field order
 */

export type DateFieldOrder = 'MDY' | 'DMY' | 'YMD'

export interface ParsedDateFormat {
  order: DateFieldOrder
}

/**
 * Parse a PHP date format string and extract field order.
 *
 * Common WordPress formats:
 * - 'Y-m-d' -> YMD
 * - 'd/m/Y' -> DMY
 * - 'm/d/Y' -> MDY
 * - 'd.m.Y' -> DMY
 * - 'F j, Y' -> MDY (month name formats)
 * - 'j F Y' -> DMY (day first with month name)
 *
 * PHP date format characters:
 * - Day: d (01-31), j (1-31), D (Mon), l (Monday)
 * - Month: m (01-12), n (1-12), F (January), M (Jan)
 * - Year: Y (2024), y (24)
 */
export function parseDateFormat(phpFormat: string): ParsedDateFormat {
  let order: DateFieldOrder = 'YMD'

  // Find positions of day, month, and year patterns
  const dayPattern = /[djDl]/
  const monthPattern = /[mnFM]/
  const yearPattern = /[Yy]/

  const dayMatch = phpFormat.match(dayPattern)
  const monthMatch = phpFormat.match(monthPattern)
  const yearMatch = phpFormat.match(yearPattern)

  // Get indices (use -1 if not found)
  const dayIndex = dayMatch ? phpFormat.indexOf(dayMatch[0]) : -1
  const monthIndex = monthMatch ? phpFormat.indexOf(monthMatch[0]) : -1
  const yearIndex = yearMatch ? phpFormat.indexOf(yearMatch[0]) : -1

  // Only determine order if all three components are present
  if (dayIndex >= 0 && monthIndex >= 0 && yearIndex >= 0) {
    if (yearIndex < monthIndex && monthIndex < dayIndex) {
      order = 'YMD'
    } else if (dayIndex < monthIndex && monthIndex < yearIndex) {
      order = 'DMY'
    } else if (monthIndex < dayIndex && dayIndex < yearIndex) {
      order = 'MDY'
    } else if (yearIndex < dayIndex && dayIndex < monthIndex) {
      // Y-d-m format (rare, treat as YMD)
      order = 'YMD'
    }
  }

  return { order }
}

/**
 * Get field configuration based on date format order
 */
export interface DateFieldConfig {
  field: 'day' | 'month' | 'year'
  maxLength: number
  placeholder: string
  width: string
}

export function getFieldConfigs(order: DateFieldOrder): DateFieldConfig[] {
  const dayConfig: DateFieldConfig = { field: 'day', maxLength: 2, placeholder: 'D', width: 'w-8' }
  const monthConfig: DateFieldConfig = { field: 'month', maxLength: 2, placeholder: 'M', width: 'w-7' }
  const yearConfig: DateFieldConfig = { field: 'year', maxLength: 4, placeholder: 'YYYY', width: 'w-12' }

  switch (order) {
    case 'DMY':
      return [dayConfig, monthConfig, yearConfig]
    case 'MDY':
      return [monthConfig, dayConfig, yearConfig]
    case 'YMD':
    default:
      return [yearConfig, monthConfig, dayConfig]
  }
}

/**
 * Get next and previous field names for keyboard navigation
 */
export function getNavigationFields(
  currentField: 'day' | 'month' | 'year',
  order: DateFieldOrder
): { prev: 'day' | 'month' | 'year' | null; next: 'day' | 'month' | 'year' | null } {
  const fields = getFieldConfigs(order).map((c) => c.field)
  const currentIndex = fields.indexOf(currentField)

  return {
    prev: currentIndex > 0 ? fields[currentIndex - 1] : null,
    next: currentIndex < fields.length - 1 ? fields[currentIndex + 1] : null,
  }
}

import { describe, expect, it } from 'vitest'

import {
  parseDateFormat,
  getFieldConfigs,
  getNavigationFields,
  type DateFieldOrder,
} from '@lib/date-format'

describe('parseDateFormat', () => {
  describe('YMD Format (ISO)', () => {
    it('should parse Y-m-d as YMD', () => {
      const result = parseDateFormat('Y-m-d')
      expect(result.order).toBe('YMD')
    })

    it('should parse Y/m/d as YMD', () => {
      const result = parseDateFormat('Y/m/d')
      expect(result.order).toBe('YMD')
    })

    it('should parse Y.m.d as YMD', () => {
      const result = parseDateFormat('Y.m.d')
      expect(result.order).toBe('YMD')
    })
  })

  describe('DMY Format (European)', () => {
    it('should parse d/m/Y as DMY', () => {
      const result = parseDateFormat('d/m/Y')
      expect(result.order).toBe('DMY')
    })

    it('should parse d-m-Y as DMY', () => {
      const result = parseDateFormat('d-m-Y')
      expect(result.order).toBe('DMY')
    })

    it('should parse d.m.Y as DMY', () => {
      const result = parseDateFormat('d.m.Y')
      expect(result.order).toBe('DMY')
    })

    it('should parse j F Y as DMY', () => {
      const result = parseDateFormat('j F Y')
      expect(result.order).toBe('DMY')
    })

    it('should parse l, j F Y as DMY', () => {
      const result = parseDateFormat('l, j F Y')
      expect(result.order).toBe('DMY')
    })
  })

  describe('MDY Format (US)', () => {
    it('should parse m/d/Y as MDY', () => {
      const result = parseDateFormat('m/d/Y')
      expect(result.order).toBe('MDY')
    })

    it('should parse m-d-Y as MDY', () => {
      const result = parseDateFormat('m-d-Y')
      expect(result.order).toBe('MDY')
    })

    it('should parse F j, Y as MDY', () => {
      const result = parseDateFormat('F j, Y')
      expect(result.order).toBe('MDY')
    })

    it('should parse M d, Y as MDY', () => {
      const result = parseDateFormat('M d, Y')
      expect(result.order).toBe('MDY')
    })
  })

  describe('Edge Cases', () => {
    it('should default to YMD for empty string', () => {
      const result = parseDateFormat('')
      expect(result.order).toBe('YMD')
    })

    it('should default to YMD for format without all components', () => {
      const result = parseDateFormat('Y-m')
      expect(result.order).toBe('YMD')
    })

    it('should handle lowercase year format', () => {
      const result = parseDateFormat('y-m-d')
      expect(result.order).toBe('YMD')
    })

    it('should handle day formats with different characters', () => {
      // j = day without leading zero
      expect(parseDateFormat('j/m/Y').order).toBe('DMY')
      // d = day with leading zero
      expect(parseDateFormat('d/m/Y').order).toBe('DMY')
    })

    it('should handle month formats with different characters', () => {
      // n = month without leading zero
      expect(parseDateFormat('d/n/Y').order).toBe('DMY')
      // F = full month name
      expect(parseDateFormat('d F Y').order).toBe('DMY')
      // M = abbreviated month name
      expect(parseDateFormat('d M Y').order).toBe('DMY')
    })
  })

  describe('Rare Formats', () => {
    it('should treat Y-d-m (rare) as YMD', () => {
      const result = parseDateFormat('Y-d-m')
      expect(result.order).toBe('YMD')
    })
  })
})

describe('getFieldConfigs', () => {
  describe('YMD Order', () => {
    it('should return year, month, day order', () => {
      const configs = getFieldConfigs('YMD')

      expect(configs).toHaveLength(3)
      expect(configs[0].field).toBe('year')
      expect(configs[1].field).toBe('month')
      expect(configs[2].field).toBe('day')
    })

    it('should have correct properties for year field', () => {
      const configs = getFieldConfigs('YMD')
      const yearConfig = configs[0]

      expect(yearConfig.maxLength).toBe(4)
      expect(yearConfig.placeholder).toBe('YYYY')
      expect(yearConfig.width).toBe('w-12')
    })
  })

  describe('DMY Order', () => {
    it('should return day, month, year order', () => {
      const configs = getFieldConfigs('DMY')

      expect(configs).toHaveLength(3)
      expect(configs[0].field).toBe('day')
      expect(configs[1].field).toBe('month')
      expect(configs[2].field).toBe('year')
    })

    it('should have correct properties for day field', () => {
      const configs = getFieldConfigs('DMY')
      const dayConfig = configs[0]

      expect(dayConfig.maxLength).toBe(2)
      expect(dayConfig.placeholder).toBe('D')
      expect(dayConfig.width).toBe('w-8')
    })
  })

  describe('MDY Order', () => {
    it('should return month, day, year order', () => {
      const configs = getFieldConfigs('MDY')

      expect(configs).toHaveLength(3)
      expect(configs[0].field).toBe('month')
      expect(configs[1].field).toBe('day')
      expect(configs[2].field).toBe('year')
    })

    it('should have correct properties for month field', () => {
      const configs = getFieldConfigs('MDY')
      const monthConfig = configs[0]

      expect(monthConfig.maxLength).toBe(2)
      expect(monthConfig.placeholder).toBe('M')
      expect(monthConfig.width).toBe('w-7')
    })
  })

  describe('Field Properties', () => {
    it('should return consistent day config across all orders', () => {
      const orders: DateFieldOrder[] = ['YMD', 'DMY', 'MDY']

      for (const order of orders) {
        const configs = getFieldConfigs(order)
        const dayConfig = configs.find((c) => c.field === 'day')!

        expect(dayConfig.maxLength).toBe(2)
        expect(dayConfig.placeholder).toBe('D')
        expect(dayConfig.width).toBe('w-8')
      }
    })

    it('should return consistent month config across all orders', () => {
      const orders: DateFieldOrder[] = ['YMD', 'DMY', 'MDY']

      for (const order of orders) {
        const configs = getFieldConfigs(order)
        const monthConfig = configs.find((c) => c.field === 'month')!

        expect(monthConfig.maxLength).toBe(2)
        expect(monthConfig.placeholder).toBe('M')
        expect(monthConfig.width).toBe('w-7')
      }
    })

    it('should return consistent year config across all orders', () => {
      const orders: DateFieldOrder[] = ['YMD', 'DMY', 'MDY']

      for (const order of orders) {
        const configs = getFieldConfigs(order)
        const yearConfig = configs.find((c) => c.field === 'year')!

        expect(yearConfig.maxLength).toBe(4)
        expect(yearConfig.placeholder).toBe('YYYY')
        expect(yearConfig.width).toBe('w-12')
      }
    })
  })
})

describe('getNavigationFields', () => {
  describe('YMD Order Navigation', () => {
    it('should return correct navigation for year field (first)', () => {
      const nav = getNavigationFields('year', 'YMD')

      expect(nav.prev).toBeNull()
      expect(nav.next).toBe('month')
    })

    it('should return correct navigation for month field (middle)', () => {
      const nav = getNavigationFields('month', 'YMD')

      expect(nav.prev).toBe('year')
      expect(nav.next).toBe('day')
    })

    it('should return correct navigation for day field (last)', () => {
      const nav = getNavigationFields('day', 'YMD')

      expect(nav.prev).toBe('month')
      expect(nav.next).toBeNull()
    })
  })

  describe('DMY Order Navigation', () => {
    it('should return correct navigation for day field (first)', () => {
      const nav = getNavigationFields('day', 'DMY')

      expect(nav.prev).toBeNull()
      expect(nav.next).toBe('month')
    })

    it('should return correct navigation for month field (middle)', () => {
      const nav = getNavigationFields('month', 'DMY')

      expect(nav.prev).toBe('day')
      expect(nav.next).toBe('year')
    })

    it('should return correct navigation for year field (last)', () => {
      const nav = getNavigationFields('year', 'DMY')

      expect(nav.prev).toBe('month')
      expect(nav.next).toBeNull()
    })
  })

  describe('MDY Order Navigation', () => {
    it('should return correct navigation for month field (first)', () => {
      const nav = getNavigationFields('month', 'MDY')

      expect(nav.prev).toBeNull()
      expect(nav.next).toBe('day')
    })

    it('should return correct navigation for day field (middle)', () => {
      const nav = getNavigationFields('day', 'MDY')

      expect(nav.prev).toBe('month')
      expect(nav.next).toBe('year')
    })

    it('should return correct navigation for year field (last)', () => {
      const nav = getNavigationFields('year', 'MDY')

      expect(nav.prev).toBe('day')
      expect(nav.next).toBeNull()
    })
  })

  describe('Full Navigation Flow', () => {
    it('should allow complete forward navigation in YMD', () => {
      const fields: Array<'day' | 'month' | 'year'> = []
      let current: 'day' | 'month' | 'year' | null = 'year'

      while (current !== null) {
        fields.push(current)
        current = getNavigationFields(current, 'YMD').next
      }

      expect(fields).toEqual(['year', 'month', 'day'])
    })

    it('should allow complete backward navigation in DMY', () => {
      const fields: Array<'day' | 'month' | 'year'> = []
      let current: 'day' | 'month' | 'year' | null = 'year'

      while (current !== null) {
        fields.push(current)
        current = getNavigationFields(current, 'DMY').prev
      }

      expect(fields).toEqual(['year', 'month', 'day'])
    })
  })
})

import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'

import { SettingsSelectField } from '@/components/settings-ui/settings-select-field'

describe('SettingsSelectField', () => {
  it('renders the selected option when value is numeric', () => {
    render(
      <SettingsSelectField
        id="delivery-hour"
        label="Delivery Time"
        value={8}
        onValueChange={() => {}}
        options={[
          { value: '7', label: '7:00 AM' },
          { value: '8', label: '8:00 AM' },
        ]}
      />
    )

    expect(screen.getByRole('combobox')).toHaveTextContent('8:00 AM')
  })
})

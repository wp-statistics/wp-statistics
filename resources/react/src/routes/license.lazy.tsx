import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { Suspense, useEffect } from 'react'

import { getSettingsComponent } from '@/registry/settings-registry'

export const Route = createLazyFileRoute('/license')({
  component: LicenseRoute,
})

/**
 * Thin shell for the /license route. The actual page lives in the premium
 * plugin (pro/modules/license/license-page.tsx) and is registered via the
 * settings registry. When premium isn't installed, redirect to /premium so
 * the user sees the upgrade page instead of an empty/broken activation form
 * that would call AJAX endpoints nobody answers.
 */
function LicenseRoute() {
  const navigate = useNavigate()
  const LicensePage = getSettingsComponent('LicensePage')

  useEffect(() => {
    if (!LicensePage) {
      void navigate({ to: '/premium', replace: true })
    }
  }, [LicensePage, navigate])

  if (!LicensePage) {
    return null
  }

  return (
    <Suspense fallback={null}>
      <LicensePage />
    </Suspense>
  )
}

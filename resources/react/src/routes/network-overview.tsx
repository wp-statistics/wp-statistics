import { createFileRoute } from '@tanstack/react-router'

import { searchValidators } from '@/lib/route-validation'

export const Route = createFileRoute('/network-overview')({
  validateSearch: searchValidators.filtersOnly,
})

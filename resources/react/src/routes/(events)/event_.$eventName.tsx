import { createFileRoute } from '@tanstack/react-router'

import { createSearchValidator } from '@/lib/route-validation'

export const Route = createFileRoute('/(events)/event_/$eventName')({
  validateSearch: createSearchValidator({ includePage: false }),
})

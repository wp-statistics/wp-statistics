/**
 * Shared Data Table Column Components
 *
 * Reusable cell components for consistent table columns across all data tables.
 * Import from this file to use the shared components.
 */

// Types
export type { PageData, ReferrerData, VisitorInfoConfig, VisitorInfoData } from './types'
export type { LocationData } from './cells/location-cell'

// Cell Components
export { DurationCell } from './cells/duration-cell'
export { EntryPageCell } from './cells/entry-page-cell'
export { JourneyCell } from './cells/journey-cell'
export { LastVisitCell } from './cells/last-visit-cell'
export { NumericCell } from './cells/numeric-cell'
export { PageCell } from './cells/page-cell'
export { ReferrerCell } from './cells/referrer-cell'
export { StatusCell } from './cells/status-cell'
export { VisitorInfoCell } from './cells/visitor-info-cell'
export { LocationCell } from './cells/location-cell'

// Helpers
export { createVisitorInfoData, createLocationData, type BaseVisitorFields } from './helpers'

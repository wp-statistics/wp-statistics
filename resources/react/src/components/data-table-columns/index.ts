/**
 * Shared Data Table Column Components
 *
 * Reusable cell components for consistent table columns across all data tables.
 * Import from this file to use the shared components.
 */

// Types
export type { LocationData } from './cells/location-cell'
export type { PageData, ReferrerData, VisitorInfoConfig, VisitorInfoData } from './types'

// Cell Components
export { AuthorCell, type AuthorCellProps } from './cells/author-cell'
export { DurationCell } from './cells/duration-cell'
export { EntryPageCell } from './cells/entry-page-cell'
export { JourneyCell } from './cells/journey-cell'
export { LastVisitCell } from './cells/last-visit-cell'
export { LocationCell } from './cells/location-cell'
export { NumericCell } from './cells/numeric-cell'
export { PageCell } from './cells/page-cell'
export { ReferrerCell } from './cells/referrer-cell'
export { StatusCell } from './cells/status-cell'
export { UriCell } from './cells/uri-cell'
export { VisitorInfoCell } from './cells/visitor-info-cell'

// Helpers
export { type BaseVisitorFields,createLocationData, createVisitorInfoData } from './helpers'

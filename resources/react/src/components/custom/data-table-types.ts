// Column priority levels for mobile card display
export type ColumnPriority = 'primary' | 'secondary' | 'hidden'

// Extended column meta for mobile display
export interface DataTableColumnMeta {
  priority?: ColumnPriority // Default: 'secondary'
  mobileLabel?: string // Optional shorter label for mobile
  cardPosition?: 'header' | 'body' | 'footer' // Where to place in card
}

// Extend TanStack Table's ColumnMeta
declare module '@tanstack/react-table' {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface ColumnMeta<TData extends unknown, TValue> extends DataTableColumnMeta {}
}

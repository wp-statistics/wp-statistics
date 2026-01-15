// Column priority levels for mobile card display
export type ColumnPriority = 'primary' | 'secondary' | 'hidden'

// Extended column meta for mobile display
export interface DataTableColumnMeta {
  title?: string // Column display title (single source of truth)
  priority?: ColumnPriority // Default: 'secondary'
  mobileLabel?: string // Optional shorter label for mobile card display
  cardPosition?: 'header' | 'body' | 'footer' // Where to place in card
  isComparable?: boolean // Whether this column supports PP comparison
  showComparison?: boolean // Whether PP comparison is currently enabled for this column
}

// Table-level meta for passing handlers
export interface DataTableMeta {
  toggleComparison?: (columnId: string) => void // Handler to toggle comparison for a column
}

// Extend TanStack Table's ColumnMeta and TableMeta
declare module '@tanstack/react-table' {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface ColumnMeta<TData extends unknown, TValue> extends DataTableColumnMeta {}
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface TableMeta<TData extends unknown> extends DataTableMeta {}
}

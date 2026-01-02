export interface EmailBlock {
  id: string
  type: EmailBlockType
  settings: Record<string, unknown>
}

export type EmailBlockType =
  | 'header'
  | 'metrics'
  | 'top-pages'
  | 'top-referrers'
  | 'top-authors'
  | 'top-categories'
  | 'text'
  | 'divider'
  | 'cta'
  | 'promo'

export interface BlockDefinition {
  type: EmailBlockType
  label: string
  description: string
  icon: React.ReactNode
  defaultSettings: Record<string, unknown>
}

export interface EmailTemplate {
  blocks: EmailBlock[]
  globalSettings: {
    primaryColor: string
    showLogo: boolean
  }
}

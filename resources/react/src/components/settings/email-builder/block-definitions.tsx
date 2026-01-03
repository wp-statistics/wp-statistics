import {
  BarChart3,
  FileText,
  Heading,
  Image,
  Link2,
  ListOrdered,
  Minus,
  Sparkles,
  Type,
  Users,
} from 'lucide-react'

import type { BlockDefinition, EmailBlockType } from './types'

export const blockDefinitions: Record<EmailBlockType, BlockDefinition> = {
  header: {
    type: 'header',
    label: 'Header',
    description: 'Logo, title, and date range',
    icon: <Heading className="h-4 w-4" />,
    defaultSettings: {
      showLogo: true,
      title: 'Statistics Report',
    },
  },
  metrics: {
    type: 'metrics',
    label: 'Key Metrics',
    description: 'Visitors, views, sessions stats',
    icon: <BarChart3 className="h-4 w-4" />,
    defaultSettings: {
      showMetrics: ['visitors', 'views', 'sessions', 'referrals', 'contents'],
      showComparison: true,
    },
  },
  'top-pages': {
    type: 'top-pages',
    label: 'Top Pages',
    description: 'Most visited pages',
    icon: <FileText className="h-4 w-4" />,
    defaultSettings: {
      limit: 5,
    },
  },
  'top-referrers': {
    type: 'top-referrers',
    label: 'Top Referrers',
    description: 'Traffic sources',
    icon: <Link2 className="h-4 w-4" />,
    defaultSettings: {
      limit: 5,
    },
  },
  'top-authors': {
    type: 'top-authors',
    label: 'Top Authors',
    description: 'Authors by content views',
    icon: <Users className="h-4 w-4" />,
    defaultSettings: {
      limit: 5,
    },
  },
  'top-categories': {
    type: 'top-categories',
    label: 'Top Categories',
    description: 'Popular categories',
    icon: <ListOrdered className="h-4 w-4" />,
    defaultSettings: {
      limit: 5,
    },
  },
  text: {
    type: 'text',
    label: 'Custom Text',
    description: 'Add custom message',
    icon: <Type className="h-4 w-4" />,
    defaultSettings: {
      content: '',
    },
  },
  divider: {
    type: 'divider',
    label: 'Divider',
    description: 'Visual separator',
    icon: <Minus className="h-4 w-4" />,
    defaultSettings: {},
  },
  cta: {
    type: 'cta',
    label: 'CTA Button',
    description: 'Link to dashboard',
    icon: <Image className="h-4 w-4" />,
    defaultSettings: {
      text: 'View Full Report',
      alignment: 'center',
    },
  },
  promo: {
    type: 'promo',
    label: 'Promo',
    description: 'Add-on promotion',
    icon: <Sparkles className="h-4 w-4" />,
    defaultSettings: {
      showPromo: true,
    },
  },
}

export const availableBlockTypes: EmailBlockType[] = [
  'header',
  'metrics',
  'top-pages',
  'top-referrers',
  'top-authors',
  'top-categories',
  'text',
  'divider',
  'cta',
  'promo',
]

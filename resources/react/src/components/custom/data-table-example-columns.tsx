import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@components/ui/tooltip'
import type { ColumnDef } from '@tanstack/react-table'
import { Info } from 'lucide-react'

import { DataTableColumnHeader } from './data-table-column-header'

// Example visitor data type based on the screenshot
export type VisitorData = {
  totalViews: number
  visitorInfo: {
    flags: string[]
    id: string
  }
  referrer: {
    domain: string
    traffic: string
  }
  entryPage: string
  exitPage: string
}

export const exampleColumns: ColumnDef<VisitorData>[] = [
  {
    accessorKey: 'totalViews',
    header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} title="Total Views" />,
    cell: ({ row }) => {
      return <div>{row.getValue('totalViews')}</div>
    },
  },
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Information',
    cell: ({ row }) => {
      const visitorInfo = row.getValue('visitorInfo') as VisitorData['visitorInfo']
      return (
        <div className="flex items-center gap-2">
          <div className="flex items-center gap-1">
            {visitorInfo.flags.map((flag, index) => (
              <span key={`${flag}-${index}`} className="inline-flex items-center h-3 text-xs leading-3">
                {flag}
              </span>
            ))}
          </div>
          <span>{visitorInfo.id}</span>
        </div>
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    cell: ({ row }) => {
      const referrer = row.getValue('referrer') as VisitorData['referrer']
      return (
        <div>
          <div>{referrer.domain}</div>
          <div className="text-xs text-neutral-500">{referrer.traffic}</div>
        </div>
      )
    },
  },
  {
    accessorKey: 'entryPage',
    header: 'Entry Page',
    cell: ({ row }) => {
      return (
        <div className="flex items-center gap-1 text-sm">
          {row.getValue('entryPage')}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Info className="h-3 w-3 text-neutral-500 cursor-help shrink-0" />
              </TooltipTrigger>
              <TooltipContent>
                <p>The first page visited in this session</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'exitPage',
    header: 'Exit Page',
    cell: ({ row }) => {
      return (
        <div className="flex items-center gap-1 text-sm">
          {row.getValue('exitPage')}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Info className="h-3 w-3 text-neutral-500 cursor-help shrink-0" />
              </TooltipTrigger>
              <TooltipContent>
                <p>The last page visited in this session</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
]

// Example data based on the screenshot
export const exampleData: VisitorData[] = [
  {
    totalViews: 438,
    visitorInfo: {
      flags: ['🇫🇷', '👤', '🌐'],
      id: 'Mostafa #1',
    },
    referrer: {
      domain: 'php.net',
      traffic: 'REFERRAL TRAFFIC',
    },
    entryPage: 'Home',
    exitPage: 'Home',
  },
  {
    totalViews: 408,
    visitorInfo: {
      flags: ['🇺🇸', '🔥', '📱'],
      id: 'Navid #2',
    },
    referrer: {
      domain: 'veronalabs.com',
      traffic: 'ORGANIC SEARCH',
    },
    entryPage: 'Login',
    exitPage: 'Login',
  },
  {
    totalViews: 655,
    visitorInfo: {
      flags: ['🇳🇱', '🌟', '📧'],
      id: 'HuffnSo',
    },
    referrer: {
      domain: 'google.com',
      traffic: 'ORGANIC SEARCH',
    },
    entryPage: 'Blog Homepage',
    exitPage: 'Blog Homepage',
  },
  {
    totalViews: 638,
    visitorInfo: {
      flags: ['🇷🇺', '💻', '🔍'],
      id: 'Rez #14',
    },
    referrer: {
      domain: 'wp-sms-pro.co',
      traffic: 'REFERRAL TRAFFIC',
    },
    entryPage: 'Services Overview',
    exitPage: 'Services Overview',
  },
  {
    totalViews: 446,
    visitorInfo: {
      flags: ['🇮🇷', '🇪🇸', '📈'],
      id: 'zH3A57ca',
    },
    referrer: {
      domain: 'whatsapp.com',
      traffic: 'SOCIAL MEDIA',
    },
    entryPage: 'Pricing',
    exitPage: 'Pricing',
  },
  {
    totalViews: 522,
    visitorInfo: {
      flags: ['🇵🇹', '📊', '⚡'],
      id: 'H4wvdnypr',
    },
    referrer: {
      domain: '[G]',
      traffic: 'DIRECT TRAFFIC',
    },
    entryPage: 'Portfolio',
    exitPage: 'Portfolio',
  },
  {
    totalViews: 446,
    visitorInfo: {
      flags: ['🇨🇦', '📱', '🔔'],
      id: 'AhsJhjlak',
    },
    referrer: {
      domain: 'bing.com',
      traffic: 'ORGANIC SEARCH',
    },
    entryPage: 'About Us',
    exitPage: 'About Us',
  },
  {
    totalViews: 522,
    visitorInfo: {
      flags: ['🇬🇧', '🌐', '💡'],
      id: 'Agq4c88nq',
    },
    referrer: {
      domain: 'bing.com',
      traffic: 'ORGANIC SEARCH',
    },
    entryPage: 'Privacy Policy',
    exitPage: 'Privacy Policy',
  },
  {
    totalViews: 446,
    visitorInfo: {
      flags: ['🇸🇦', '🔥', '📊'],
      id: 'HJs25gjbv',
    },
    referrer: {
      domain: 'youtube.com',
      traffic: 'SOCIAL MEDIA',
    },
    entryPage: 'Search Results',
    exitPage: 'Search Results',
  },
  {
    totalViews: 522,
    visitorInfo: {
      flags: ['🇮🇳', '🔊', '🌟'],
      id: '#fwef74dv',
    },
    referrer: {
      domain: 'google.com',
      traffic: 'ORGANIC SEARCH',
    },
    entryPage: 'Feature: Category Report',
    exitPage: 'Feature: Category Report',
  },
]

import { __, sprintf } from '@wordpress/i18n'
import { CheckCircle2, Loader2, RefreshCw, XCircle } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { RadioCardGroup, type RadioCardOption } from '@/components/ui/radio-card-group'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

import type { ImportStatus } from './import-export-page'

interface V14MigrationWizardProps {
  importStatus: ImportStatus
  setImportStatus: React.Dispatch<React.SetStateAction<ImportStatus>>
}

type MigrationMode = 'all' | 'selective' | 'fresh'

interface V14DataStats {
  visitors: number
  visits: number
  pages: number
  useronline: number
  search: number
  exclusions: number
  total: number
  isLoading: boolean
  hasV14Data: boolean
}

function getTimePeriodOptions() {
  return [
    { value: 'all', label: __('All Time', 'wp-statistics') },
    { value: '30', label: __('Last 30 Days', 'wp-statistics') },
    { value: '90', label: __('Last 90 Days', 'wp-statistics') },
    { value: '180', label: __('Last 6 Months', 'wp-statistics') },
    { value: '365', label: __('Last Year', 'wp-statistics') },
    { value: '730', label: __('Last 2 Years', 'wp-statistics') },
  ]
}

const migrationOptions: RadioCardOption[] = [
  {
    value: 'all',
    icon: (
      <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
        <CheckCircle2 className="h-5 w-5 text-emerald-600" />
      </div>
    ),
    label: __('Migrate All Data', 'wp-statistics'),
    description: __('Transfer all your existing statistics to the new v15 schema. Your historical data will be preserved and converted.', 'wp-statistics'),
    badge: __('Recommended', 'wp-statistics'),
  },
  {
    value: 'selective',
    icon: (
      <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
        <RefreshCw className="h-5 w-5 text-blue-600" />
      </div>
    ),
    label: __('Selective Migration', 'wp-statistics'),
    description: __('Choose which data types to migrate. Useful if you only want specific data or have limited storage.', 'wp-statistics'),
  },
  {
    value: 'fresh',
    icon: (
      <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
        <XCircle className="h-5 w-5 text-amber-600" />
      </div>
    ),
    label: __('Fresh Start', 'wp-statistics'),
    description: __('Start with a clean database. Old v14 tables will be archived (not deleted) and can be restored later if needed.', 'wp-statistics'),
  },
]

export function V14MigrationWizard({ importStatus, setImportStatus }: V14MigrationWizardProps) {
  const [migrationMode, setMigrationMode] = React.useState<MigrationMode>('all')
  const [selectedTables, setSelectedTables] = React.useState<string[]>(['visitors', 'visits', 'pages'])
  const [timePeriod, setTimePeriod] = React.useState<string>('all')
  const [dataStats, setDataStats] = React.useState<V14DataStats>({
    visitors: 0,
    visits: 0,
    pages: 0,
    useronline: 0,
    search: 0,
    exclusions: 0,
    total: 0,
    isLoading: true,
    hasV14Data: false,
  })
  const [confirmFreshStart, setConfirmFreshStart] = React.useState(false)

  // Fetch V14 data statistics on mount
  React.useEffect(() => {
    fetchV14Stats()
  }, [])

  const fetchV14Stats = async () => {
    try {
      const wp = WordPress.getInstance()
      const formData = new FormData()
      formData.append('wps_nonce', wp.getNonce())

      const response = await fetch(`${wp.getAjaxUrl()}?action=wp_statistics_v14_stats`, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      })
      const data = await response.json()
      if (data.success && data.data) {
        setDataStats({
          ...data.data,
          isLoading: false,
          hasV14Data: data.data.total > 0,
        })
      } else {
        setDataStats((prev) => ({ ...prev, isLoading: false }))
      }
    } catch {
      setDataStats((prev) => ({ ...prev, isLoading: false }))
    }
  }

  const handleTableToggle = (table: string) => {
    setSelectedTables((prev) => (prev.includes(table) ? prev.filter((t) => t !== table) : [...prev, table]))
  }

  const startMigration = async () => {
    if (migrationMode === 'fresh' && !confirmFreshStart) {
      return
    }

    setImportStatus({
      status: 'importing',
      progress: 0,
      message: migrationMode === 'fresh' ? __('Creating fresh v15 schema...', 'wp-statistics') : __('Starting migration...', 'wp-statistics'),
    })

    try {
      const wp = WordPress.getInstance()
      const formData = new FormData()
      formData.append('mode', migrationMode)
      formData.append('tables', JSON.stringify(selectedTables))
      if (migrationMode === 'selective' && timePeriod !== 'all') {
        formData.append('days', timePeriod)
      }
      formData.append('wps_nonce', wp.getNonce())

      const response = await fetch(`${wp.getAjaxUrl()}?action=wp_statistics_v14_migrate`, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      })

      const data = await response.json()

      if (data.success) {
        setImportStatus({
          status: 'success',
          progress: 100,
          message: data.data?.message || __('Migration completed successfully!', 'wp-statistics'),
        })
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || __('Migration failed', 'wp-statistics'),
        })
      }
    } catch {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: __('Migration failed. Please try again.', 'wp-statistics'),
      })
    }
  }

  const tables = [
    { key: 'visitors', label: __('Visitors', 'wp-statistics'), count: dataStats.visitors },
    { key: 'visits', label: __('Visits/Sessions', 'wp-statistics'), count: dataStats.visits },
    { key: 'pages', label: __('Page Views', 'wp-statistics'), count: dataStats.pages },
    { key: 'useronline', label: __('Online Users History', 'wp-statistics'), count: dataStats.useronline },
    { key: 'search', label: __('Search Keywords', 'wp-statistics'), count: dataStats.search },
    { key: 'exclusions', label: __('Exclusion Logs', 'wp-statistics'), count: dataStats.exclusions },
  ]

  if (dataStats.isLoading) {
    return (
      <div className="rounded-lg border bg-muted/50 p-6 flex items-center justify-center">
        <Loader2 className="h-5 w-5 animate-spin mr-2" />
        <span className="text-sm">{__('Checking for existing v14 data...', 'wp-statistics')}</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Data Summary */}
      {dataStats.hasV14Data && (
        <NoticeBanner
          title={__('Existing V14 Data Detected', 'wp-statistics')}
          message={sprintf(__('Found %s total records in your v14 database. Choose how you\'d like to proceed with the upgrade.', 'wp-statistics'), dataStats.total.toLocaleString())}
          type="info"
          dismissible={false}
        />
      )}

      {/* Migration Options */}
      <div className="space-y-3">
        <Label>{__('Migration Strategy', 'wp-statistics')}</Label>
        <RadioCardGroup
          name="migration-mode"
          value={migrationMode}
          onValueChange={(v) => {
            setMigrationMode(v as MigrationMode)
            if (v === 'fresh') setConfirmFreshStart(false)
          }}
          options={migrationOptions}
          indicator="check"
        />
      </div>

      {/* Selective Migration: Table Selection & Time Period */}
      {migrationMode === 'selective' && (
        <div className="rounded-lg border p-4 space-y-4">
          <div className="space-y-3">
            <Label>{__('Select Data to Migrate', 'wp-statistics')}</Label>
            <div className="grid grid-cols-2 gap-2">
              {tables.map((table) => (
                // eslint-disable-next-line jsx-a11y/label-has-associated-control -- wraps shadcn Checkbox
                <label
                  key={table.key}
                  className={cn(
                    'flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition-all',
                    selectedTables.includes(table.key)
                      ? 'border-primary bg-primary/5'
                      : 'border-muted hover:bg-muted/50'
                  )}
                >
                  <Checkbox
                    checked={selectedTables.includes(table.key)}
                    onCheckedChange={() => handleTableToggle(table.key)}
                  />
                  <div className="flex-1">
                    <span className="text-sm font-medium">{table.label}</span>
                    <span className="text-xs text-muted-foreground ml-2">({table.count.toLocaleString()})</span>
                  </div>
                </label>
              ))}
            </div>
          </div>

          <div className="border-t pt-4 space-y-3">
            <Label htmlFor="migration-time-period">{__('Time Period', 'wp-statistics')}</Label>
            <p className="text-xs text-muted-foreground">{__('Choose how much historical data to migrate.', 'wp-statistics')}</p>
            <Select value={timePeriod} onValueChange={setTimePeriod}>
              <SelectTrigger id="migration-time-period" className="w-full max-w-xs">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {getTimePeriodOptions().map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>
      )}

      {/* Fresh Start: Confirmation */}
      {migrationMode === 'fresh' && (
        <div className="space-y-3">
          <NoticeBanner
            title={__('Are you sure?', 'wp-statistics')}
            message={__('This will archive your existing v14 data and create fresh v15 tables. Your old data won\'t be deleted but won\'t be accessible in the new dashboard.', 'wp-statistics')}
            type="warning"
            dismissible={false}
          />
          <label className="flex items-center gap-2 cursor-pointer ml-1">
            <Checkbox
              checked={confirmFreshStart}
              onCheckedChange={(checked) => setConfirmFreshStart(checked === true)}
            />
            <span className="text-sm text-muted-foreground">{__('I understand and want to start fresh', 'wp-statistics')}</span>
          </label>
        </div>
      )}

      {/* V15 Benefits */}
      <div className="rounded-lg border bg-primary/5 p-4">
        <h4 className="text-sm font-medium mb-2">{__("What's New in V15", 'wp-statistics')}</h4>
        <ul className="text-sm text-muted-foreground space-y-1">
          <li>
            • <strong>{__('10x faster queries', 'wp-statistics')}</strong> {__('with optimized database schema', 'wp-statistics')}
          </li>
          <li>
            • <strong>{__('Session-based tracking', 'wp-statistics')}</strong> {__('for better visitor journey insights', 'wp-statistics')}
          </li>
          <li>
            • <strong>{__('Enhanced privacy', 'wp-statistics')}</strong> {__('with improved GDPR compliance', 'wp-statistics')}
          </li>
          <li>
            • <strong>{__('Real-time dashboard', 'wp-statistics')}</strong> {__('with modern React UI', 'wp-statistics')}
          </li>
          <li>
            • <strong>{__('Better aggregations', 'wp-statistics')}</strong> {__('for faster reporting', 'wp-statistics')}
          </li>
        </ul>
      </div>

      {/* Action Button */}
      <Button
        onClick={startMigration}
        disabled={
          importStatus.status !== 'idle' ||
          (migrationMode === 'selective' && selectedTables.length === 0) ||
          (migrationMode === 'fresh' && !confirmFreshStart)
        }
        className="w-full"
        size="lg"
      >
        {importStatus.status === 'importing' ? (
          <Loader2 className="mr-2 h-4 w-4 animate-spin" />
        ) : (
          <RefreshCw className="mr-2 h-4 w-4" />
        )}
        {migrationMode === 'all' && __('Start Full Migration', 'wp-statistics')}
        {migrationMode === 'selective' && sprintf(__('Migrate %d selected tables', 'wp-statistics'), selectedTables.length)}
        {migrationMode === 'fresh' && __('Create Fresh V15 Database', 'wp-statistics')}
      </Button>
    </div>
  )
}

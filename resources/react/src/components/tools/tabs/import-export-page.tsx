import * as React from 'react'
import {
  Loader2,
  Upload,
  Download,
  RefreshCw,
  CheckCircle2,
  XCircle,
  AlertTriangle,
} from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { cn } from '@/lib/utils'

// Get the plugin URL from WordPress localized data
const getPluginUrl = () => {
  const wpsReact = (window as any).wps_react
  return wpsReact?.globals?.pluginUrl || '/wp-content/plugins/wp-statistics'
}

interface ImportAdapter {
  key: string
  name: string
  label: string
  extensions: string[]
  is_aggregate_import: boolean
}

interface ImportStatus {
  status: 'idle' | 'uploading' | 'previewing' | 'importing' | 'success' | 'error'
  progress: number
  message: string
  importId?: string
  preview?: {
    headers: string[]
    total_rows: number
    sample_rows: any[]
    is_valid: boolean
  }
}

interface ExportStatus {
  status: 'idle' | 'exporting' | 'success' | 'error'
  progress: number
  message: string
  exportId?: string
}

// Helper to get import/export config
const getConfig = () => {
  const wpsReact = (window as any).wps_react
  return {
    ajaxUrl: wpsReact?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: wpsReact?.globals?.nonce || '',
  }
}

// Helper to call import/export endpoint with sub_action
const callImportExportApi = async (
  subAction: string,
  params: Record<string, string> = {},
  formData?: FormData
) => {
  const config = getConfig()
  const data = formData || new FormData()
  data.append('wps_nonce', config.nonce)
  data.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    if (!data.has(key)) {
      data.append(key, value)
    }
  })

  const response = await fetch(`${config.ajaxUrl}?action=wp_statistics_import_export`, {
    method: 'POST',
    body: data,
  })
  return response.json()
}

// V14 to V15 Migration Wizard Component
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

const TIME_PERIOD_OPTIONS = [
  { value: 'all', label: 'All Time' },
  { value: '30', label: 'Last 30 Days' },
  { value: '90', label: 'Last 90 Days' },
  { value: '180', label: 'Last 6 Months' },
  { value: '365', label: 'Last Year' },
  { value: '730', label: 'Last 2 Years' },
]

function V14MigrationWizard({ importStatus, setImportStatus }: V14MigrationWizardProps) {
  const [migrationMode, setMigrationMode] = React.useState<MigrationMode>('all')
  const [selectedTables, setSelectedTables] = React.useState<string[]>([
    'visitors',
    'visits',
    'pages',
  ])
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
      const config = getConfig()
      const response = await fetch(
        `${config.ajaxUrl}?action=wp_statistics_v14_stats`,
        {
          method: 'POST',
          headers: { 'X-WP-Nonce': config.nonce },
        }
      )
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
    setSelectedTables((prev) =>
      prev.includes(table) ? prev.filter((t) => t !== table) : [...prev, table]
    )
  }

  const startMigration = async () => {
    if (migrationMode === 'fresh' && !confirmFreshStart) {
      return
    }

    setImportStatus({
      status: 'importing',
      progress: 0,
      message: migrationMode === 'fresh'
        ? 'Creating fresh v15 schema...'
        : 'Starting migration...',
    })

    try {
      const config = getConfig()
      const formData = new FormData()
      formData.append('mode', migrationMode)
      formData.append('tables', JSON.stringify(selectedTables))
      if (migrationMode === 'selective' && timePeriod !== 'all') {
        formData.append('days', timePeriod)
      }
      formData.append('_wpnonce', config.nonce)

      const response = await fetch(
        `${config.ajaxUrl}?action=wp_statistics_v14_migrate`,
        {
          method: 'POST',
          headers: { 'X-WP-Nonce': config.nonce },
          body: formData,
        }
      )

      const data = await response.json()

      if (data.success) {
        setImportStatus({
          status: 'success',
          progress: 100,
          message: data.data?.message || 'Migration completed successfully!',
        })
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || 'Migration failed',
        })
      }
    } catch {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: 'Migration failed. Please try again.',
      })
    }
  }

  const tables = [
    { key: 'visitors', label: 'Visitors', count: dataStats.visitors },
    { key: 'visits', label: 'Visits/Sessions', count: dataStats.visits },
    { key: 'pages', label: 'Page Views', count: dataStats.pages },
    { key: 'useronline', label: 'Online Users History', count: dataStats.useronline },
    { key: 'search', label: 'Search Keywords', count: dataStats.search },
    { key: 'exclusions', label: 'Exclusion Logs', count: dataStats.exclusions },
  ]

  if (dataStats.isLoading) {
    return (
      <div className="rounded-lg border bg-muted/50 p-6 flex items-center justify-center">
        <Loader2 className="h-5 w-5 animate-spin mr-2" />
        <span className="text-sm">Checking for existing v14 data...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Data Summary */}
      {dataStats.hasV14Data && (
        <div className="rounded-lg border bg-blue-50 dark:bg-blue-950/20 p-4">
          <div className="flex items-start gap-3">
            <AlertTriangle className="h-5 w-5 text-blue-600 mt-0.5" />
            <div>
              <h4 className="text-sm font-medium text-blue-900 dark:text-blue-100">
                Existing V14 Data Detected
              </h4>
              <p className="text-sm text-blue-700 dark:text-blue-300 mt-1">
                Found <strong>{dataStats.total.toLocaleString()}</strong> total records in your v14 database.
                Choose how you'd like to proceed with the upgrade.
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Migration Options */}
      <div className="space-y-3">
        <Label>Migration Strategy</Label>
        <div className="grid gap-3">
          {/* Option: Migrate All */}
          <label
            className={cn(
              'relative flex items-start gap-4 rounded-lg border-2 p-4 cursor-pointer transition-all',
              migrationMode === 'all'
                ? 'border-primary bg-primary/5'
                : 'border-muted hover:border-muted-foreground/30'
            )}
          >
            <input
              type="radio"
              name="migration-mode"
              value="all"
              checked={migrationMode === 'all'}
              onChange={() => setMigrationMode('all')}
              className="sr-only"
            />
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
              <CheckCircle2 className="h-5 w-5 text-green-600" />
            </div>
            <div className="flex-1">
              <div className="flex items-center gap-2">
                <span className="font-medium">Migrate All Data</span>
                <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Recommended</span>
              </div>
              <p className="text-sm text-muted-foreground mt-1">
                Transfer all your existing statistics to the new v15 schema.
                Your historical data will be preserved and converted.
              </p>
            </div>
            {migrationMode === 'all' && (
              <CheckCircle2 className="absolute top-4 right-4 h-5 w-5 text-primary" />
            )}
          </label>

          {/* Option: Selective Migration */}
          <label
            className={cn(
              'relative flex items-start gap-4 rounded-lg border-2 p-4 cursor-pointer transition-all',
              migrationMode === 'selective'
                ? 'border-primary bg-primary/5'
                : 'border-muted hover:border-muted-foreground/30'
            )}
          >
            <input
              type="radio"
              name="migration-mode"
              value="selective"
              checked={migrationMode === 'selective'}
              onChange={() => setMigrationMode('selective')}
              className="sr-only"
            />
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
              <RefreshCw className="h-5 w-5 text-blue-600" />
            </div>
            <div className="flex-1">
              <span className="font-medium">Selective Migration</span>
              <p className="text-sm text-muted-foreground mt-1">
                Choose which data types to migrate. Useful if you only want specific data or have limited storage.
              </p>
            </div>
            {migrationMode === 'selective' && (
              <CheckCircle2 className="absolute top-4 right-4 h-5 w-5 text-primary" />
            )}
          </label>

          {/* Option: Fresh Start */}
          <label
            className={cn(
              'relative flex items-start gap-4 rounded-lg border-2 p-4 cursor-pointer transition-all',
              migrationMode === 'fresh'
                ? 'border-primary bg-primary/5'
                : 'border-muted hover:border-muted-foreground/30'
            )}
          >
            <input
              type="radio"
              name="migration-mode"
              value="fresh"
              checked={migrationMode === 'fresh'}
              onChange={() => {
                setMigrationMode('fresh')
                setConfirmFreshStart(false)
              }}
              className="sr-only"
            />
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
              <XCircle className="h-5 w-5 text-amber-600" />
            </div>
            <div className="flex-1">
              <span className="font-medium">Fresh Start</span>
              <p className="text-sm text-muted-foreground mt-1">
                Start with a clean database. Old v14 tables will be archived (not deleted) and can be restored later if needed.
              </p>
            </div>
            {migrationMode === 'fresh' && (
              <CheckCircle2 className="absolute top-4 right-4 h-5 w-5 text-primary" />
            )}
          </label>
        </div>
      </div>

      {/* Selective Migration: Table Selection & Time Period */}
      {migrationMode === 'selective' && (
        <div className="rounded-lg border p-4 space-y-4">
          <div className="space-y-3">
            <Label>Select Data to Migrate</Label>
            <div className="grid grid-cols-2 gap-2">
              {tables.map((table) => (
                <label
                  key={table.key}
                  className={cn(
                    'flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition-all',
                    selectedTables.includes(table.key)
                      ? 'border-primary bg-primary/5'
                      : 'border-muted hover:bg-muted/50'
                  )}
                >
                  <input
                    type="checkbox"
                    checked={selectedTables.includes(table.key)}
                    onChange={() => handleTableToggle(table.key)}
                    className="rounded border-muted"
                  />
                  <div className="flex-1">
                    <span className="text-sm font-medium">{table.label}</span>
                    <span className="text-xs text-muted-foreground ml-2">
                      ({table.count.toLocaleString()})
                    </span>
                  </div>
                </label>
              ))}
            </div>
          </div>

          <div className="border-t pt-4 space-y-3">
            <Label htmlFor="migration-time-period">Time Period</Label>
            <p className="text-xs text-muted-foreground">
              Choose how much historical data to migrate.
            </p>
            <select
              id="migration-time-period"
              value={timePeriod}
              onChange={(e) => setTimePeriod(e.target.value)}
              className="flex h-9 w-full max-w-xs rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              {TIME_PERIOD_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>
        </div>
      )}

      {/* Fresh Start: Confirmation */}
      {migrationMode === 'fresh' && (
        <div className="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/20 p-4 space-y-3">
          <div className="flex items-start gap-3">
            <AlertTriangle className="h-5 w-5 text-amber-600 mt-0.5" />
            <div>
              <h4 className="text-sm font-medium text-amber-900 dark:text-amber-100">
                Are you sure?
              </h4>
              <p className="text-sm text-amber-700 dark:text-amber-300 mt-1">
                This will archive your existing v14 data and create fresh v15 tables.
                Your old data won't be deleted but won't be accessible in the new dashboard.
              </p>
            </div>
          </div>
          <label className="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              checked={confirmFreshStart}
              onChange={(e) => setConfirmFreshStart(e.target.checked)}
              className="rounded border-amber-300"
            />
            <span className="text-sm text-amber-800 dark:text-amber-200">
              I understand and want to start fresh
            </span>
          </label>
        </div>
      )}

      {/* V15 Benefits */}
      <div className="rounded-lg border bg-gradient-to-r from-primary/5 to-transparent p-4">
        <h4 className="text-sm font-medium mb-2">✨ What's New in V15</h4>
        <ul className="text-sm text-muted-foreground space-y-1">
          <li>• <strong>10x faster queries</strong> with optimized database schema</li>
          <li>• <strong>Session-based tracking</strong> for better visitor journey insights</li>
          <li>• <strong>Enhanced privacy</strong> with improved GDPR compliance</li>
          <li>• <strong>Real-time dashboard</strong> with modern React UI</li>
          <li>• <strong>Better aggregations</strong> for faster reporting</li>
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
        {migrationMode === 'all' && 'Start Full Migration'}
        {migrationMode === 'selective' && `Migrate ${selectedTables.length} Selected Tables`}
        {migrationMode === 'fresh' && 'Create Fresh V15 Database'}
      </Button>
    </div>
  )
}

export function ImportExportPage() {
  const [adapters, setAdapters] = React.useState<ImportAdapter[]>([])
  const [selectedAdapter, setSelectedAdapter] = React.useState<string>('')
  const [importStatus, setImportStatus] = React.useState<ImportStatus>({
    status: 'idle',
    progress: 0,
    message: '',
  })
  const [exportStatus, setExportStatus] = React.useState<ExportStatus>({
    status: 'idle',
    progress: 0,
    message: '',
  })
  const [exportDateFrom, setExportDateFrom] = React.useState<string>('')
  const [exportDateTo, setExportDateTo] = React.useState<string>('')
  const [isLoadingAdapters, setIsLoadingAdapters] = React.useState(true)

  const fileInputRef = React.useRef<HTMLInputElement>(null)

  // Fetch available import adapters on mount
  React.useEffect(() => {
    fetchAdapters()
  }, [])

  const fetchAdapters = async () => {
    try {
      const data = await callImportExportApi('get_adapters')

      if (data.success && data.data?.adapters) {
        const adapterList = Object.values(data.data.adapters) as ImportAdapter[]
        setAdapters(adapterList)
        if (adapterList.length > 0) {
          setSelectedAdapter(adapterList[0].key)
        }
      }
    } catch (error) {
      console.error('Failed to fetch adapters:', error)
    } finally {
      setIsLoadingAdapters(false)
    }
  }

  const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file || !selectedAdapter) return

    setImportStatus({
      status: 'uploading',
      progress: 0,
      message: 'Uploading file...',
    })

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('adapter', selectedAdapter)

      const data = await callImportExportApi('upload', {}, formData)

      if (data.success) {
        setImportStatus({
          status: 'previewing',
          progress: 25,
          message: 'Loading preview...',
          importId: data.data.import_id,
        })

        await fetchPreview(data.data.import_id)
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || 'Upload failed',
        })
      }
    } catch (error) {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: 'Upload failed. Please try again.',
      })
    }

    if (fileInputRef.current) {
      fileInputRef.current.value = ''
    }
  }

  const fetchPreview = async (importId: string) => {
    try {
      const data = await callImportExportApi('preview', { import_id: importId })

      if (data.success) {
        setImportStatus((prev) => ({
          ...prev,
          status: 'previewing',
          progress: 50,
          message: `Found ${data.data.total_rows} records`,
          preview: data.data,
        }))
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || 'Failed to load preview',
        })
      }
    } catch (error) {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: 'Failed to load preview',
      })
    }
  }

  const startImport = async () => {
    if (!importStatus.importId) return

    setImportStatus((prev) => ({
      ...prev,
      status: 'importing',
      progress: 50,
      message: 'Importing data...',
    }))

    try {
      const data = await callImportExportApi('start_import', { import_id: importStatus.importId })

      if (data.success) {
        setImportStatus({
          status: 'success',
          progress: 100,
          message: 'Import completed successfully!',
        })
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || 'Import failed',
        })
      }
    } catch (error) {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: 'Import failed. Please try again.',
      })
    }
  }

  const cancelImport = async () => {
    if (!importStatus.importId) return

    try {
      await callImportExportApi('cancel_import', { import_id: importStatus.importId })
    } catch (error) {
      // Ignore errors on cancel
    }

    setImportStatus({
      status: 'idle',
      progress: 0,
      message: '',
    })
  }

  const startExport = async () => {
    setExportStatus({
      status: 'exporting',
      progress: 25,
      message: 'Preparing export...',
    })

    try {
      const data = await callImportExportApi('start_export', {
        date_from: exportDateFrom,
        date_to: exportDateTo,
      })

      if (data.success) {
        setExportStatus({
          status: 'success',
          progress: 100,
          message: 'Export ready for download',
          exportId: data.data.export_id,
        })
      } else {
        setExportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || 'Export failed',
        })
      }
    } catch (error) {
      setExportStatus({
        status: 'error',
        progress: 0,
        message: 'Export failed. Please try again.',
      })
    }
  }

  const downloadExport = () => {
    if (!exportStatus.exportId) return

    const config = getConfig()
    window.location.href = `${config.ajaxUrl}?action=wp_statistics_import_export&sub_action=download&export_id=${exportStatus.exportId}&wps_nonce=${config.nonce}`

    setTimeout(() => {
      setExportStatus({
        status: 'idle',
        progress: 0,
        message: '',
      })
    }, 2000)
  }

  const selectedAdapterInfo = adapters.find((a) => a.key === selectedAdapter)

  // Get logo for adapter
  const getAdapterLogo = (adapterKey: string, size: string = 'h-8 w-8') => {
    const pluginUrl = getPluginUrl()
    const logoMap: Record<string, string> = {
      wp_statistics_backup: 'wp-statistics.svg',
      legacy_v14: 'wp-statistics.svg',
      google_analytics_4: 'google-analytics.svg',
      plausible: 'plausible.svg',
    }

    const logoFile = logoMap[adapterKey] || 'wp-statistics.svg'

    return (
      <img
        src={`${pluginUrl}public/images/logos/${logoFile}`}
        alt=""
        className={size}
      />
    )
  }

  return (
    <div className="space-y-6">
      {/* Import Section */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Upload className="h-5 w-5" />
            Import Data
          </CardTitle>
          <CardDescription>
            Import analytics data from external sources like Google Analytics 4, Plausible, or restore from a backup file.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          {isLoadingAdapters ? (
            <div className="flex items-center justify-center p-8">
              <Loader2 className="h-6 w-6 animate-spin" />
              <span className="ml-2">Loading import options...</span>
            </div>
          ) : (
            <>
              <div className="space-y-3">
                <Label>Import Source</Label>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                  {adapters.map((adapter) => (
                    <label
                      key={adapter.key}
                      className={cn(
                        'relative flex flex-col items-center gap-2 rounded-lg border-2 p-4 cursor-pointer transition-all hover:bg-muted/50',
                        selectedAdapter === adapter.key
                          ? 'border-primary bg-primary/5'
                          : 'border-muted hover:border-muted-foreground/30'
                      )}
                    >
                      <input
                        type="radio"
                        name="import-source"
                        value={adapter.key}
                        checked={selectedAdapter === adapter.key}
                        onChange={(e) => setSelectedAdapter(e.target.value)}
                        className="sr-only"
                      />
                      {getAdapterLogo(adapter.key, 'h-10 w-10')}
                      <span className="text-xs font-medium text-center leading-tight">
                        {adapter.label}
                      </span>
                      {selectedAdapter === adapter.key && (
                        <CheckCircle2 className="absolute top-2 right-2 h-4 w-4 text-primary" />
                      )}
                    </label>
                  ))}
                </div>
                {selectedAdapterInfo && (
                  <p className="text-xs text-muted-foreground">
                    Supported formats: {selectedAdapterInfo.extensions.join(', ').toUpperCase() || 'Database migration'}
                    {selectedAdapterInfo.is_aggregate_import && (
                      <span className="ml-2 text-amber-600">
                        (Imports to summary tables)
                      </span>
                    )}
                  </p>
                )}
              </div>

              {selectedAdapter !== 'legacy_v14' && (
                <div className="space-y-2">
                  <Label htmlFor="import-file">Upload File</Label>
                  <Input
                    ref={fileInputRef}
                    id="import-file"
                    type="file"
                    accept={selectedAdapterInfo?.extensions.map((e) => `.${e}`).join(',')}
                    onChange={handleFileSelect}
                    disabled={importStatus.status !== 'idle'}
                  />
                </div>
              )}

              {selectedAdapter === 'legacy_v14' && (
                <V14MigrationWizard
                  importStatus={importStatus}
                  setImportStatus={setImportStatus}
                />
              )}

              {/* Import Status */}
              {importStatus.status !== 'idle' && (
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    {importStatus.status === 'uploading' || importStatus.status === 'importing' ? (
                      <Loader2 className="h-4 w-4 animate-spin text-primary" />
                    ) : importStatus.status === 'success' ? (
                      <CheckCircle2 className="h-4 w-4 text-green-500" />
                    ) : importStatus.status === 'error' ? (
                      <XCircle className="h-4 w-4 text-destructive" />
                    ) : (
                      <AlertTriangle className="h-4 w-4 text-amber-500" />
                    )}
                    <span className="text-sm">{importStatus.message}</span>
                  </div>

                  <Progress value={importStatus.progress} className="h-2" />

                  {/* Preview */}
                  {importStatus.status === 'previewing' && importStatus.preview && (
                    <div className="rounded-lg border p-4 space-y-4">
                      <div className="flex items-center justify-between">
                        <h4 className="text-sm font-medium">Preview</h4>
                        <span className="text-xs text-muted-foreground">
                          {importStatus.preview.total_rows.toLocaleString()} records
                        </span>
                      </div>

                      {importStatus.preview.is_valid ? (
                        <>
                          <div className="text-xs text-muted-foreground">
                            Columns: {importStatus.preview.headers.slice(0, 5).join(', ')}
                            {importStatus.preview.headers.length > 5 &&
                              ` +${importStatus.preview.headers.length - 5} more`}
                          </div>

                          <div className="flex gap-2">
                            <Button onClick={startImport}>
                              <Upload className="mr-2 h-4 w-4" />
                              Start Import
                            </Button>
                            <Button variant="outline" onClick={cancelImport}>
                              Cancel
                            </Button>
                          </div>
                        </>
                      ) : (
                        <div className="flex items-center gap-2 text-destructive">
                          <XCircle className="h-4 w-4" />
                          <span className="text-sm">
                            File format is invalid. Please check the file and try again.
                          </span>
                        </div>
                      )}
                    </div>
                  )}

                  {/* Success/Error Actions */}
                  {(importStatus.status === 'success' || importStatus.status === 'error') && (
                    <Button
                      variant="outline"
                      onClick={() =>
                        setImportStatus({ status: 'idle', progress: 0, message: '' })
                      }
                    >
                      Import Another File
                    </Button>
                  )}
                </div>
              )}
            </>
          )}
        </CardContent>
      </Card>

      {/* Export Section */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Download className="h-5 w-5" />
            Export Data
          </CardTitle>
          <CardDescription>
            Export your analytics data to JSON format for migration to another site or for external analysis.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="export-from">From Date (Optional)</Label>
              <Input
                id="export-from"
                type="date"
                value={exportDateFrom}
                onChange={(e) => setExportDateFrom(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="export-to">To Date (Optional)</Label>
              <Input
                id="export-to"
                type="date"
                value={exportDateTo}
                onChange={(e) => setExportDateTo(e.target.value)}
              />
            </div>
          </div>

          <p className="text-xs text-muted-foreground">
            Leave dates empty to export all data. The export will be in JSON format compatible with the Import feature.
          </p>

          {/* Export Status */}
          {exportStatus.status !== 'idle' && (
            <div className="space-y-4">
              <div className="flex items-center gap-2">
                {exportStatus.status === 'exporting' ? (
                  <Loader2 className="h-4 w-4 animate-spin text-primary" />
                ) : exportStatus.status === 'success' ? (
                  <CheckCircle2 className="h-4 w-4 text-green-500" />
                ) : (
                  <XCircle className="h-4 w-4 text-destructive" />
                )}
                <span className="text-sm">{exportStatus.message}</span>
              </div>

              <Progress value={exportStatus.progress} className="h-2" />
            </div>
          )}

          <div className="flex gap-2">
            {exportStatus.status === 'success' && exportStatus.exportId ? (
              <Button onClick={downloadExport}>
                <Download className="mr-2 h-4 w-4" />
                Download Export
              </Button>
            ) : (
              <Button
                onClick={startExport}
                disabled={exportStatus.status === 'exporting'}
              >
                {exportStatus.status === 'exporting' ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <Download className="mr-2 h-4 w-4" />
                )}
                Create Export
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

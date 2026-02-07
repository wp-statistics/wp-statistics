import { __, sprintf } from '@wordpress/i18n'
import { AlertTriangle, CheckCircle2, Download, Loader2, Upload, XCircle } from 'lucide-react'
import * as React from 'react'

import { SettingsCard } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { callImportExportApi } from '@/services/tools'

import { V14MigrationWizard } from './v14-migration-wizard'

interface ImportAdapter {
  key: string
  name: string
  label: string
  extensions: string[]
  is_aggregate_import: boolean
}

export interface ImportStatus {
  status: 'idle' | 'uploading' | 'previewing' | 'importing' | 'success' | 'error'
  progress: number
  message: string
  importId?: string
  preview?: {
    headers: string[]
    total_rows: number
    sample_rows: unknown[]
    is_valid: boolean
  }
}

interface ExportStatus {
  status: 'idle' | 'exporting' | 'success' | 'error'
  progress: number
  message: string
  exportId?: string
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
    } catch {
      // Silently fail - user sees empty adapter list
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
      message: __('Uploading file...', 'wp-statistics'),
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
          message: __('Loading preview...', 'wp-statistics'),
          importId: data.data.import_id,
        })

        await fetchPreview(data.data.import_id)
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || __('Upload failed', 'wp-statistics'),
        })
      }
    } catch {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: __('Upload failed. Please try again.', 'wp-statistics'),
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
          message: sprintf(__('Found %s records', 'wp-statistics'), data.data.total_rows.toLocaleString()),
          preview: data.data,
        }))
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || __('Failed to load preview', 'wp-statistics'),
        })
      }
    } catch {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: __('Failed to load preview', 'wp-statistics'),
      })
    }
  }

  const startImport = async () => {
    if (!importStatus.importId) return

    setImportStatus((prev) => ({
      ...prev,
      status: 'importing',
      progress: 50,
      message: __('Importing data...', 'wp-statistics'),
    }))

    try {
      const data = await callImportExportApi('start_import', { import_id: importStatus.importId })

      if (data.success) {
        setImportStatus({
          status: 'success',
          progress: 100,
          message: __('Import completed successfully!', 'wp-statistics'),
        })
      } else {
        setImportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || __('Import failed', 'wp-statistics'),
        })
      }
    } catch {
      setImportStatus({
        status: 'error',
        progress: 0,
        message: __('Import failed. Please try again.', 'wp-statistics'),
      })
    }
  }

  const cancelImport = async () => {
    if (!importStatus.importId) return

    try {
      await callImportExportApi('cancel_import', { import_id: importStatus.importId })
    } catch {
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
      message: __('Preparing export...', 'wp-statistics'),
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
          message: __('Export ready for download', 'wp-statistics'),
          exportId: data.data.export_id,
        })
      } else {
        setExportStatus({
          status: 'error',
          progress: 0,
          message: data.data?.message || __('Export failed', 'wp-statistics'),
        })
      }
    } catch {
      setExportStatus({
        status: 'error',
        progress: 0,
        message: __('Export failed. Please try again.', 'wp-statistics'),
      })
    }
  }

  const downloadExport = () => {
    if (!exportStatus.exportId) return

    const wp = WordPress.getInstance()
    window.location.href = `${wp.getAjaxUrl()}?action=wp_statistics_import_export&sub_action=download&export_id=${exportStatus.exportId}&wps_nonce=${wp.getNonce()}`

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
    const pluginUrl = WordPress.getInstance().getPluginUrl()
    const logoMap: Record<string, string> = {
      wp_statistics_backup: 'wp-statistics.svg',
      legacy_v14: 'wp-statistics.svg',
      google_analytics_4: 'google-analytics.svg',
      plausible: 'plausible.svg',
    }

    const logoFile = logoMap[adapterKey] || 'wp-statistics.svg'

    return <img src={`${pluginUrl}public/images/logos/${logoFile}`} alt="" className={size} />
  }

  return (
    <div className="space-y-5">
      {/* Import Section */}
      <SettingsCard
        title={__('Import Data', 'wp-statistics')}
        icon={Upload}
        description={__('Import analytics data from external sources like Google Analytics 4, Plausible, or restore from a backup file.', 'wp-statistics')}
      >
          {isLoadingAdapters ? (
            <div className="flex items-center justify-center p-8">
              <Loader2 className="h-6 w-6 animate-spin" />
              <span className="ml-2">{__('Loading import options...', 'wp-statistics')}</span>
            </div>
          ) : (
            <>
              <div className="space-y-3">
                <Label>{__('Import Source', 'wp-statistics')}</Label>
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
                      <span className="text-xs font-medium text-center leading-tight">{adapter.label}</span>
                      {selectedAdapter === adapter.key && (
                        <CheckCircle2 className="absolute top-2 right-2 h-4 w-4 text-primary" />
                      )}
                    </label>
                  ))}
                </div>
                {selectedAdapterInfo && (
                  <p className="text-xs text-muted-foreground">
                    {__('Supported formats:', 'wp-statistics')} {selectedAdapterInfo.extensions.join(', ').toUpperCase() || __('Database migration', 'wp-statistics')}
                    {selectedAdapterInfo.is_aggregate_import && (
                      <span className="ml-2 text-amber-600">({__('Imports to summary tables', 'wp-statistics')})</span>
                    )}
                  </p>
                )}
              </div>

              {selectedAdapter !== 'legacy_v14' && (
                <div className="space-y-2">
                  <Label htmlFor="import-file">{__('Upload File', 'wp-statistics')}</Label>
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
                <V14MigrationWizard importStatus={importStatus} setImportStatus={setImportStatus} />
              )}

              {/* Import Status */}
              {importStatus.status !== 'idle' && (
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    {importStatus.status === 'uploading' || importStatus.status === 'importing' ? (
                      <Loader2 className="h-4 w-4 animate-spin text-primary" />
                    ) : importStatus.status === 'success' ? (
                      <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                    ) : importStatus.status === 'error' ? (
                      <XCircle className="h-4 w-4 text-destructive" />
                    ) : (
                      <AlertTriangle className="h-4 w-4 text-amber-600" />
                    )}
                    <span className="text-sm">{importStatus.message}</span>
                  </div>

                  <Progress value={importStatus.progress} className="h-2" />

                  {/* Preview */}
                  {importStatus.status === 'previewing' && importStatus.preview && (
                    <div className="rounded-lg border p-4 space-y-4">
                      <div className="flex items-center justify-between">
                        <h4 className="text-sm font-medium">{__('Preview', 'wp-statistics')}</h4>
                        <span className="text-xs text-muted-foreground">
                          {sprintf(__('%s records', 'wp-statistics'), importStatus.preview.total_rows.toLocaleString())}
                        </span>
                      </div>

                      {importStatus.preview.is_valid ? (
                        <>
                          <div className="text-xs text-muted-foreground">
                            {__('Columns:', 'wp-statistics')} {importStatus.preview.headers.slice(0, 5).join(', ')}
                            {importStatus.preview.headers.length > 5 &&
                              ` ${sprintf(__('+%d more', 'wp-statistics'), importStatus.preview.headers.length - 5)}`}
                          </div>

                          <div className="flex gap-2">
                            <Button onClick={startImport}>
                              <Upload className="mr-2 h-4 w-4" />
                              {__('Start Import', 'wp-statistics')}
                            </Button>
                            <Button variant="outline" onClick={cancelImport}>
                              {__('Cancel', 'wp-statistics')}
                            </Button>
                          </div>
                        </>
                      ) : (
                        <div className="flex items-center gap-2 text-destructive">
                          <XCircle className="h-4 w-4" />
                          <span className="text-sm">{__('File format is invalid. Please check the file and try again.', 'wp-statistics')}</span>
                        </div>
                      )}
                    </div>
                  )}

                  {/* Success/Error Actions */}
                  {(importStatus.status === 'success' || importStatus.status === 'error') && (
                    <Button
                      variant="outline"
                      onClick={() => setImportStatus({ status: 'idle', progress: 0, message: '' })}
                    >
                      {__('Import Another File', 'wp-statistics')}
                    </Button>
                  )}
                </div>
              )}
            </>
          )}
      </SettingsCard>

      {/* Export Section */}
      <SettingsCard
        title={__('Export Data', 'wp-statistics')}
        icon={Download}
        description={__('Export your analytics data to JSON format for migration to another site or for external analysis.', 'wp-statistics')}
      >
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="export-from">{__('From Date (Optional)', 'wp-statistics')}</Label>
              <Input
                id="export-from"
                type="date"
                value={exportDateFrom}
                onChange={(e) => setExportDateFrom(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="export-to">{__('To Date (Optional)', 'wp-statistics')}</Label>
              <Input
                id="export-to"
                type="date"
                value={exportDateTo}
                onChange={(e) => setExportDateTo(e.target.value)}
              />
            </div>
          </div>

          <p className="text-xs text-muted-foreground">
            {__('Leave dates empty to export all data. The export will be in JSON format compatible with the Import feature.', 'wp-statistics')}
          </p>

          {/* Export Status */}
          {exportStatus.status !== 'idle' && (
            <div className="space-y-4">
              <div className="flex items-center gap-2">
                {exportStatus.status === 'exporting' ? (
                  <Loader2 className="h-4 w-4 animate-spin text-primary" />
                ) : exportStatus.status === 'success' ? (
                  <CheckCircle2 className="h-4 w-4 text-emerald-600" />
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
                {__('Download Export', 'wp-statistics')}
              </Button>
            ) : (
              <Button onClick={startExport} disabled={exportStatus.status === 'exporting'}>
                {exportStatus.status === 'exporting' ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <Download className="mr-2 h-4 w-4" />
                )}
                {__('Create Export', 'wp-statistics')}
              </Button>
            )}
          </div>
      </SettingsCard>
    </div>
  )
}

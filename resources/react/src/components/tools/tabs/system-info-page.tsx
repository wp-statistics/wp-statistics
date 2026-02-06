import { __, sprintf } from '@wordpress/i18n'
import { Clock, Database, HardDrive, Loader2, RefreshCw, Server, Settings, User } from 'lucide-react'
import * as React from 'react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { callToolsApi } from '@/services/tools'

interface TableInfo {
  key: string
  name: string
  description: string
  records: number
  size: string
  engine: string
  isLegacy: boolean
  isAddon: boolean
  addonName: string | null
}

interface PluginInfo {
  version: string
  db_version: string
  php: string
  mysql: string
  wp: string
}

interface OptionItem {
  key: string
  value: string
  group: string
}

interface TransientItem {
  name: string
  value: string
}

interface UserMetaItem {
  key: string
  value: string
  exists: boolean
  isLegacy: boolean
}

export function SystemInfoPage() {
  const [tables, setTables] = React.useState<TableInfo[]>([])
  const [plugin, setPlugin] = React.useState<PluginInfo | null>(null)
  const [isLoading, setIsLoading] = React.useState(true)
  const [loadError, setLoadError] = React.useState<string | null>(null)
  const [options, setOptions] = React.useState<OptionItem[]>([])
  const [transients, setTransients] = React.useState<TransientItem[]>([])
  const [userMeta, setUserMeta] = React.useState<UserMetaItem[]>([])
  const [isLoadingOptionsTransients, setIsLoadingOptionsTransients] = React.useState(false)

  // Fetch system info on mount
  React.useEffect(() => {
    fetchSystemInfo()
  }, [])

  const fetchSystemInfo = async () => {
    try {
      const data = await callToolsApi('system_info')

      if (data.success) {
        setTables(data.data.tables || [])
        setPlugin(data.data.plugin || null)
      }
    } catch {
      setLoadError(__('Failed to load system information. Please refresh the page.', 'wp-statistics'))
    } finally {
      setIsLoading(false)
    }
  }

  const getTotalRecords = () => {
    return tables.reduce((sum, table) => sum + (table.records || 0), 0)
  }

  const fetchOptionsAndTransients = async () => {
    setIsLoadingOptionsTransients(true)
    try {
      const data = await callToolsApi('options_transients')
      if (data.success) {
        setOptions(data.data.options || [])
        setTransients(data.data.transients || [])
        setUserMeta(data.data.user_meta || [])
      }
    } catch {
      // Errors silently ignored - user can retry via Load Data button
    } finally {
      setIsLoadingOptionsTransients(false)
    }
  }

  const getGroupLabel = (group: string) => {
    const labels: Record<string, string> = {
      main: __('Main Settings', 'wp-statistics'),
      db: __('Database', 'wp-statistics'),
      jobs: __('Background Jobs', 'wp-statistics'),
      cache: __('Cache', 'wp-statistics'),
      version: __('Version Info', 'wp-statistics'),
    }
    return labels[group] || group
  }

  const groupedOptions = React.useMemo(() => {
    const groups: Record<string, OptionItem[]> = {}
    options.forEach((opt) => {
      if (!groups[opt.group]) {
        groups[opt.group] = []
      }
      groups[opt.group].push(opt)
    })
    return groups
  }, [options])

  if (isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">{__('Loading system information...', 'wp-statistics')}</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Load Error */}
      {loadError && (
        <NoticeBanner
          id="system-info-status"
          message={loadError}
          type="error"
          dismissible={false}
        />
      )}

      {/* Plugin Info Card */}
      {plugin && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Server className="h-5 w-5" />
              {__('Plugin Information', 'wp-statistics')}
            </CardTitle>
            <CardDescription>{__('Current versions and environment details.', 'wp-statistics')}</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">{__('Plugin Version', 'wp-statistics')}</p>
                <p className="font-medium">{plugin.version}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">{__('Database Version', 'wp-statistics')}</p>
                <p className="font-medium">{plugin.db_version}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">{__('PHP Version', 'wp-statistics')}</p>
                <p className="font-medium">{plugin.php}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">{__('MySQL Version', 'wp-statistics')}</p>
                <p className="font-medium">{plugin.mysql}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">{__('WordPress Version', 'wp-statistics')}</p>
                <p className="font-medium">{plugin.wp}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Database Tables Card */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Database className="h-5 w-5" />
            {__('Database Tables', 'wp-statistics')}
          </CardTitle>
          <CardDescription>
            {sprintf(__('%1$d tables with %2$s total records.', 'wp-statistics'), tables.length, getTotalRecords().toLocaleString())}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {tables.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Database className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">{__('No tables found', 'wp-statistics')}</h3>
              <p className="text-sm text-muted-foreground">{__('Database tables have not been created yet.', 'wp-statistics')}</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{__('Table', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Description', 'wp-statistics')}</TableHead>
                  <TableHead className="text-right">{__('Records', 'wp-statistics')}</TableHead>
                  <TableHead className="text-right">{__('Size', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Engine', 'wp-statistics')}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {tables.map((table) => (
                  <TableRow key={table.key} className={table.isLegacy ? 'opacity-60' : ''}>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        <HardDrive className="h-4 w-4 text-muted-foreground" />
                        <code className="text-xs bg-muted px-1.5 py-0.5 rounded">{table.key}</code>
                        {table.isLegacy && (
                          <Badge
                            variant="secondary"
                            className="text-[11px] px-1.5 py-0 h-4 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                          >
                            {__('Legacy', 'wp-statistics')}
                          </Badge>
                        )}
                        {table.isAddon && (
                          <Badge
                            variant="secondary"
                            className="text-[11px] px-1.5 py-0 h-4 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400"
                          >
                            {table.addonName || __('Add-on', 'wp-statistics')}
                          </Badge>
                        )}
                      </div>
                    </TableCell>
                    <TableCell className="text-muted-foreground">{table.description || '-'}</TableCell>
                    <TableCell className="text-right font-mono">{table.records.toLocaleString()}</TableCell>
                    <TableCell className="text-right text-muted-foreground">{table.size}</TableCell>
                    <TableCell>
                      <Badge variant="outline">{table.engine}</Badge>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>

      {/* Options & Transients Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="flex items-center gap-2">
              <Settings className="h-5 w-5" />
              {__('Options & Transients', 'wp-statistics')}
            </CardTitle>
            <CardDescription>{__('WordPress options, transients, and user meta used by WP Statistics.', 'wp-statistics')}</CardDescription>
          </div>
          <Button variant="outline" size="sm" onClick={fetchOptionsAndTransients} disabled={isLoadingOptionsTransients}>
            {isLoadingOptionsTransients ? (
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            ) : (
              <RefreshCw className="mr-2 h-4 w-4" />
            )}
            {__('Load Data', 'wp-statistics')}
          </Button>
        </CardHeader>
        <CardContent>
          {options.length === 0 && transients.length === 0 && userMeta.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              {__('Click "Load Data" to view options, transients, and user meta.', 'wp-statistics')}
            </p>
          ) : (
            <div className="space-y-6">
              {/* Options Section */}
              {options.length > 0 && (
                <div>
                  <h4 className="text-sm font-medium mb-3 flex items-center gap-2">
                    <Settings className="h-4 w-4" />
                    {__('Options', 'wp-statistics')} ({options.length})
                  </h4>
                  <div className="space-y-4">
                    {Object.entries(groupedOptions).map(([group, items]) => (
                      <div key={group} className="rounded-md border">
                        <div className="bg-muted/50 px-3 py-2 border-b">
                          <span className="text-xs font-medium text-muted-foreground">{getGroupLabel(group)}</span>
                        </div>
                        <div className="divide-y">
                          {items.map((opt, idx) => (
                            <div key={`${opt.key}-${idx}`} className="px-3 py-2 flex items-start gap-4">
                              <code className="text-xs bg-muted px-1.5 py-0.5 rounded shrink-0">{opt.key}</code>
                              <pre className="text-xs text-muted-foreground whitespace-pre-wrap break-all flex-1 max-h-24 overflow-auto">
                                {opt.value}
                              </pre>
                            </div>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Transients Section */}
              {transients.length > 0 && (
                <div>
                  <h4 className="text-sm font-medium mb-3 flex items-center gap-2">
                    <Clock className="h-4 w-4" />
                    {__('Transients', 'wp-statistics')} ({transients.length})
                  </h4>
                  <div className="rounded-md border divide-y">
                    {transients.map((trans, idx) => (
                      <div key={`${trans.name}-${idx}`} className="px-3 py-2 flex items-start gap-4">
                        <code className="text-xs bg-muted px-1.5 py-0.5 rounded shrink-0">{trans.name}</code>
                        <pre className="text-xs text-muted-foreground whitespace-pre-wrap break-all flex-1 max-h-24 overflow-auto">
                          {trans.value}
                        </pre>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {transients.length === 0 && options.length > 0 && (
                <p className="text-sm text-muted-foreground">{__('No transients found.', 'wp-statistics')}</p>
              )}

              {/* User Meta Section */}
              {userMeta.length > 0 && (
                <div>
                  <h4 className="text-sm font-medium mb-3 flex items-center gap-2">
                    <User className="h-4 w-4" />
                    {__('User Meta', 'wp-statistics')} ({userMeta.filter((m) => m.exists).length} {__('stored', 'wp-statistics')})
                  </h4>
                  <div className="rounded-md border divide-y">
                    {userMeta.map((meta, idx) => (
                      <div
                        key={`${meta.key}-${idx}`}
                        className={`px-3 py-2 flex items-start gap-4 ${meta.isLegacy ? 'opacity-60' : ''}`}
                      >
                        <div className="flex items-center gap-2 shrink-0">
                          <code className="text-xs bg-muted px-1.5 py-0.5 rounded">{meta.key}</code>
                          {meta.isLegacy && (
                            <Badge
                              variant="secondary"
                              className="text-[11px] px-1.5 py-0 h-4 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                            >
                              {__('Legacy', 'wp-statistics')}
                            </Badge>
                          )}
                        </div>
                        {meta.exists ? (
                          <pre className="text-xs text-muted-foreground whitespace-pre-wrap break-all flex-1 max-h-24 overflow-auto">
                            {meta.value}
                          </pre>
                        ) : (
                          <span className="text-xs text-muted-foreground italic">{__('Not set', 'wp-statistics')}</span>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}

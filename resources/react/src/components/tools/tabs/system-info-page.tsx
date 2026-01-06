import * as React from 'react'
import {
  Loader2,
  Database,
  Server,
  HardDrive,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  RefreshCw,
  Wrench,
} from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

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

interface SchemaCheckResult {
  status: 'success' | 'warning' | 'error' | 'unknown'
  issues: string[]
  errors: string[]
}

// Helper to get config
const getConfig = () => {
  const wpsReact = (window as any).wps_react
  return {
    ajaxUrl: wpsReact?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: wpsReact?.globals?.nonce || '',
  }
}

// Helper to call tools endpoint with sub_action
const callToolsApi = async (subAction: string, params: Record<string, string> = {}) => {
  const config = getConfig()
  const formData = new FormData()
  formData.append('wps_nonce', config.nonce)
  formData.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    formData.append(key, value)
  })

  const response = await fetch(`${config.ajaxUrl}?action=wp_statistics_tools`, {
    method: 'POST',
    body: formData,
  })
  return response.json()
}

export function SystemInfoPage() {
  const [tables, setTables] = React.useState<TableInfo[]>([])
  const [plugin, setPlugin] = React.useState<PluginInfo | null>(null)
  const [isLoading, setIsLoading] = React.useState(true)
  const [schemaStatus, setSchemaStatus] = React.useState<SchemaCheckResult | null>(null)
  const [isCheckingSchema, setIsCheckingSchema] = React.useState(false)
  const [isRepairingSchema, setIsRepairingSchema] = React.useState(false)
  const [statusMessage, setStatusMessage] = React.useState<{
    type: 'success' | 'error'
    message: string
  } | null>(null)

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
    } catch (error) {
      console.error('Failed to fetch system info:', error)
      setStatusMessage({
        type: 'error',
        message: 'Failed to load system information. Please refresh the page.',
      })
    } finally {
      setIsLoading(false)
    }
  }

  const checkSchema = async () => {
    setIsCheckingSchema(true)
    setStatusMessage(null)

    try {
      const data = await callToolsApi('schema_check')

      if (data.success) {
        setSchemaStatus(data.data)
      } else {
        setStatusMessage({
          type: 'error',
          message: data.data?.message || 'Failed to check schema.',
        })
      }
    } catch (error) {
      setStatusMessage({
        type: 'error',
        message: 'Failed to check schema. Please try again.',
      })
    } finally {
      setIsCheckingSchema(false)
    }
  }

  const repairSchema = async () => {
    setIsRepairingSchema(true)
    setStatusMessage(null)

    try {
      const data = await callToolsApi('schema_repair')

      if (data.success) {
        setStatusMessage({
          type: 'success',
          message: data.data?.message || 'Schema repair completed.',
        })
        // Re-check schema after repair
        await checkSchema()
        // Refresh system info to get updated table info
        await fetchSystemInfo()
      } else {
        setStatusMessage({
          type: 'error',
          message: data.data?.message || 'Failed to repair schema.',
        })
      }
    } catch (error) {
      setStatusMessage({
        type: 'error',
        message: 'Failed to repair schema. Please try again.',
      })
    } finally {
      setIsRepairingSchema(false)
    }
  }

  const getTotalRecords = () => {
    return tables.reduce((sum, table) => sum + (table.records || 0), 0)
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">Loading system information...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Status Message */}
      {statusMessage && (
        <div
          className={`rounded-lg border p-4 ${
            statusMessage.type === 'error'
              ? 'border-destructive/50 bg-destructive/10 text-destructive'
              : 'border-green-500/50 bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400'
          }`}
        >
          <div className="flex gap-2 items-center">
            {statusMessage.type === 'success' ? (
              <CheckCircle2 className="h-4 w-4" />
            ) : (
              <XCircle className="h-4 w-4" />
            )}
            <span>{statusMessage.message}</span>
          </div>
        </div>
      )}

      {/* Plugin Info Card */}
      {plugin && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Server className="h-5 w-5" />
              Plugin Information
            </CardTitle>
            <CardDescription>
              Current versions and environment details.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">Plugin Version</p>
                <p className="font-medium">{plugin.version}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">Database Version</p>
                <p className="font-medium">{plugin.db_version}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">PHP Version</p>
                <p className="font-medium">{plugin.php}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">MySQL Version</p>
                <p className="font-medium">{plugin.mysql}</p>
              </div>
              <div className="space-y-1">
                <p className="text-sm text-muted-foreground">WordPress Version</p>
                <p className="font-medium">{plugin.wp}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Schema Health Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="flex items-center gap-2">
              <Wrench className="h-5 w-5" />
              Schema Health
            </CardTitle>
            <CardDescription>
              Check database schema for missing tables or columns.
            </CardDescription>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={checkSchema}
              disabled={isCheckingSchema}
            >
              {isCheckingSchema ? (
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              ) : (
                <RefreshCw className="mr-2 h-4 w-4" />
              )}
              Check Schema
            </Button>
            {schemaStatus && schemaStatus.status !== 'success' && (
              <Button
                size="sm"
                onClick={repairSchema}
                disabled={isRepairingSchema}
              >
                {isRepairingSchema ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <Wrench className="mr-2 h-4 w-4" />
                )}
                Repair
              </Button>
            )}
          </div>
        </CardHeader>
        <CardContent>
          {!schemaStatus ? (
            <p className="text-sm text-muted-foreground">
              Click "Check Schema" to verify your database structure.
            </p>
          ) : (
            <div className="space-y-4">
              <div className="flex items-center gap-2">
                {schemaStatus.status === 'success' && (
                  <>
                    <CheckCircle2 className="h-5 w-5 text-green-500" />
                    <span className="font-medium text-green-700 dark:text-green-400">
                      All tables and columns are present
                    </span>
                  </>
                )}
                {schemaStatus.status === 'warning' && (
                  <>
                    <AlertTriangle className="h-5 w-5 text-yellow-500" />
                    <span className="font-medium text-yellow-700 dark:text-yellow-400">
                      Some issues found
                    </span>
                  </>
                )}
                {schemaStatus.status === 'error' && (
                  <>
                    <XCircle className="h-5 w-5 text-destructive" />
                    <span className="font-medium text-destructive">
                      Schema errors detected
                    </span>
                  </>
                )}
              </div>
              {schemaStatus.issues && schemaStatus.issues.length > 0 && (
                <div className="rounded-md border p-3 bg-muted/50">
                  <p className="text-sm font-medium mb-2">Issues found:</p>
                  <ul className="text-sm text-muted-foreground space-y-1">
                    {schemaStatus.issues.map((issue, i) => (
                      <li key={i} className="flex items-start gap-2">
                        <span className="text-yellow-500 mt-0.5">*</span>
                        {issue}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Database Tables Card */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Database className="h-5 w-5" />
            Database Tables
          </CardTitle>
          <CardDescription>
            {tables.length} tables with {getTotalRecords().toLocaleString()} total records.
          </CardDescription>
        </CardHeader>
        <CardContent>
          {tables.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Database className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">No tables found</h3>
              <p className="text-sm text-muted-foreground">
                Database tables have not been created yet.
              </p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Table</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead className="text-right">Records</TableHead>
                  <TableHead className="text-right">Size</TableHead>
                  <TableHead>Engine</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {tables.map((table) => (
                  <TableRow key={table.key} className={table.isLegacy ? 'opacity-60' : ''}>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        <HardDrive className="h-4 w-4 text-muted-foreground" />
                        <code className="text-xs bg-muted px-1.5 py-0.5 rounded">
                          {table.key}
                        </code>
                        {table.isLegacy && (
                          <Badge variant="secondary" className="text-[10px] px-1.5 py-0 h-4 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            Legacy
                          </Badge>
                        )}
                        {table.isAddon && (
                          <Badge variant="secondary" className="text-[10px] px-1.5 py-0 h-4 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            {table.addonName || 'Add-on'}
                          </Badge>
                        )}
                      </div>
                    </TableCell>
                    <TableCell className="text-muted-foreground">
                      {table.description || '-'}
                    </TableCell>
                    <TableCell className="text-right font-mono">
                      {table.records.toLocaleString()}
                    </TableCell>
                    <TableCell className="text-right text-muted-foreground">
                      {table.size}
                    </TableCell>
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
    </div>
  )
}

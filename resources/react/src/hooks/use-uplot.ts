import * as React from 'react'
import uPlot from 'uplot'

interface UseUPlotOptions {
  options: () => Omit<uPlot.Options, 'width' | 'height'>
  data: uPlot.AlignedData
}

/**
 * Manages uPlot lifecycle: create on mount, update data, resize, destroy on unmount.
 */
export function useUPlot({ options, data }: UseUPlotOptions) {
  const containerRef = React.useRef<HTMLDivElement>(null)
  const chartRef = React.useRef<uPlot | null>(null)
  const optsFnRef = React.useRef(options)
  optsFnRef.current = options

  // Recreate chart when options factory identity changes (metrics toggled, timeframe changed)
  React.useEffect(() => {
    const el = containerRef.current
    if (!el) return

    const rect = el.getBoundingClientRect()
    const opts: uPlot.Options = {
      ...optsFnRef.current(),
      width: Math.floor(rect.width) || 300,
      height: Math.floor(rect.height) || 250,
    }

    const chart = new uPlot(opts, data, el)
    chartRef.current = chart

    const ro = new ResizeObserver((entries) => {
      const entry = entries[0]
      if (!entry) return
      const { width, height } = entry.contentRect
      if (width > 0 && height > 0) {
        chart.setSize({ width: Math.floor(width), height: Math.floor(height) })
      }
    })
    ro.observe(el)

    return () => {
      ro.disconnect()
      chart.destroy()
      chartRef.current = null
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps -- options identity drives recreation
  }, [options])

  // Update data without recreating chart
  React.useEffect(() => {
    chartRef.current?.setData(data, true)
  }, [data])

  return { containerRef, chartRef }
}

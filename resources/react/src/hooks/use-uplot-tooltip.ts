import * as React from 'react'
import type uPlot from 'uplot'

export interface UPlotTooltipState {
  visible: boolean
  idx: number | null
  left: number
  top: number
}

/**
 * Provides tooltip state driven by uPlot's setCursor hook.
 * Returns a plugin to pass into uPlot options and reactive tooltip state.
 */
export function useUPlotTooltip() {
  const [tooltip, setTooltip] = React.useState<UPlotTooltipState>({
    visible: false,
    idx: null,
    left: 0,
    top: 0,
  })

  const plugin = React.useMemo((): uPlot.Plugin => {
    return {
      hooks: {
        setCursor: [(u: uPlot) => {
          const { left, top, idx } = u.cursor
          if (left == null || left < 0 || idx == null) {
            setTooltip((prev) => (prev.visible ? { visible: false, idx: null, left: 0, top: 0 } : prev))
          } else {
            setTooltip((prev) => {
              if (prev.visible && prev.idx === idx && prev.left === left && prev.top === (top ?? 0)) return prev
              return { visible: true, idx, left: left!, top: top ?? 0 }
            })
          }
        }],
      },
    }
  }, [])

  return { tooltip, plugin }
}

import type { Declaration } from 'postcss'

export default function postcssImportantPlugin() {
  return {
    postcssPlugin: 'postcss-important-plugin',
    Declaration(decl: Declaration) {
      // Only process declarations that have !important
      if (!decl.important) {
        return
      }

      // Remove !important from CSS custom properties that start with --tw-
      if (decl.prop.startsWith('--tw-')) {
        decl.important = false
        return
      }

      // Remove !important from animation-related properties
      const animationProps = [
        'animation',
        'animation-name',
        'animation-duration',
        'animation-timing-function',
        'animation-delay',
        'animation-iteration-count',
        'animation-direction',
        'animation-fill-mode',
        'animation-play-state',
      ]

      if (animationProps.includes(decl.prop)) {
        decl.important = false
      }
    },
  }
}

postcssImportantPlugin.postcss = true

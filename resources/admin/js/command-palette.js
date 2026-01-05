/**
 * WP Statistics Command Palette Integration
 *
 * Registers WP Statistics navigation commands with WordPress's native
 * Command Palette (Cmd+K / Ctrl+K).
 *
 * @since 15.0.0
 */
(function () {
  'use strict';

  /**
   * Register all WP Statistics commands with the Command Palette.
   */
  function registerCommands() {
    // Check if WordPress command store is available
    if (
      typeof wp === 'undefined' ||
      typeof wp.data === 'undefined' ||
      typeof wp.commands === 'undefined'
    ) {
      return;
    }

    // Check if command data is available
    if (
      typeof window.wpStatisticsCommands === 'undefined' ||
      !window.wpStatisticsCommands.commands
    ) {
      return;
    }

    var dispatch = wp.data.dispatch;
    var commandsStore = wp.commands.store;
    var commands = window.wpStatisticsCommands.commands;

    commands.forEach(function (command) {
      try {
        dispatch(commandsStore).registerCommand({
          name: command.name,
          label: command.label,
          // Skip icon to avoid React version conflicts
          callback: function (params) {
            window.location.href = command.url;
            if (params && typeof params.close === 'function') {
              params.close();
            }
          },
        });
      } catch (error) {
        // Silently fail if command registration fails
        console.warn('WP Statistics: Failed to register command', command.name, error);
      }
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerCommands);
  } else {
    // DOM is already ready, register commands after a short delay
    // to ensure WordPress scripts are fully loaded
    setTimeout(registerCommands, 100);
  }
})();
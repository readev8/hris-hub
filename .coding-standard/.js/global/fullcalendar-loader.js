/**
 * FullCalendar Local Loader
 * Loads FullCalendar core and plugins in correct order
 *
 * This script ensures:
 * 1. Core loads first
 * 2. Plugins load after core is ready
 * 3. Plugins are properly registered
 */

(function() {
    'use strict';

    var FullCalendarLoader = {
        isLoaded: false,
        isLoading: false,
        callbacks: [],

        /**
         * Load FullCalendar with plugins
         * @param {Function} callback - Callback when loaded
         */
        load: function(callback) {
            // If already loaded, call callback immediately
            if (this.isLoaded) {
                callback();
                return;
            }

            // Add callback to queue
            this.callbacks.push(callback);

            // If already loading, don't start again
            if (this.isLoading) {
                return;
            }

            this.isLoading = true;

            console.log('[FullCalendar Loader] Starting load...');

            // Load scripts in order
            this.loadCore(this.onCoreLoaded.bind(this));
        },

        /**
         * Load FullCalendar core
         * @param {Function} callback - Callback when core loaded
         */
        loadCore: function(callback) {
            var script = document.createElement('script');
            script.src = base_url + 'public/assets/plugin/fullcalendar/packages/core/index.global.min.js';
            script.async = false;  // Important: load synchronously

            script.onload = function() {
                console.log('[FullCalendar Loader] Core loaded');
                callback();
            };

            script.onerror = function() {
                console.error('[FullCalendar Loader] Failed to load core');
            };

            document.head.appendChild(script);
        },

        /**
         * Called when core is loaded
         */
        onCoreLoaded: function() {
            console.log('[FullCalendar Loader] Core ready, loading plugins...');

            // Wait for FullCalendar to be available
            var checkInterval = setInterval(function() {
                if (typeof FullCalendar !== 'undefined') {
                    clearInterval(checkInterval);
                    this.loadPlugins();
                }
            }.bind(this), 50);
        },

        /**
         * Load plugins
         */
        loadPlugins: function() {
            console.log('[FullCalendar Loader] Loading DayGrid plugin...');

            var script = document.createElement('script');
            script.src = base_url + 'public/assets/plugin/fullcalendar/packages/daygrid/index.global.min.js';
            script.async = false;

            script.onload = function() {
                console.log('[FullCalendar Loader] DayGrid plugin loaded');

                // Wait for plugin to be registered
                FullCalendarLoader.waitForPlugin();
            };

            script.onerror = function() {
                console.error('[FullCalendar Loader] Failed to load DayGrid plugin');
            };

            document.head.appendChild(script);
        },

        /**
         * Wait for plugin to be registered
         */
        waitForPlugin: function() {
            var maxAttempts = 50;  // 5 seconds
            var attempts = 0;

            var checkInterval = setInterval(function() {
                attempts++;

                // Check if plugin is registered
                var isPluginLoaded = false;

                if (typeof FullCalendar !== 'undefined') {
                    // Method 1: Check DayGridPlugin property
                    if (typeof FullCalendar.DayGridPlugin !== 'undefined') {
                        isPluginLoaded = true;
                    }
                    // Method 2: Check globalPlugins array
                    else if (FullCalendar.globalPlugins && FullCalendar.globalPlugins.length > 0) {
                        console.log('[FullCalendar Loader] Found', FullCalendar.globalPlugins.length, 'plugins in globalPlugins array');
                        isPluginLoaded = true;

                        // Find DayGrid plugin and export it
                        var dayGridPlugin = FullCalendar.globalPlugins.find(function(plugin) {
                            return plugin.name && plugin.name.indexOf('daygrid') !== -1;
                        });

                        if (dayGridPlugin) {
                            console.log('[FullCalendar Loader] Found DayGrid plugin in globalPlugins:', dayGridPlugin.name);
                            // Export to global scope for easier access
                            FullCalendar.DayGridPlugin = dayGridPlugin;
                        }
                    }
                }

                if (isPluginLoaded) {
                    clearInterval(checkInterval);
                    console.log('[FullCalendar Loader] ✓ All plugins loaded successfully');
                    console.log('[FullCalendar Loader] DayGridPlugin available:', typeof FullCalendar.DayGridPlugin);

                    FullCalendarLoader.isLoaded = true;
                    FullCalendarLoader.isLoading = false;

                    // Call all queued callbacks
                    FullCalendarLoader.callbacks.forEach(function(cb) {
                        cb();
                    });

                    // Clear callbacks
                    FullCalendarLoader.callbacks = [];

                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    console.warn('[FullCalendar Loader] ⚠ Plugins not loaded, but continuing anyway');
                    console.log('[FullCalendar Loader] FullCalendar:', typeof FullCalendar);
                    console.log('[FullCalendar Loader] DayGridPlugin:', typeof FullCalendar !== 'undefined' ? typeof FullCalendar.DayGridPlugin : 'N/A');
                    console.log('[FullCalendar Loader] globalPlugins:', FullCalendar && FullCalendar.globalPlugins ? FullCalendar.globalPlugins.length : 'N/A');

                    // Still mark as loaded (with degraded functionality)
                    FullCalendarLoader.isLoaded = true;
                    FullCalendarLoader.isLoading = false;

                    // Call callbacks anyway (calendar will work without plugin)
                    FullCalendarLoader.callbacks.forEach(function(cb) {
                        cb();
                    });

                    FullCalendarLoader.callbacks = [];
                }
            }, 100);
        }
    };

    // Export to global scope
    window.FullCalendarLoader = FullCalendarLoader;

    console.log('[FullCalendar Loader] Ready');

})();

/**
 * Widget runtime for yii2-extensions/filepond.
 *
 * Registers FilePond plugins once per page and keeps one FilePond instance per input id, so several widgets can
 * coexist on the same page with independent options.
 */
(function (window, document) {
    'use strict';

    const registeredPlugins = new Set();
    const ponds = new Map();
    const runtime = window.yii2FilePond || {};

    function registerPlugins(names) {
        names.forEach((name) => {
            if (registeredPlugins.has(name)) {
                return;
            }

            const plugin = window[name];

            if (plugin === undefined) {
                throw new Error(`FilePond plugin "${name}" is not loaded.`);
            }

            window.FilePond.registerPlugin(plugin);
            registeredPlugins.add(name);
        });
    }

    /**
     * Creates a FilePond instance on the input with the given id.
     *
     * @param {string} id Input element id.
     * @param {string[]} plugins Global plugin identifiers to register before creation.
     * @param {Object} options FilePond instance options.
     * @returns {Object} FilePond instance.
     */
    runtime.create = function (id, plugins, options) {
        const input = document.getElementById(id);

        if (input === null) {
            throw new Error(`FilePond input "${id}" was not found.`);
        }

        registerPlugins(plugins);
        runtime.destroy(id);

        const pond = window.FilePond.create(input, options);

        ponds.set(id, pond);

        return pond;
    };

    /**
     * Returns the FilePond instance created for the given input id, or `null`.
     *
     * @param {string} id Input element id.
     * @returns {Object|null} FilePond instance.
     */
    runtime.get = function (id) {
        return ponds.get(id) || null;
    };

    /**
     * Destroys the FilePond instance created for the given input id.
     *
     * @param {string} id Input element id.
     * @returns {boolean} Whether an instance was destroyed.
     */
    runtime.destroy = function (id) {
        const pond = ponds.get(id);

        if (pond === undefined) {
            return false;
        }

        pond.destroy();
        ponds.delete(id);

        return true;
    };

    window.yii2FilePond = runtime;
})(window, document);

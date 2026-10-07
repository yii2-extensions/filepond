// @ts-check

/** @type {import("@stryker-mutator/api/core").PartialStrykerOptions} */
const config = {
    ignorePatterns: ['/internal', '/reports', '/runtime', '/vendor'],
    mutate: ['src/asset/cropper/filepond-cropper.js', 'src/asset/widget/filepond-widget.js'],
    testRunner: 'vitest',
    vitest: {
        configFile: 'vitest.config.mjs',
    },
    concurrency: 4,
    reporters: ['clear-text', 'progress', 'html'],
    thresholds: {
        high: 100,
        low: 100,
        break: 100,
    },
};

export default config;

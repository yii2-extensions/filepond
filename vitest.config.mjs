import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        coverage: {
            provider: 'v8',
            include: ['src/asset/**/*.js'],
            exclude: ['src/asset/**/*.min.js'],
            reporter: ['text'],
            reportsDirectory: 'runtime/coverage-js',
            thresholds: {
                branches: 100,
                functions: 100,
                lines: 100,
            },
        },
        environment: 'jsdom',
        include: ['tests/js/**/*.test.js'],
        isolate: true,
        pool: 'threads',
    },
});

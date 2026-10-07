/**
 * Writes minified copies (`*.min.js`, `*.min.css`) next to the package's own scripts and stylesheets.
 *
 * The sources are plain browser scripts, so the build only minifies; it does not bundle. Run with `npm run build`.
 */
import { readFile, writeFile } from 'node:fs/promises';
import { transform } from 'esbuild';

const SOURCES = [
    'src/asset/cropper/filepond-cropper.css',
    'src/asset/cropper/filepond-cropper.js',
    'src/asset/widget/filepond-widget.css',
    'src/asset/widget/filepond-widget.js',
];

for (const source of SOURCES) {
    const loader = source.endsWith('.css') ? 'css' : 'js';
    const target = source.replace(/\.(css|js)$/, '.min.$1');
    const { code } = await transform(await readFile(source, 'utf8'), {
        legalComments: 'none',
        loader,
        minify: true,
        target: loader === 'js' ? 'es2020' : 'esnext',
    });

    await writeFile(target, code);
    console.log(`${source} -> ${target} (${code.length} bytes)`);
}

import { readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { JSDOM } from 'jsdom';
import { afterEach, describe, expect, test, vi } from 'vitest';

const windows = [];
const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');

function browser(plugins = []) {
  const dom = new JSDOM('<!doctype html><form><input type="file" name="avatar" id="avatar"></form>', {
    pretendToBeVisual: true,
    runScripts: 'outside-only',
    url: 'https://example.invalid/',
  });
  const page = dom.window;

  windows.push(page);
  page.CSS = { supports: () => true };
  page.matchMedia = () => ({ matches: false, addListener() {}, removeListener() {} });
  page.URL.createObjectURL = () => 'blob:integration-test';
  page.URL.revokeObjectURL = () => {};

  for (const file of [
    'node_modules/filepond/dist/filepond.js',
    ...plugins,
    'src/asset/widget/filepond-widget.js',
    'src/asset/cropper/filepond-cropper.js',
  ]) {
    page.eval(readFileSync(resolve(root, file), 'utf8'));
  }

  return page;
}

afterEach(() => {
  windows.splice(0).forEach((page) => page.close());
});

describe('real FilePond integration', () => {
  test('recreates and destroys an input without losing its name or delayed DOM errors', async () => {
    const page = browser();
    const original = page.document.getElementById('avatar');
    const errors = [];

    page.addEventListener('error', (event) => {
      errors.push(event.message);
      event.preventDefault();
    });

    const first = page.yii2FilePond.create('avatar', [], { credits: false });

    expect(first.name, 'Initial creation must use the input name.').toBe('avatar');
    expect(page.document.getElementById('avatar').tagName, 'FilePond must replace the input.').toBe('DIV');

    const second = page.yii2FilePond.create('avatar', [], { credits: false });

    await new Promise((resolve) => setTimeout(resolve, 100));

    expect(second.name, 'Recreation must use the restored original input.').toBe('avatar');
    expect(errors, 'Delayed destruction must not target a detached root.').toEqual([]);
    expect(page.document.querySelectorAll('.filepond--root'), 'Only the replacement may remain.').toHaveLength(1);
    expect(page.yii2FilePond.destroy('avatar'), 'Destroy must succeed.').toBe(true);
    expect(page.document.getElementById('avatar'), 'The original input must be restored synchronously.').toBe(
      original,
    );

    await new Promise((resolve) => setTimeout(resolve, 50));

    expect(errors, 'The delayed destroy event must remain harmless.').toEqual([]);
    expect(page.document.querySelectorAll('.filepond--root'), 'Destroy must remove the replacement.').toHaveLength(
      0,
    );
  });

  test('advances the real instant-edit queue when Cropper.js is missing', async () => {
    const page = browser(['node_modules/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js']);
    const messages = [];
    const editor = page.yii2FilePond.cropper.createEditor();

    page.console.error = (message) => messages.push(message);

    const pond = page.yii2FilePond.create('avatar', ['FilePondPluginImageEdit'], {
      allowImageEdit: true,
      allowImagePreview: false,
      allowMultiple: true,
      credits: false,
      imageEditEditor: editor,
      imageEditInstantEdit: true,
    });

    pond.addFile(new page.File(['first'], 'first.png', { type: 'image/png' })).catch(() => {});
    pond.addFile(new page.File(['second'], 'second.png', { type: 'image/png' })).catch(() => {});

    await vi.waitFor(() => {
      expect(messages, 'Both queued images must reach the unavailable editor.').toHaveLength(2);
      expect(pond.getFiles(), 'Cancelled items must leave the queue.').toHaveLength(0);
    });
  });

  test('submits a local source identifier rather than a new encoded payload', async () => {
    const page = browser(['node_modules/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js']);

    page.yii2FilePond.create('avatar', ['FilePondPluginFileEncode'], {
      credits: false,
      files: [
        {
          source: 'stored-avatar.png',
          options: { type: 'local', file: { name: 'stored-avatar.png', size: 2048, type: 'image/png' } },
        },
      ],
    });

    await vi.waitFor(() => {
      const entries = Array.from(new page.FormData(page.document.querySelector('form')).entries());

      expect(entries, 'The application must authorize the posted stored identifier separately.').toEqual([
        ['avatar', 'stored-avatar.png'],
      ]);
    });
  });
});

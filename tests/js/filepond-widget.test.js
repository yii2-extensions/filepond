import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

/**
 * Unit tests for the `yii2FilePond` widget runtime (`src/asset/widget/filepond-widget.js`).
 */
async function loadRuntime() {
    vi.resetModules();

    await import('../../src/asset/widget/filepond-widget.js');

    return window.yii2FilePond;
}

function createPond() {
    return { destroy: vi.fn() };
}

describe('filepond-widget', () => {
    beforeEach(() => {
        document.body.innerHTML = '<input id="avatar" type="file"><input id="gallery" type="file">';
        window.FilePond = {
            create: vi.fn(() => createPond()),
            registerPlugin: vi.fn(),
        };
        window.FilePondPluginFileEncode = { name: 'encode' };
        window.FilePondPluginImagePreview = { name: 'preview' };
    });

    afterEach(() => {
        delete window.yii2FilePond;
        delete window.FilePond;
        delete window.FilePondPluginFileEncode;
        delete window.FilePondPluginImagePreview;
        document.body.innerHTML = '';
    });

    test('preserves members registered before the runtime loads', async () => {
        const cropper = { createEditor: vi.fn() };

        window.yii2FilePond = { cropper };

        const runtime = await loadRuntime();

        expect(runtime.cropper, 'Existing members must survive.').toBe(cropper);
        expect(typeof runtime.create, 'Runtime must add `create`.').toBe('function');
    });

    test('creates an instance on the input with the given options', async () => {
        const runtime = await loadRuntime();
        const options = { allowMultiple: true };

        const pond = runtime.create('avatar', [], options);

        expect(window.FilePond.create).toHaveBeenCalledExactlyOnceWith(document.getElementById('avatar'), options);
        expect(window.FilePond.create.mock.results[0].value, 'Instance must be returned.').toBe(pond);
        expect(runtime.get('avatar'), 'Instance must be stored by id.').toBe(pond);
    });

    test('keeps independent instances per input id', async () => {
        const runtime = await loadRuntime();

        const avatar = runtime.create('avatar', [], {});
        const gallery = runtime.create('gallery', [], {});

        expect(avatar, 'Each input must get its own instance.').not.toBe(gallery);
        expect(runtime.get('avatar'), 'First instance must stay stored.').toBe(avatar);
        expect(runtime.get('gallery'), 'Second instance must be stored.').toBe(gallery);
        expect(avatar.destroy, 'Creating another id must not destroy.').not.toHaveBeenCalled();
    });

    test('registers each plugin once per page', async () => {
        const runtime = await loadRuntime();

        runtime.create('avatar', ['FilePondPluginFileEncode', 'FilePondPluginImagePreview'], {});
        runtime.create('gallery', ['FilePondPluginFileEncode'], {});

        expect(window.FilePond.registerPlugin.mock.calls).toEqual([
            [window.FilePondPluginFileEncode],
            [window.FilePondPluginImagePreview],
        ]);
    });

    test('registers plugins before creating the instance', async () => {
        const runtime = await loadRuntime();
        const order = [];

        window.FilePond.registerPlugin.mockImplementation(() => order.push('register'));
        window.FilePond.create.mockImplementation(() => {
            order.push('create');

            return createPond();
        });

        runtime.create('avatar', ['FilePondPluginFileEncode'], {});

        expect(order, 'Plugins must be registered first.').toEqual(['register', 'create']);
    });

    test('replaces the instance when the same id is created again', async () => {
        const runtime = await loadRuntime();

        const first = runtime.create('avatar', [], {});
        const second = runtime.create('avatar', [], {});

        expect(first.destroy, 'Previous instance must be destroyed.').toHaveBeenCalledOnce();
        expect(second.destroy, 'New instance must stay alive.').not.toHaveBeenCalled();
        expect(runtime.get('avatar'), 'New instance must replace the old one.').toBe(second);
    });

    test('throws when the input does not exist', async () => {
        const runtime = await loadRuntime();

        expect(() => runtime.create('missing', ['FilePondPluginFileEncode'], {})).toThrow(
            new Error('FilePond input "missing" was not found.'),
        );
        expect(window.FilePond.registerPlugin, 'Nothing must be registered.').not.toHaveBeenCalled();
        expect(window.FilePond.create, 'Nothing must be created.').not.toHaveBeenCalled();
    });

    test('throws when a plugin global is not loaded', async () => {
        const runtime = await loadRuntime();

        expect(() => runtime.create('avatar', ['FilePondPluginMissing'], {})).toThrow(
            new Error('FilePond plugin "FilePondPluginMissing" is not loaded.'),
        );
        expect(window.FilePond.registerPlugin, 'Missing plugin must not register.').not.toHaveBeenCalled();
        expect(window.FilePond.create, 'Nothing must be created.').not.toHaveBeenCalled();
    });

    test('returns `null` for an unknown id', async () => {
        const runtime = await loadRuntime();

        expect(runtime.get('avatar'), 'Unknown id must yield `null`.').toBeNull();
    });

    test('destroys a created instance', async () => {
        const runtime = await loadRuntime();
        const pond = runtime.create('avatar', [], {});

        expect(runtime.destroy('avatar'), 'Destroy must report success.').toBe(true);
        expect(pond.destroy, 'Instance must be destroyed.').toHaveBeenCalledOnce();
        expect(runtime.get('avatar'), 'Instance must be forgotten.').toBeNull();
        expect(runtime.destroy('avatar'), 'Second destroy must report nothing to do.').toBe(false);
    });

    test('returns `false` when destroying an unknown id', async () => {
        const runtime = await loadRuntime();

        expect(runtime.destroy('avatar'), 'Unknown id must yield `false`.').toBe(false);
    });
});

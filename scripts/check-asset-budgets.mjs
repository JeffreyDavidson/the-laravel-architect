import { readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';

const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));

const budgets = [
    {
        entry: 'resources/css/app.css',
        label: 'Public stylesheet',
        maxGzipBytes: 23 * 1024,
    },
    {
        entry: 'resources/css/pages/home-entry.css',
        label: 'Homepage stylesheet',
        maxGzipBytes: 5 * 1024,
    },
    {
        entry: 'resources/css/pages/podcast-entry.css',
        label: 'Podcast stylesheet',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/app.js',
        label: 'Public JavaScript',
        maxGzipBytes: 4 * 1024,
    },
    {
        entry: 'resources/js/alpine.js',
        label: 'On-demand Alpine runtime',
        maxGzipBytes: 20 * 1024,
    },
    {
        entry: 'resources/js/pages/about.js',
        label: 'About page interaction',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/blog.js',
        label: 'Blog page interaction',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/podcast.js',
        label: 'Podcast page interaction',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/home.js',
        label: 'Homepage loader',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/architecture-scene.js',
        label: 'Lazy architecture scene',
        maxGzipBytes: 135 * 1024,
    },
    {
        entry: 'resources/css/prism.css',
        label: 'Code highlighting stylesheet',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/prism.js',
        label: 'Code highlighting JavaScript',
        maxGzipBytes: 14 * 1024,
    },
    {
        entry: 'resources/css/filament/admin/theme.css',
        label: 'Filament admin theme',
        maxGzipBytes: 68 * 1024,
    },
];

let failed = false;

for (const budget of budgets) {
    const asset = manifest[budget.entry];

    if (! asset) {
        throw new Error(`Vite manifest entry not found: ${budget.entry}`);
    }

    const contents = readFileSync(`public/build/${asset.file}`);
    const gzipBytes = gzipSync(contents, { level: 9 }).length;
    const gzipKilobytes = (gzipBytes / 1024).toFixed(1);
    const limitKilobytes = (budget.maxGzipBytes / 1024).toFixed(1);

    console.log(`${budget.label}: ${gzipKilobytes} KiB gzip (limit ${limitKilobytes} KiB)`);

    if (gzipBytes > budget.maxGzipBytes) {
        failed = true;
    }
}

if (failed) {
    console.error('Asset budget exceeded. Review the generated bundle before increasing the limit.');
    process.exitCode = 1;
}

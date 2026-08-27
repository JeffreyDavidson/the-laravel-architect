import { readdirSync, readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';

const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));

const publicImageBudgets = [
    { file: 'public/images/favicon-16x16.png', label: '16px favicon', maxBytes: 2 * 1024 },
    { file: 'public/images/favicon-180x180.png', label: 'Apple touch icon', maxBytes: 40 * 1024 },
    { file: 'public/images/favicon-192x192.png', label: '192px web app icon', maxBytes: 44 * 1024 },
    { file: 'public/images/favicon-32x32.png', label: '32px favicon', maxBytes: 4 * 1024 },
    { file: 'public/images/favicon-512x512.png', label: '512px web app icon', maxBytes: 128 * 1024 },
    { file: 'public/images/logo-color-128.webp', label: 'Global logo', maxBytes: 20 * 1024 },
    { file: 'public/images/logo-color-black-bg.png', label: 'Social sharing image', maxBytes: 400 * 1024 },
];

const allowedPublicImages = new Set(
    publicImageBudgets.map(({ file }) => file.replace('public/images/', '')),
);

const unexpectedPublicImages = readdirSync('public/images')
    .filter((file) => ! allowedPublicImages.has(file));

if (unexpectedPublicImages.length > 0) {
    console.error(`Unexpected files in public/images: ${unexpectedPublicImages.join(', ')}`);
    console.error('Use resources/images with Vite for build-managed images, or explicitly allow stable direct-URL assets.');
    process.exit(1);
}

const budgets = [
    ...publicImageBudgets,
    {
        entry: 'resources/css/app.css',
        label: 'Public stylesheet',
        maxGzipBytes: 17 * 1024,
    },
    {
        entry: 'resources/fonts/empera/Empera-Regular.woff2',
        label: 'Brand font',
        maxGzipBytes: 8 * 1024,
    },
    {
        entry: 'resources/css/pages/home-entry.css',
        label: 'Homepage stylesheet',
        maxGzipBytes: 5 * 1024,
    },
    {
        entry: 'resources/css/pages/about-entry.css',
        label: 'About stylesheet',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/css/pages/listings-entry.css',
        label: 'Blog and projects stylesheet',
        maxGzipBytes: 1 * 1024,
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
        entry: 'resources/js/pages/about.js',
        label: 'About page interaction',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/blog.js',
        label: 'Blog article interaction',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/blog-index.js',
        label: 'Blog index filtering',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/contact.js',
        label: 'Contact verification loader',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/podcast.js',
        label: 'Podcast page interaction',
        maxGzipBytes: 3 * 1024,
    },
    {
        entry: 'resources/js/pages/home.js',
        label: 'Homepage loader',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/pages/architecture-scene.js',
        label: 'Lazy architecture scene',
        maxGzipBytes: 4 * 1024,
    },
    {
        entry: 'resources/css/prism.css',
        label: 'Code highlighting stylesheet',
        maxGzipBytes: 2 * 1024,
    },
    {
        entry: 'resources/js/prism.js',
        label: 'Lazy code highlighting runtime',
        maxGzipBytes: 14 * 1024,
    },
    {
        entry: 'resources/images/avatar-320.webp',
        label: 'About portrait (320px)',
        maxBytes: 24 * 1024,
    },
    {
        entry: 'resources/images/avatar-640.webp',
        label: 'About portrait (640px)',
        maxBytes: 42 * 1024,
    },
    {
        entry: 'resources/images/podcast-coffee-logo-128.webp',
        label: 'Coffee podcast artwork (128px)',
        maxBytes: 4 * 1024,
    },
    {
        entry: 'resources/images/podcast-coffee-logo-320.webp',
        label: 'Coffee podcast artwork (320px)',
        maxBytes: 12 * 1024,
    },
    {
        entry: 'resources/images/podcast-coffee-logo-512.webp',
        label: 'Coffee podcast artwork (512px)',
        maxBytes: 20 * 1024,
    },
    {
        entry: 'resources/images/podcast-cloudy-logo-128.webp',
        label: 'Cloudy podcast artwork (128px)',
        maxBytes: 5 * 1024,
    },
    {
        entry: 'resources/images/podcast-cloudy-logo-320.webp',
        label: 'Cloudy podcast artwork (320px)',
        maxBytes: 15 * 1024,
    },
    {
        entry: 'resources/images/podcast-cloudy-logo-512.webp',
        label: 'Cloudy podcast artwork (512px)',
        maxBytes: 23 * 1024,
    },
    {
        entry: 'resources/css/filament/admin/theme.css',
        label: 'Filament admin theme',
        maxGzipBytes: 68 * 1024,
    },
    {
        entry: 'resources/js/filament/admin.js',
        label: 'Filament admin interaction',
        maxGzipBytes: 2 * 1024,
    },
];

let failed = false;

for (const budget of budgets) {
    const asset = budget.entry ? manifest[budget.entry] : null;

    if (budget.entry && ! asset) {
        throw new Error(`Vite manifest entry not found: ${budget.entry}`);
    }

    const path = budget.file ?? `public/build/${asset.file}`;
    const contents = readFileSync(path);
    const measuredBytes = budget.maxBytes === undefined
        ? gzipSync(contents, { level: 9 }).length
        : contents.length;
    const maxBytes = budget.maxBytes ?? budget.maxGzipBytes;
    const measuredKilobytes = (measuredBytes / 1024).toFixed(1);
    const limitKilobytes = (maxBytes / 1024).toFixed(1);
    const measurement = budget.maxBytes ? 'file' : 'gzip';

    console.log(`${budget.label}: ${measuredKilobytes} KiB ${measurement} (limit ${limitKilobytes} KiB)`);

    if (measuredBytes > maxBytes) {
        failed = true;
    }
}

if (failed) {
    console.error('Asset budget exceeded. Review the generated bundle before increasing the limit.');
    process.exitCode = 1;
}

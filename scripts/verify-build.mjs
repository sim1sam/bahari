import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const buildDir = path.join(root, 'public', 'build');
const manifestPath = path.join(buildDir, 'manifest.json');
const assetsDir = path.join(buildDir, 'assets');

function fail(message) {
    console.error(`\n[build] ERROR: ${message}\n`);
    process.exit(1);
}

if (!fs.existsSync(manifestPath)) {
    fail('public/build/manifest.json not found. Run: npm run build');
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const requiredFiles = new Set(['manifest.json', 'fonts-manifest.json']);

for (const entry of Object.values(manifest)) {
    if (entry?.file) {
        requiredFiles.add(entry.file);
    }
}

const missing = [];

for (const relativeFile of requiredFiles) {
    const absolute = path.join(buildDir, ...relativeFile.split('/'));

    if (!fs.existsSync(absolute)) {
        missing.push(relativeFile);
    }
}

if (!fs.existsSync(assetsDir)) {
    fail('public/build/assets folder is missing.');
}

const assetFiles = fs.readdirSync(assetsDir);

if (assetFiles.length === 0) {
    fail('public/build/assets is empty.');
}

if (missing.length > 0) {
    fail(`Missing build files:\n- ${missing.join('\n- ')}`);
}

const cssEntry = manifest['resources/css/app.css']?.file;
const jsEntry = manifest['resources/js/app.js']?.file;

console.log('[build] OK — production assets are ready.');
console.log(`[build] CSS: ${cssEntry}`);
console.log(`[build] JS:  ${jsEntry}`);
console.log(`[build] Assets: ${assetFiles.length} files in public/build/assets/`);
console.log('[build] Upload the entire public/build/ folder to your server.');

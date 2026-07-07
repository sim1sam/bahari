import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { execSync } from 'node:child_process';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const buildDir = path.join(root, 'public', 'build');
const zipPath = path.join(root, 'deploy-build.zip');

if (!fs.existsSync(buildDir)) {
    console.error('[package] Run npm run build:deploy first.');
    process.exit(1);
}

if (fs.existsSync(zipPath)) {
    fs.unlinkSync(zipPath);
}

const isWindows = process.platform === 'win32';

if (isWindows) {
    execSync(
        `powershell -NoProfile -Command "Compress-Archive -Path '${buildDir.replace(/'/g, "''")}\\*' -DestinationPath '${zipPath.replace(/'/g, "''")}' -Force"`,
        { stdio: 'inherit' }
    );
} else {
    execSync(`cd "${buildDir}" && zip -r "${zipPath}" .`, { stdio: 'inherit' });
}

const sizeKb = Math.round(fs.statSync(zipPath).size / 1024);

console.log(`\n[package] Created deploy-build.zip (${sizeKb} KB)`);
console.log('[package] On server: extract into public/build/ (replace old files).');

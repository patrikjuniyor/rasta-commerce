import { cp, mkdir, rm, stat } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(scriptDirectory, '..');
const pluginSlug = 'rasta-commerce-core';
const pluginVersion = '1.0.0';
const releaseDirectory = path.join(root, 'release');
const sourceDirectory = path.join(root, 'plugins', pluginSlug);
const pluginDirectory = path.join(releaseDirectory, pluginSlug);
const zipFile = path.join(releaseDirectory, `${pluginSlug}-${pluginVersion}.zip`);
const includes = ['rasta-commerce-core.php', 'assets', 'includes', 'languages', 'README.md', 'LICENSE'];

await mkdir(releaseDirectory, { recursive: true });
await rm(pluginDirectory, { recursive: true, force: true });
await rm(zipFile, { force: true });
await mkdir(pluginDirectory, { recursive: true });

for (const item of includes) {
  const source = path.join(sourceDirectory, item);
  const destination = path.join(pluginDirectory, item);
  await stat(source);
  await cp(source, destination, { recursive: true });
}

execFileSync('zip', ['-qr', zipFile, pluginSlug], {
  cwd: releaseDirectory,
  stdio: 'inherit',
});

console.log(`Rasta Commerce Core package created: ${path.relative(root, zipFile)}`);

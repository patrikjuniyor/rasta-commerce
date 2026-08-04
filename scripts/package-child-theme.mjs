import { cp, mkdir, rm, stat } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(scriptDirectory, '..');
const childSlug = 'rasta-commerce-child';
const childVersion = '1.0.0';
const releaseDirectory = path.join(root, 'release');
const sourceDirectory = path.join(root, 'marketplace', 'theme-child', childSlug);
const childDirectory = path.join(releaseDirectory, childSlug);
const zipFile = path.join(releaseDirectory, `${childSlug}-${childVersion}.zip`);
const includes = ['style.css', 'functions.php', 'README.md', 'LICENSE'];

await mkdir(releaseDirectory, { recursive: true });
await rm(childDirectory, { recursive: true, force: true });
await rm(zipFile, { force: true });
await mkdir(childDirectory, { recursive: true });

for (const item of includes) {
  const source = path.join(sourceDirectory, item);
  const destination = path.join(childDirectory, item);
  await stat(source);
  await cp(source, destination, { recursive: true });
}

execFileSync('zip', ['-qr', zipFile, childSlug], {
  cwd: releaseDirectory,
  stdio: 'inherit',
});

console.log(`Child theme package created: ${path.relative(root, zipFile)}`);

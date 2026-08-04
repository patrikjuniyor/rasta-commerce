import { cp, mkdir, rm, stat } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(scriptDirectory, '..');
const releaseDirectory = path.join(root, 'release');
const themeDirectory = path.join(releaseDirectory, 'rasta-commerce');
const themeVersion = '1.3.0';
const zipFile = path.join(releaseDirectory, `rasta-commerce-${themeVersion}.zip`);
const includes = [
  'assets',
  'docs',
  'inc',
  'languages',
  'template-parts',
  'woocommerce',
  '404.php',
  'archive.php',
  'comments.php',
  'footer.php',
  'front-page.php',
  'functions.php',
  'header.php',
  'index.php',
  'page.php',
  'rtl.css',
  'search.php',
  'screenshot.png',
  'sidebar.php',
  'single.php',
  'style.css',
  'theme.json',
  'README.md',
  'LICENSE',
  'CHANGELOG.md',
];

await rm(releaseDirectory, { recursive: true, force: true });
await mkdir(themeDirectory, { recursive: true });

for (const item of includes) {
  const source = path.join(root, item);
  const destination = path.join(themeDirectory, item);
  await stat(source);
  await cp(source, destination, { recursive: true });
}

execFileSync('zip', ['-qr', zipFile, 'rasta-commerce'], {
  cwd: releaseDirectory,
  stdio: 'inherit',
});

console.log(`Theme package created: ${path.relative(root, zipFile)}`);

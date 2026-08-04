import { cp, mkdir, readdir, rm, stat, writeFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(scriptDirectory, '..');
const marketplaceDirectory = path.join(root, 'marketplace');
const buildDirectory = path.join(marketplaceDirectory, 'build');
const releaseDirectory = path.join(marketplaceDirectory, 'release');
const inputDirectory = path.join(marketplaceDirectory, 'input');
const mode = process.argv[2] || 'prepare';
const themeVersion = '1.4.0';
const packageName = 'RastaCommerce-RTLTheme';
const requiredProjectFiles = [
  path.join(root, 'release', `rasta-commerce-${themeVersion}.zip`),
  path.join(root, 'release', 'rasta-commerce-child-1.0.0.zip'),
  path.join(root, 'release', 'rasta-commerce-core-1.0.0.zip'),
  path.join(root, 'release', 'rasta-zarinpal-gateway-1.0.1.zip'),
  path.join(marketplaceDirectory, 'help.pdf'),
  path.join(marketplaceDirectory, 'import-files', 'rasta-commerce-starter.xml'),
];

const pathExists = async (candidate) => {
  try {
    await stat(candidate);
    return true;
  } catch {
    return false;
  }
};

const getSubmissionGaps = async () => {
  const demoDirectory = path.join(inputDirectory, 'demo');
  const assetsDirectory = path.join(inputDirectory, 'assets');
  const demoFiles = (await pathExists(demoDirectory)) ? await readdir(demoDirectory) : [];
  const assetFiles = (await pathExists(assetsDirectory)) ? await readdir(assetsDirectory) : [];
  const archive = demoFiles.find((file) => /\.(zip|dup)$/i.test(file));
  const infographicCount = assetFiles.filter((file) => /^infographic-.+\.(png|jpe?g|webp)$/i.test(file)).length;
  const gaps = [];

  if (!demoFiles.includes('installer.php')) {
    gaps.push('فایل marketplace/input/demo/installer.php وجود ندارد.');
  }
  if (!archive) {
    gaps.push('archive دموی Duplicator با پسوند .zip یا .dup وجود ندارد.');
  }
  if (!assetFiles.includes('icon-320x320.png')) {
    gaps.push('آیکون انسانی icon-320x320.png وجود ندارد.');
  }
  if (!assetFiles.some((file) => /^cover-2100x1040\.(png|jpe?g)$/i.test(file))) {
    gaps.push('کاور انسانی cover-2100x1040.png یا .jpg وجود ندارد.');
  }
  if (infographicCount < 5) {
    gaps.push('حداقل 5 فایل infographic-*.png/jpg برای معرفی محصول وجود ندارد.');
  }

  return { gaps, archive };
};

const verifyProjectFiles = async () => {
  const missing = [];
  for (const file of requiredProjectFiles) {
    if (!(await pathExists(file))) {
      missing.push(path.relative(root, file));
    }
  }
  if (missing.length) {
    throw new Error(`ابتدا npm run package و scripts/build-marketplace-help.sh را اجرا کنید. فایل‌های گمشده: ${missing.join(', ')}`);
  }
};

const createBasePackage = async (destination, includeSubmissionAssets = false) => {
  await rm(destination, { recursive: true, force: true });
  await mkdir(path.join(destination, 'Theme'), { recursive: true });
  await mkdir(path.join(destination, 'Plugins'), { recursive: true });
  await mkdir(path.join(destination, 'Import Files'), { recursive: true });

  await cp(path.join(root, 'release', `rasta-commerce-${themeVersion}.zip`), path.join(destination, 'Theme', `rasta-commerce-${themeVersion}.zip`));
  await cp(path.join(root, 'release', 'rasta-commerce-child-1.0.0.zip'), path.join(destination, 'Theme', 'rasta-commerce-child-1.0.0.zip'));
  await cp(path.join(root, 'release', 'rasta-commerce-core-1.0.0.zip'), path.join(destination, 'Plugins', 'rasta-commerce-core-1.0.0.zip'));
  await cp(path.join(root, 'release', 'rasta-zarinpal-gateway-1.0.1.zip'), path.join(destination, 'Plugins', 'rasta-zarinpal-gateway-1.0.1.zip'));
  await cp(path.join(marketplaceDirectory, 'import-files'), path.join(destination, 'Import Files'), { recursive: true });
  await cp(path.join(marketplaceDirectory, 'help.pdf'), path.join(destination, 'help.pdf'));
  await cp(path.join(marketplaceDirectory, 'requirements-checklist.md'), path.join(destination, 'requirements-checklist.md'));
  await cp(path.join(marketplaceDirectory, 'SOURCE-OWNERSHIP.md'), path.join(destination, 'SOURCE-OWNERSHIP.md'));

  if (includeSubmissionAssets) {
    await mkdir(path.join(destination, 'Submission Assets'), { recursive: true });
    await cp(path.join(inputDirectory, 'assets'), path.join(destination, 'Submission Assets'), { recursive: true });
  }
};

const zipDirectory = (directory, zipFile) => {
  execFileSync('zip', ['-qr', zipFile, path.basename(directory)], {
    cwd: path.dirname(directory),
    stdio: 'inherit',
  });
};

await verifyProjectFiles();
const submission = await getSubmissionGaps();

if (mode === 'validate') {
  if (submission.gaps.length) {
    console.error('پکیج هنوز برای ارسال به راست چین آماده نیست:');
    submission.gaps.forEach((gap) => console.error(`- ${gap}`));
    process.exit(1);
  }
  console.log('Marketplace validation: PASS');
  process.exit(0);
}

if (mode === 'prepare') {
  const destination = path.join(buildDirectory, `${packageName}-preparation`);
  await createBasePackage(destination);
  const message = submission.gaps.length
    ? `موارد باقی‌مانده پیش از ارسال:\n${submission.gaps.map((gap) => `- ${gap}`).join('\n')}\n\nاین فایل برای کنترل داخلی است و پکیج آماده ارسال نیست.\n`
    : 'همه فایل‌های محلی مورد نیاز موجود هستند. npm run marketplace:build را اجرا کنید.\n';
  await writeFile(path.join(destination, 'MISSING-BEFORE-SUBMISSION.md'), message);
  await mkdir(releaseDirectory, { recursive: true });
  const zipFile = path.join(releaseDirectory, `${packageName}-${themeVersion}-preparation.zip`);
  await rm(zipFile, { force: true });
  zipDirectory(destination, zipFile);
  console.log(`Marketplace preparation package created: ${path.relative(root, zipFile)}`);
  if (submission.gaps.length) {
    console.log('این خروجی فقط برای بازبینی است؛ ارسال به راست چین مجاز نیست.');
  }
  process.exit(0);
}

if (mode === 'build') {
  if (submission.gaps.length) {
    console.error('امکان ساخت پکیج نهایی وجود ندارد:');
    submission.gaps.forEach((gap) => console.error(`- ${gap}`));
    process.exit(1);
  }

  const destination = path.join(buildDirectory, packageName);
  await createBasePackage(destination, true);
  await mkdir(releaseDirectory, { recursive: true });
  const productZip = path.join(releaseDirectory, `${packageName}-${themeVersion}.zip`);
  await rm(productZip, { force: true });
  zipDirectory(destination, productZip);

  const demoDirectory = path.join(buildDirectory, `${packageName}-Demo`);
  await rm(demoDirectory, { recursive: true, force: true });
  await mkdir(demoDirectory, { recursive: true });
  await cp(path.join(inputDirectory, 'demo', 'installer.php'), path.join(demoDirectory, 'installer.php'));
  await cp(path.join(inputDirectory, 'demo', submission.archive), path.join(demoDirectory, submission.archive));
  const demoZip = path.join(releaseDirectory, `${packageName}-demo-duplicator.zip`);
  await rm(demoZip, { force: true });
  zipDirectory(demoDirectory, demoZip);

  console.log(`Marketplace submission package created: ${path.relative(root, productZip)}`);
  console.log(`Marketplace demo package created: ${path.relative(root, demoZip)}`);
  process.exit(0);
}

throw new Error(`Unknown marketplace mode: ${mode}`);

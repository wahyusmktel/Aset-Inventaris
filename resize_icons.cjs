const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SOURCE_IMAGE = 'C:\\Users\\TJ\\.gemini\\antigravity\\brain\\38551030-7caf-4c5b-b116-c6f8af356632\\.user_uploaded\\media_1788244477113.png';
const ASSETS_LOGO = 'c:\\Projects\\template-ui\\mobile_app\\assets\\images\\logo.png';
const RES_DIR = 'c:\\Projects\\template-ui\\mobile_app\\android\\app\\src\\main\\res';

// 1. Copy source to assets
fs.copyFileSync(SOURCE_IMAGE, ASSETS_LOGO);
console.log('Copied source to mobile_app assets logo:', ASSETS_LOGO);

// 2. Also copy to public/images
fs.copyFileSync(SOURCE_IMAGE, 'c:\\Projects\\template-ui\\public\\images\\app-logo.png');

const sizes = [
  { dir: 'mipmap-mdpi', size: 48 },
  { dir: 'mipmap-hdpi', size: 72 },
  { dir: 'mipmap-xhdpi', size: 96 },
  { dir: 'mipmap-xxhdpi', size: 144 },
  { dir: 'mipmap-xxxhdpi', size: 192 },
];

async function generateIcons() {
  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: "new",
    args: ['--no-sandbox']
  });

  const page = await browser.newPage();
  const bitmap = fs.readFileSync(SOURCE_IMAGE);
  const base64 = `data:image/png;base64,${bitmap.toString('base64')}`;

  for (const { dir, size } of sizes) {
    const targetDir = path.join(RES_DIR, dir);
    if (!fs.existsSync(targetDir)) {
      fs.mkdirSync(targetDir, { recursive: true });
    }

    await page.setViewport({ width: size, height: size });
    await page.setContent(`
      <!DOCTYPE html>
      <html>
      <body style="margin: 0; padding: 0; background: transparent; overflow: hidden; display: flex; align-items: center; justify-content: center; width: ${size}px; height: ${size}px;">
        <img src="${base64}" style="width: ${size}px; height: ${size}px; object-fit: contain;" />
      </body>
      </html>
    `);

    const iconPath = path.join(targetDir, 'ic_launcher.png');
    const roundIconPath = path.join(targetDir, 'ic_launcher_round.png');

    await page.screenshot({ path: iconPath, omitBackground: true });
    await page.screenshot({ path: roundIconPath, omitBackground: true });
    console.log(`Generated ${size}x${size} icon in ${dir}`);
  }

  await browser.close();
  console.log('All icons generated successfully!');
}

generateIcons();

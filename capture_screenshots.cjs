const puppeteer = require('puppeteer');
const path = require('path');

const ARTIFACT_DIR = 'C:\\Users\\TJ\\.gemini\\antigravity\\brain\\38551030-7caf-4c5b-b116-c6f8af356632';

async function capture() {
  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: "new",
    defaultViewport: { width: 1440, height: 900, deviceScaleFactor: 2 },
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();

  try {
    // 1. Login Page
    console.log('1. Capturing Login Screen...');
    await page.goto('http://localhost:8000/login', { waitUntil: 'networkidle2' });
    await page.screenshot({ path: path.join(ARTIFACT_DIR, 'screenshot_login.png'), fullPage: false });

    // 2. Perform Login as Super Admin
    console.log('2. Performing Login as Super Admin...');
    await page.type('input[type="email"]', 'admin@admin.com');
    await page.type('input[type="password"]', '12345678');
    
    const submitBtn = await page.$('button[type="submit"]');
    if (submitBtn) {
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        submitBtn.click()
      ]);
    }

    // 3. Dashboard Screen
    console.log('3. Capturing Dashboard Screen...');
    await new Promise(r => setTimeout(r, 2500));
    await page.screenshot({ path: path.join(ARTIFACT_DIR, 'screenshot_dashboard.png'), fullPage: false });

    // 4. Inventaris Barang Screen
    console.log('4. Capturing Inventory Items Screen...');
    await page.goto('http://localhost:8000/inventaris/items', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(ARTIFACT_DIR, 'screenshot_inventory.png'), fullPage: false });

    // 5. Open "Catat Barang" Modal
    console.log('5. Capturing Add Item Modal...');
    const buttons = await page.$$('button');
    for (const btn of buttons) {
      const text = await page.evaluate(el => el.textContent, btn);
      if (text && text.includes('Catat Barang')) {
        await btn.click();
        await new Promise(r => setTimeout(r, 1200));
        break;
      }
    }
    await page.screenshot({ path: path.join(ARTIFACT_DIR, 'screenshot_modal_input.png'), fullPage: false });

    // 6. User Management Screen
    console.log('6. Capturing User Management Screen...');
    await page.goto('http://localhost:8000/manajemen-pengguna', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(ARTIFACT_DIR, 'screenshot_users.png'), fullPage: false });

    // 7. Pakta Integritas Screen (Log out and log in as Anggota)
    console.log('7. Capturing Pakta Integritas Screen...');
    await page.goto('http://localhost:8000/pakta-integritas', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(ARTIFACT_DIR, 'screenshot_pakta_integritas.png'), fullPage: false });

    console.log('All screenshots captured successfully!');
  } catch (err) {
    console.error('Error during capture:', err);
  } finally {
    await browser.close();
  }
}

capture();

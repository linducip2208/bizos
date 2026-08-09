/**
 * BizOS Mobile Screenshot Capture
 * 
 * Logs in as admin, captures key Filament admin pages at iPhone viewport.
 * Saves PNGs to public/marketing/screens-mobile/
 */

const { chromium } = require('playwright');
const path = require('path');

const BASE = 'http://bizos.test';
const OUT_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens-mobile');
const EMAIL = 'admin@bizos.id';
const PASSWORD = 'password';

const pages = [
    { url: '/admin',           file: '01-dashboard.png',       auth: true },
    { url: '/admin/employees', file: '02-employees-list.png',  auth: true },
    { url: '/admin/attendances', file: '03-attendances.png',   auth: true },
    { url: '/admin/invoices',  file: '04-invoices.png',        auth: true },
    { url: '/admin/laporan-bisnis', file: '05-laporan-bisnis.png', auth: true },
];

(async () => {
    console.log('Launching browser (mobile viewport)...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 414, height: 896 },
        deviceScaleFactor: 2,
        isMobile: true,
    });
    const page = await context.newPage();

    // Login
    console.log('Logging in as admin...');
    await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('input[type="email"]', { timeout: 10000 });

    await page.fill('input[type="email"]', '');
    await page.type('input[type="email"]', EMAIL, { delay: 100 });
    await page.fill('input[type="password"]', '');
    await page.type('input[type="password"]', PASSWORD, { delay: 100 });

    await page.waitForTimeout(1000);

    await Promise.all([
        page.waitForURL('**/admin', { timeout: 15000 }).catch(() => {}),
        page.locator('button[type="submit"]').click(),
    ]);

    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle').catch(() => {});

    const url = page.url();
    if (url.includes('/login')) {
        console.error(`FAILED to login. Current URL: ${url}`);
        await browser.close();
        process.exit(1);
    }
    console.log(`Login success! URL: ${url}`);

    let success = 0;
    let failed = 0;

    for (const entry of pages) {
        const { url: pageUrl, file, auth } = entry;
        const fullUrl = `${BASE}${pageUrl}`;
        const outPath = path.join(OUT_DIR, file);

        try {
            if (!auth) {
                const anonContext = await browser.newContext({
                    viewport: { width: 414, height: 896 },
                    deviceScaleFactor: 2,
                    isMobile: true,
                });
                const anonPage = await anonContext.newPage();
                await anonPage.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
                await anonPage.waitForTimeout(2000);
                await anonPage.screenshot({ path: outPath, fullPage: false });
                await anonContext.close();
                console.log(`[${++success}/${pages.length}] ${file}`);
                continue;
            }

            await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
            await page.waitForTimeout(2000);

            if (page.url().includes('/login')) {
                console.log(`[SKIP] ${file} — redirected to login (no access)`);
                continue;
            }

            await page.screenshot({ path: outPath, fullPage: false });
            console.log(`[${++success}/${pages.length}] ${file}`);
        } catch (err) {
            console.log(`[FAIL] ${file} — ${err.message}`);
            failed++;
        }
    }

    console.log(`\nDone! Success: ${success}, Failed: ${failed}`);
    await browser.close();
})();

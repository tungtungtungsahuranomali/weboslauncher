const puppeteer = require('puppeteer');

(async () => {
    try {
        const browser = await puppeteer.launch({ headless: 'new' });
        const page = await browser.newPage();
        
        page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        page.on('pageerror', err => console.log('PAGE ERROR:', err.toString()));

        await page.goto('http://127.0.0.1/AHFix/dining.html', { waitUntil: 'networkidle0' });
        
        // Take a screenshot of the page to see what's actually rendering!
        await page.screenshot({ path: 'd:/JOKO/xampp8.2/htdocs/AHFix/dining_test_screen.png' });
        
        // Get the innerHTML of menuGrid or slideshow
        const content = await page.evaluate(() => {
            return {
                menuGrid: document.getElementById('menuGrid') ? document.getElementById('menuGrid').innerHTML : 'Not found',
                slideShow: document.getElementById('slideshow') ? document.getElementById('slideshow').innerHTML : 'Not found'
            };
        });
        
        console.log("DOM output:", JSON.stringify(content, null, 2));

        await browser.close();
    } catch (e) {
        console.error("Puppeteer Script Error:", e);
    }
})();

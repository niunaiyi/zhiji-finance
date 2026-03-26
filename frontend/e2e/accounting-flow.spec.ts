import { test, expect } from '@playwright/test';

test.describe('End-to-End Accounting Flow', () => {

  test('Complete Flow: Login -> Create Account -> Verify', async ({ page }) => {
    // 1. 登录
    await page.goto('/login');
    await page.fill('input[placeholder="邮箱"]', 'admin@acc001.com');
    await page.fill('input[placeholder="密码"]', 'password');
    await page.click('button[type="submit"]');

    // 2. 检查登录是否跳转 (普通用户会自动选择第一个账套并跳转)
    await expect(page).not.toHaveURL('/login', { timeout: 20000 });
    console.log('Login successful, URL:', page.url());

    // 3. 进入科目页面
    await page.goto('/subjects');
    await page.waitForTimeout(2000);
    await expect(page.locator('h2')).toContainText('会计科目');

    // 4. 新增科目
    await page.click('button:has-text("新增科目")');
    await expect(page.locator('.ant-modal-content')).toBeVisible();
    
    const randomCode = `10010${Math.floor(Math.random() * 9 + 1)}`;
    const randomName = '自动化测试-' + randomCode;
    
    await page.fill('#code', randomCode);
    await page.fill('#name', randomName);
    
    // 选择类别和余额方向
    await page.click('#element_type');
    await page.click('.ant-select-item-option-content:has-text("资产")');
    await page.click('input[value="debit"]');

    // 5. 提交
    await page.click('.ant-modal-footer button:has-text("确定")');

    // 6. 等待成功提示
    await expect(page.locator('.ant-message-notice-content')).toContainText('成功', { timeout: 10000 });

    // 7. 搜索并确认
    await page.fill('input[placeholder="搜索科目编码或名称..."]', randomCode);
    await expect(page.locator('.custom-table')).toContainText(randomName);
  });
});

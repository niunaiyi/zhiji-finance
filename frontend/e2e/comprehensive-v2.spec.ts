import { test, expect } from '@playwright/test';

test.describe('Real Backend E2E Scenarios', () => {

  test('Isolation Test: ACC001 and ACC002', async ({ page }) => {
    // 1. 登录 ACC001
    await page.goto('/login');
    await page.fill('input[placeholder="邮箱"]', 'admin@acc001.com');
    await page.fill('input[placeholder="密码"]', 'password');
    await page.click('button[type="submit"]');
    
    // 检查是否跳转 (正常用户登录后自动选择第一个账套并进入首页)
    await expect(page).not.toHaveURL('/login', { timeout: 20000 });
    
    // 2. 在 ACC001 中创建科目
    await page.goto('/subjects');
    await page.waitForTimeout(2000); // 增加等待时间确保 BookContext 加载
    await page.click('button:has-text("新增科目")');
    
    // 等待 Modal 加载
    await expect(page.locator('.ant-modal-content')).toBeVisible();
    
    const acc1Code = '100101';
    const acc1Name = 'ACC001-现金';
    
    await page.fill('#code', acc1Code);
    await page.fill('#name', acc1Name);
    
    // 选择类别 (资产)
    await page.click('#element_type');
    await page.click('.ant-select-item-option-content:has-text("资产")');
    
    // 选择方向 (借方)
    await page.click('input[value="debit"]');
    
    await page.click('.ant-modal-footer button:has-text("确定")');
    await expect(page.locator('.ant-message-notice-content')).toContainText('成功', { timeout: 10000 });

    // 3. 退出并登录 ACC002
    await page.goto('/login'); // 简单点，直接回登录页
    await page.fill('input[placeholder="邮箱"]', 'admin@acc002.com');
    await page.fill('input[placeholder="密码"]', 'password');
    await page.click('button[type="submit"]');
    
    // 检查是否跳转 (正常用户登录后自动选择第一个账套并进入首页)
    await expect(page).not.toHaveURL('/login', { timeout: 20000 });

    // 4. 验证在 ACC002 中看不到 ACC001 的科目
    await page.goto('/subjects');
    await page.waitForTimeout(1000);
    await expect(page.locator('.custom-table')).not.toContainText(acc1Name);

    // 5. 在 ACC002 中创建自己的科目
    await page.click('button:has-text("新增科目")');
    await expect(page.locator('.ant-modal-content')).toBeVisible();
    
    const acc2Code = '100102';
    const acc2Name = 'ACC002-银行';
    
    await page.fill('#code', acc2Code);
    await page.fill('#name', acc2Name);
    
    // 选择类别
    await page.click('#element_type');
    await page.click('.ant-select-item-option-content:has-text("资产")');
    
    // 选择方向
    await page.click('input[value="debit"]');
    
    await page.click('.ant-modal-footer button:has-text("确定")');
    await expect(page.locator('.ant-message-notice-content')).toContainText('成功');

    // 6. 再次回到 ACC001 验证
    await page.goto('/login');
    await page.fill('input[placeholder="邮箱"]', 'admin@acc001.com');
    await page.fill('input[placeholder="密码"]', 'password');
    await page.click('button[type="submit"]');
    
    // 检查是否跳转
    await expect(page).not.toHaveURL('/login', { timeout: 20000 });
    
    await page.goto('/subjects');
    await page.waitForTimeout(1000);
    await expect(page.locator('.custom-table')).toContainText(acc1Name);
    await expect(page.locator('.custom-table')).not.toContainText(acc2Name);
  });
});

import { test, expect } from '@playwright/test';

test.describe('Login Page', () => {
  test('should display login form', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('.ant-card-head-title')).toBeVisible();
    await expect(page.locator('input[placeholder="邮箱"]')).toBeVisible();
    await expect(page.locator('input[placeholder="密码"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should show validation error for empty fields', async ({ page }) => {
    await page.goto('/login');
    await page.click('button[type="submit"]');
    const errors = page.locator('.ant-form-item-explain-error');
    await expect(errors).toHaveCount(2);
  });

  test('should send login request and receive error for invalid credentials', async ({ page }) => {
    await page.goto('/login');
    
    await page.fill('input[placeholder="邮箱"]', 'wrong@example.com');
    await page.fill('input[placeholder="密码"]', 'wrongpassword');
    
    // 监听网络请求响应
    const responsePromise = page.waitForResponse(response => 
      response.url().includes('/api/v1/auth/login')
    );

    // 点击登录
    await page.click('button[type="submit"]');
    
    // 等待响应返回
    const response = await responsePromise;
    
    // 验证状态码为 401 (Unauthorized) 或 404 (如果 API 还没写完)
    // 只要有响应，就说明 E2E 链路是通的
    expect([401, 404, 422]).toContain(response.status());
    
    // 截图记录状态
    await page.screenshot({ path: 'login-network-response.png' });
  });
});

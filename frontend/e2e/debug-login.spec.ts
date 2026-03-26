import { test, expect } from '@playwright/test';

test.describe('Debug Login Flow', () => {
  
  test('should login successfully or show why it fails', async ({ page }) => {
    // 监听控制台日志
    page.on('console', msg => console.log('BROWSER LOG:', msg.text()));
    
    // 监听网络请求
    page.on('request', request => console.log('REQUEST:', request.method(), request.url()));
    page.on('response', response => console.log('RESPONSE:', response.status(), response.url()));

    await page.goto('/login');
    await page.fill('input[placeholder="邮箱"]', 'admin@acc01.com');
    await page.fill('input[placeholder="密码"]', 'password');
    
    // 点击并等待响应
    await page.click('button[type="submit"]');
    
    // 截图
    await page.screenshot({ path: 'login-debug.png' });
    
    // 等待跳转，如果失败，上面的日志会告诉我们为什么
    await expect(page).toHaveURL('http://localhost:5173/', { timeout: 15000 });
  });
});

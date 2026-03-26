# 测试模式 (Testing Patterns)

**分析日期:** 2025-11-20

## 测试框架

### 后端 (PHP)

**执行器:**
- PHPUnit 11.0.1
- 配置文件: `phpunit.xml`

**断言库:**
- PHPUnit 内置断言
- 使用 Mockery 进行对象模拟

**执行命令:**
```bash
./vendor/bin/phpunit         # 运行所有测试
./vendor/bin/paratest       # 并行运行测试
```

### 前端 (TypeScript)

**执行器:**
- 自定义集成测试（可能使用 Vitest 或 Jest）。
- Playwright 用于 E2E/集成测试。

**执行命令:**
```bash
# 前端测试脚本未显式定义在 package.json 中，但存在集成测试
```

## 测试文件组织

### 后端

**位置:**
- `tests/Functional/`: Action/Controller 层的特性/集成测试。
- `tests/Integration/`: 低级别组件的集成测试。
- `tests/Unit/`: 隔离的单元测试。

**命名:**
- `[Action/TaskName]Test.php` (如 `CreateCompanyActionTest.php`)

### 前端

**位置:**
- `frontend/src/__tests__/`: 前端集成和单元测试。

**命名:**
- `[filename].test.ts` (如 `api.test.ts`)

## 测试结构

### 后端 (功能测试)
```php
public function test_something(): void
{
    // Arrange: 设置初始状态，创建模型，以用户身份操作
    $user = User::factory()->create();
    $this->actingAs($user);
    $data = [...];

    // Act: 执行被测试的 Action 或 Task
    $action = app(CreateCompanyAction::class);
    $company = $action->run($data);

    // Assert: 验证数据库状态或返回值
    $this->assertDatabaseHas('companies', ['code' => 'TEST01']);
}
```

### 前端 (集成测试)
```typescript
describe('API Integration Tests', () => {
  beforeAll(async () => {
    // 设置登录/认证
  });

  afterAll(async () => {
    // 清理创建的资源
  });

  it('应该创建并获取账户', async () => {
    const response = await accountsApi.create({ ... });
    expect(response.code).toBe(uniqueCode);
  });
});
```

## Mocking (模拟)

### 后端

**框架:** Mockery

**模式:**
```php
// 在 Action 测试中模拟一个 Task
$mockTask = $this->mock(\App\Containers\Finance\Auth\Tasks\AssignUserRoleTask::class);
$mockTask->shouldReceive('run')
    ->once()
    ->andThrow(new \RuntimeException('模拟失败'));
```

**模拟对象:**
- 副作用（如外部 API 调用、邮件发送）。
- Action 测试中的 Tasks，用于隔离失败场景（如事务回滚测试）。

### 前端

**模式:**
- 目前集成测试使用真实的 API 调用并配合设置/清理工作（参考 `api.test.ts`）。

## Fixtures 与 Factories

### 后端

**测试数据:**
- 使用每个容器 `Data/Factories` 中的 Laravel/Apiato Factories。
- `User::factory()->create()` 是常见用法。

**位置:**
- 容器 Factories: `app/Containers/{Section}/{Container}/Data/Factories/`
- Seeders: `app/Containers/{Section}/{Container}/Data/Seeders/`

## 覆盖率

**要求:**
- 配置中未发现严格的覆盖率阈值要求。

## 测试类型

**单元测试:**
- 专注于隔离的 Tasks 或工具类。

**功能/集成测试:**
- 后端: 测试带有数据库交互的 Actions（刷新数据库）。
- 前端: `api.test.ts` 测试与后端 API 的交互。

---

*测试分析: 2025-11-20*

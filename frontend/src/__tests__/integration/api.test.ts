// frontend/src/__tests__/integration/api.test.ts
import { accountsApi } from '../../api/accounts';
import { auxCategoriesApi, auxItemsApi } from '../../api/auxiliary';
import { authApi } from '../../api/auth';

describe('API Integration Tests', () => {
  // Setup: Login and select company before running tests
  beforeAll(async () => {
    // Login with test credentials (from CompanySeeder)
    const loginResponse = await authApi.login('admin@example.com', 'password');

    // Select first available company
    const company = loginResponse.companies[0];
    const selectResponse = await authApi.selectCompany(company.id);

    // Store auth data in localStorage for API client interceptors
    localStorage.setItem('auth', JSON.stringify({
      user: loginResponse.user,
      token: selectResponse.token,
      company: selectResponse.company,
      role: selectResponse.role,
    }));
  });

  // Cleanup: Clear auth after tests
  afterAll(() => {
    localStorage.removeItem('auth');
  });

  describe('Accounts API', () => {
    it('should list accounts', async () => {
      const response = await accountsApi.list();
      expect(response.data).toBeInstanceOf(Array);
    });

    it('should create and retrieve account', async () => {
      const newAccount = await accountsApi.create({
        code: '9999',
        name: 'Test Account',
      });
      expect(newAccount.code).toBe('9999');

      const retrieved = await accountsApi.get(newAccount.id);
      expect(retrieved.name).toBe('Test Account');
    });
  });

  describe('Auxiliary API', () => {
    it('should list aux categories', async () => {
      const response = await auxCategoriesApi.list();
      expect(response.data).toBeInstanceOf(Array);
      expect(response.data.length).toBeGreaterThan(0);
    });

    it('should create aux item', async () => {
      const categories = await auxCategoriesApi.list();
      const category = categories.data[0];

      const newItem = await auxItemsApi.create({
        aux_category_id: category.id,
        code: 'TEST001',
        name: 'Test Item',
      });
      expect(newItem.code).toBe('TEST001');
    });
  });
});

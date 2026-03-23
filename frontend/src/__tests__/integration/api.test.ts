// frontend/src/__tests__/integration/api.test.ts
import { accountsApi } from '../../api/accounts';
import { auxCategoriesApi, auxItemsApi } from '../../api/auxiliary';
import { authApi } from '../../api/auth';

describe('API Integration Tests', () => {
  // Track created resources for cleanup
  const createdAccountIds: number[] = [];
  const createdItemIds: number[] = [];

  // Setup: Login and select company before running tests
  beforeAll(async () => {
    try {
      // Login with test credentials (from CompanySeeder)
      const loginResponse = await authApi.login('admin@example.com', 'password');

      if (!loginResponse.companies?.length) {
        throw new Error('No companies available for testing');
      }

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
    } catch (error) {
      console.error('Test setup failed:', error);
      throw error;
    }
  });

  // Cleanup: Clear auth and test data after tests
  afterAll(async () => {
    // Clean up created test data
    for (const id of createdAccountIds) {
      try {
        await accountsApi.deactivate(id);
      } catch (error) {
        console.warn('Failed to cleanup account:', id);
      }
    }

    for (const id of createdItemIds) {
      try {
        await auxItemsApi.deactivate(id);
      } catch (error) {
        console.warn('Failed to cleanup aux item:', id);
      }
    }

    localStorage.removeItem('auth');
  });

  describe('Accounts API', () => {
    it('should list accounts', async () => {
      const response = await accountsApi.list();
      expect(response.data).toBeInstanceOf(Array);
    });

    it('should create and retrieve account', async () => {
      const uniqueCode = `TEST${Date.now()}`;
      const newAccount = await accountsApi.create({
        code: uniqueCode,
        name: 'Test Account',
      });
      createdAccountIds.push(newAccount.id);
      expect(newAccount.code).toBe(uniqueCode);

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

      const uniqueCode = `TEST${Date.now()}`;
      const newItem = await auxItemsApi.create({
        aux_category_id: category.id,
        code: uniqueCode,
        name: 'Test Item',
      });
      createdItemIds.push(newItem.id);
      expect(newItem.code).toBe(uniqueCode);
    });
  });
});

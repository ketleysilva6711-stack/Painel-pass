import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../auth'

// Mock axios
vi.mock('axios', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    interceptors: {
      request: { use: vi.fn() },
      response: { use: vi.fn() }
    }
  }
}))

import axios from 'axios'
const mockedAxios = vi.mocked(axios)

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    localStorage.clear()
  })

  describe('login', () => {
    it('should login successfully and store token', async () => {
      const mockUser = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        role: 'user',
        roleLabel: 'Usuário',
        eyebrow: 'Meu espaço',
        initials: 'TE',
        funcionalidades: ['play-fake', 'qr-code'],
        contaStatus: 'active',
        contaStatusLabel: 'Conta ativa',
        contaStatusKind: 'ok',
        contaExpiraEm: null,
        totpEnabled: false
      }

      mockedAxios.post.mockResolvedValue({
        data: {
          access_token: 'test-token-123',
          token_type: 'Bearer',
          user: mockUser
        },
      })

      const authStore = useAuthStore()
      await authStore.login('test@example.com', 'password123')

      expect(authStore.token).toBe('test-token-123')
      expect(authStore.user).toEqual(mockUser)
      expect(localStorage.getItem('auth_token')).toBe('test-token-123')
      expect(localStorage.getItem('auth_user')).toBe(JSON.stringify(mockUser))
    })

    it('should handle login error', async () => {
      mockedAxios.post.mockRejectedValue({
        response: {
          status: 401,
          data: { message: 'Usuário ou senha inválidos.' }
        }
      })

      const authStore = useAuthStore()

      await expect(authStore.login('wrong@email.com', 'wrongpass'))
        .rejects.toThrow('Usuário ou senha inválidos.')

      expect(authStore.token).toBeNull()
      expect(authStore.user).toBeNull()
    })
  })

  describe('logout', () => {
    it('should clear auth state', async () => {
      // Setup initial state
      localStorage.setItem('auth_token', 'test-token')
      localStorage.setItem('auth_user', JSON.stringify({ id: 1, name: 'Test' }))

      mockedAxios.post.mockResolvedValue({ data: { message: 'Sessão encerrada.' } })

      const authStore = useAuthStore()
      authStore.token = 'test-token'
      authStore.user = { id: 1, name: 'Test' }

      await authStore.logout()

      expect(authStore.token).toBeNull()
      expect(authStore.user).toBeNull()
      expect(localStorage.getItem('auth_token')).toBeNull()
      expect(localStorage.getItem('auth_user')).toBeNull()
    })
  })

  describe('fetchUser', () => {
    it('should fetch and set user data', async () => {
      const mockUser = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        role: 'admin',
        roleLabel: 'Admin',
        eyebrow: 'Gestão',
        initials: 'AD',
        funcionalidades: ['play-fake', 'qr-code', 'admin'],
        contaStatus: 'active',
        contaStatusLabel: 'Acesso total',
        contaStatusKind: 'ok',
        totpEnabled: false
      }

      mockedAxios.get.mockResolvedValue({
        data: { user: mockUser }
      })

      const authStore = useAuthStore()
      authStore.token = 'test-token'

      await authStore.fetchUser()

      expect(authStore.user).toEqual(mockUser)
    })

    it('should clear auth on 401', async () => {
      mockedAxios.get.mockRejectedValue({
        response: { status: 401 }
      })

      const authStore = useAuthStore()
      authStore.token = 'test-token'
      authStore.user = { id: 1, name: 'Test' }

      await expect(authStore.fetchUser()).rejects.toThrow()
      expect(authStore.token).toBeNull()
      expect(authStore.user).toBeNull()
    })
  })

  describe('hasFuncionalidade', () => {
    it('should return true for existing funcionalidade', () => {
      const authStore = useAuthStore()
      authStore.user = {
        funcionalidades: ['play-fake', 'qr-code', 'admin']
      } as any

      expect(authStore.hasFuncionalidade('play-fake')).toBe(true)
      expect(authStore.hasFuncionalidade('qr-code')).toBe(true)
      expect(authStore.hasFuncionalidade('admin')).toBe(true)
    })

    it('should return false for non-existing funcionalidade', () => {
      const authStore = useAuthStore()
      authStore.user = {
        funcionalidades: ['play-fake', 'qr-code']
      } as any

      expect(authStore.hasFuncionalidade('admin')).toBe(false)
      expect(authStore.hasFuncionalidade('bypass')).toBe(false)
    })

    it('should return false when user has no funcionalidades', () => {
      const authStore = useAuthStore()
      authStore.user = { funcionalidades: [] } as any

      expect(authStore.hasFuncionalidade('play-fake')).toBe(false)
    })

    it('should return false when user is null', () => {
      const authStore = useAuthStore()
      authStore.user = null

      expect(authStore.hasFuncionalidade('play-fake')).toBe(false)
    })
  })

  describe('isAdmin / isRevendedor / isUser', () => {
    it('should correctly identify admin', () => {
      const authStore = useAuthStore()
      authStore.user = { role: 'admin' } as any
      expect(authStore.isAdmin).toBe(true)
      expect(authStore.isRevendedor).toBe(false)
      expect(authStore.isUser).toBe(false)
    })

    it('should correctly identify revendedor', () => {
      const authStore = useAuthStore()
      authStore.user = { role: 'revendedor' } as any
      expect(authStore.isAdmin).toBe(false)
      expect(authStore.isRevendedor).toBe(true)
      expect(authStore.isUser).toBe(false)
    })

    it('should correctly identify user', () => {
      const authStore = useAuthStore()
      authStore.user = { role: 'user' } as any
      expect(authStore.isAdmin).toBe(false)
      expect(authStore.isRevendedor).toBe(false)
      expect(authStore.isUser).toBe(true)
    })
  })

  describe('initializeAuth', () => {
    it('should restore from localStorage', () => {
      const mockUser = {
        id: 1,
        name: 'Test',
        email: 'test@test.com',
        role: 'user',
        funcionalidades: ['play-fake']
      }

      localStorage.setItem('auth_token', 'stored-token')
      localStorage.setItem('auth_user', JSON.stringify(mockUser))

      mockedAxios.get.mockResolvedValue({ data: { user: mockUser } })

      const authStore = useAuthStore()
      await authStore.initializeAuth()

      expect(authStore.token).toBe('stored-token')
      expect(authStore.user).toEqual(mockUser)
    })

    it('should clear invalid localStorage', async () => {
      localStorage.setItem('auth_token', 'invalid-token')
      localStorage.setItem('auth_user', JSON.stringify({ id: 1 }))

      mockedAxios.get.mockRejectedValue({
        response: { status: 401 }
      })

      const authStore = useAuthStore()
      await authStore.initializeAuth()

      expect(authStore.token).toBeNull()
      expect(authStore.user).toBeNull()
      expect(localStorage.getItem('auth_token')).toBeNull()
    })
  })
})
import { defineStore } from 'pinia';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as any,
    token: localStorage.getItem('token') || '',
    isInitialized: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => state.user?.role || 'user',
    isAdmin: (state) => state.user?.role === 'admin',
    isRevendedor: (state) => state.user?.role === 'revendedor',
  },
  actions: {
    async login(email: string, password: string) {
      const response = await axios.post(`${API_URL}/auth/login`, { email, password });
      this.token = response.data.access_token;
      this.user = response.data.user;
      localStorage.setItem('token', this.token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      return response.data;
    },
    async initAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        try {
          const res = await axios.get(`${API_URL}/auth/me`);
          this.user = res.data.user;
        } catch (err) {
          this.logout();
        }
      }
      this.isInitialized = true;
    },
    async logout() {
      if (this.token) {
        try {
          await axios.post(`${API_URL}/auth/logout`);
        } catch (e) {}
      }
      this.token = '';
      this.user = null;
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
    }
  }
});
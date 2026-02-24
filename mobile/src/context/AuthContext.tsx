import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { login as apiLogin, LoginResponse } from '../services/api';

export type AuthState = {
  token: string | null;
  empresaId: number | null;
  userId: number | null;
  usuarioNome?: string | null;
  usuarioEmail?: string | null;
};

type AuthContextValue = {
  auth: AuthState;
  loading: boolean;
  login: (email: string, senha: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

const STORAGE_KEYS = {
  token: 'auth_token',
  empresaId: 'empresa_id',
  userId: 'user_id',
  usuarioNome: 'usuario_nome',
  usuarioEmail: 'usuario_email',
};

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [auth, setAuth] = useState<AuthState>({
    token: null,
    empresaId: null,
    userId: null,
    usuarioNome: null,
    usuarioEmail: null,
  });
  const [loading, setLoading] = useState(true);

  const hydrate = useCallback(async () => {
    try {
      const [token, empresaId, userId, usuarioNome, usuarioEmail] = await Promise.all([
        AsyncStorage.getItem(STORAGE_KEYS.token),
        AsyncStorage.getItem(STORAGE_KEYS.empresaId),
        AsyncStorage.getItem(STORAGE_KEYS.userId),
        AsyncStorage.getItem(STORAGE_KEYS.usuarioNome),
        AsyncStorage.getItem(STORAGE_KEYS.usuarioEmail),
      ]);

      setAuth({
        token: token || null,
        empresaId: empresaId ? Number(empresaId) : null,
        userId: userId ? Number(userId) : null,
        usuarioNome: usuarioNome || null,
        usuarioEmail: usuarioEmail || null,
      });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    hydrate();
  }, [hydrate]);

  const login = useCallback(async (email: string, senha: string) => {
    const data: LoginResponse = await apiLogin(email, senha);
    const token = data.token;
    if (!token) {
      throw new Error('Falha no login: token ausente.');
    }
    const empresaId = data.empresa?.id_empresa ?? null;
    const userId = data.usuario?.id_usuario ?? null;

    await AsyncStorage.setItem(STORAGE_KEYS.token, token);
    if (empresaId) {
      await AsyncStorage.setItem(STORAGE_KEYS.empresaId, String(empresaId));
    } else {
      await AsyncStorage.removeItem(STORAGE_KEYS.empresaId);
    }
    if (userId) {
      await AsyncStorage.setItem(STORAGE_KEYS.userId, String(userId));
    } else {
      await AsyncStorage.removeItem(STORAGE_KEYS.userId);
    }
    await AsyncStorage.setItem(STORAGE_KEYS.usuarioNome, data.usuario?.nome || '');
    await AsyncStorage.setItem(STORAGE_KEYS.usuarioEmail, data.usuario?.email || '');

    setAuth({
      token,
      empresaId,
      userId,
      usuarioNome: data.usuario?.nome || null,
      usuarioEmail: data.usuario?.email || null,
    });
  }, []);

  const logout = useCallback(async () => {
    await Promise.all(Object.values(STORAGE_KEYS).map((key) => AsyncStorage.removeItem(key)));
    setAuth({
      token: null,
      empresaId: null,
      userId: null,
      usuarioNome: null,
      usuarioEmail: null,
    });
  }, []);

  const value = useMemo(
    () => ({
      auth,
      loading,
      login,
      logout,
    }),
    [auth, loading, login, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth deve ser usado dentro de AuthProvider');
  }
  return ctx;
};

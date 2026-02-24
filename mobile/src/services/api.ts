import { API_BASE_URL } from '../config';

export type LoginResponse = {
  token: string;
  usuario: {
    id_usuario: number;
    nome?: string;
    email?: string;
    perfil?: string;
  } | null;
  empresa: {
    id_empresa: number;
    nome_empresa?: string;
  } | null;
  licenca?: unknown;
  segmento?: string;
};

type LoginRaw = {
  token?: string;
  access_token?: string;
  usuario?: LoginResponse['usuario'];
  empresa?: LoginResponse['empresa'];
  licenca?: unknown;
  segmento?: string;
  data?: {
    token?: string;
    access_token?: string;
    usuario?: LoginResponse['usuario'];
    empresa?: LoginResponse['empresa'];
    licenca?: unknown;
    segmento?: string;
  };
  message?: string;
};

export type InventarioCapa = {
  id_capa_inventario: number;
  descricao: string;
  status: string;
  data_inicio?: string;
  data_fechamento?: string;
  observacao?: string;
  filial?: {
    id_filial: number;
    nome_filial?: string;
  };
  usuario?: {
    id_usuario: number;
    nome?: string;
  };
};

export async function fetchCapasInventarioEmpresa(
  empresaId: number,
  token: string
): Promise<InventarioCapa[]> {
  const response = await fetch(buildUrl(`/api/v1/capas-inventario/empresa/${empresaId}`), {
    method: 'GET',
    headers: getHeaders(token, empresaId),
  });
  const data = await normalizeJson(response);
  if (!response.ok) {
    throw new Error(`Erro ao carregar inventários (${response.status}).`);
  }
  if (Array.isArray(data)) return data as InventarioCapa[];
  if (data && typeof data === 'object') {
    const maybeItems =
      (data as { data?: unknown; items?: unknown }).data ??
      (data as { items?: unknown }).items ??
      (data as { data?: { data?: unknown } }).data?.data;
    if (Array.isArray(maybeItems)) return maybeItems as InventarioCapa[];
  }
  return [];
}

export type InventarioItem = {
  id_inventario: number;
  id_produto: number;
  id_filial: number;
  quantidade_sistema?: number;
  quantidade_fisica?: number | null;
  produto?: {
    id_produto: number;
    descricao?: string;
    codigo_barras?: string | null;
    unidade_medida?: string | null;
  };
};

export type Contagem = {
  id_contagem: number;
  id_inventario: number;
  id_produto: number;
  id_empresa: number;
  id_filial: number;
  id_usuario: number;
  quantidade: number;
  tipo_operacao: 'Adicionar' | 'Excluir' | 'Substituir' | string;
  observacao?: string | null;
  data_contagem?: string;
};

const buildUrl = (path: string) => `${API_BASE_URL}${path}`;

const normalizeJson = async (response: Response) => {
  const text = await response.text();
  const cleaned = text.replace(/^\uFEFF/, '');
  try {
    return JSON.parse(cleaned);
  } catch (error) {
    return cleaned;
  }
};

const getHeaders = (token: string, empresaId?: number, json = false) => {
  const headers: Record<string, string> = {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
  };
  if (json) {
    headers['Content-Type'] = 'application/json';
  }
  if (empresaId) {
    headers['X-ID-EMPRESA'] = empresaId.toString();
  }
  return headers;
};

function normalizeLoginResponse(data: LoginRaw): LoginResponse {
  const token =
    data?.token ||
    data?.access_token ||
    data?.data?.token ||
    data?.data?.access_token ||
    '';

  if (!token) {
    const message = data?.message || 'Falha no login. Token não retornado.';
    throw new Error(message);
  }

  return {
    token,
    usuario: data?.usuario || data?.data?.usuario || null,
    empresa: data?.empresa || data?.data?.empresa || null,
    licenca: data?.licenca || data?.data?.licenca,
    segmento: data?.segmento || data?.data?.segmento,
  };
}

export async function login(email: string, senha: string): Promise<LoginResponse> {
  const response = await fetch(buildUrl('/api/login'), {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, senha }),
  });

  const data = (await normalizeJson(response)) as LoginRaw;
  if (!response.ok) {
    const message = typeof data === 'object' && data && 'message' in data ? data.message : null;
    throw new Error(message || `Falha no login (${response.status}).`);
  }

  return normalizeLoginResponse(data);
}

export async function fetchCapaInventario(
  idInventario: number,
  token: string,
  empresaId: number
): Promise<InventarioCapa> {
  const response = await fetch(buildUrl(`/api/capa-inventarios/${idInventario}`), {
    method: 'GET',
    headers: getHeaders(token, empresaId),
  });
  const data = await normalizeJson(response);
  if (!response.ok) {
    throw new Error(`Erro ao carregar inventário (${response.status}).`);
  }
  return data as InventarioCapa;
}

export async function fetchItensInventario(
  idInventario: number,
  token: string,
  empresaId: number
): Promise<InventarioItem[]> {
  const response = await fetch(buildUrl(`/api/v1/inventarios/capa/${idInventario}`), {
    method: 'GET',
    headers: getHeaders(token, empresaId),
  });
  const data = await normalizeJson(response);
  if (!response.ok) {
    throw new Error(`Erro ao carregar itens (${response.status}).`);
  }
  if (Array.isArray(data)) return data as InventarioItem[];
  if (data && typeof data === 'object') {
    const maybeItems = (data as { data?: unknown; items?: unknown }).data ?? (data as { items?: unknown }).items;
    if (Array.isArray(maybeItems)) return maybeItems as InventarioItem[];
  }
  return [];
}

export async function fetchContagens(
  idInventario: number,
  token: string,
  empresaId: number
): Promise<Contagem[]> {
  const response = await fetch(buildUrl(`/api/contagens/inventario/${idInventario}`), {
    method: 'GET',
    headers: getHeaders(token, empresaId),
  });
  const data = await normalizeJson(response);
  if (!response.ok) {
    throw new Error(`Erro ao carregar contagens (${response.status}).`);
  }
  if (Array.isArray(data)) return data as Contagem[];
  if (data && typeof data === 'object') {
    const maybeItems = (data as { data?: unknown; items?: unknown }).data ?? (data as { items?: unknown }).items;
    if (Array.isArray(maybeItems)) return maybeItems as Contagem[];
  }
  return [];
}

export async function createContagem(
  payload: Omit<Contagem, 'id_contagem'>,
  token: string,
  empresaId: number
): Promise<Contagem> {
  const response = await fetch(buildUrl('/api/contagens'), {
    method: 'POST',
    headers: getHeaders(token, empresaId, true),
    body: JSON.stringify(payload),
  });
  const data = await normalizeJson(response);
  if (!response.ok) {
    throw new Error(`Erro ao criar contagem (${response.status}).`);
  }
  return data as Contagem;
}

export async function updateContagem(
  idContagem: number,
  payload: Omit<Contagem, 'id_contagem'>,
  token: string,
  empresaId: number
): Promise<Contagem> {
  const response = await fetch(buildUrl(`/api/contagens/${idContagem}`), {
    method: 'PUT',
    headers: getHeaders(token, empresaId, true),
    body: JSON.stringify(payload),
  });
  const data = await normalizeJson(response);
  if (!response.ok) {
    throw new Error(`Erro ao atualizar contagem (${response.status}).`);
  }
  return data as Contagem;
}

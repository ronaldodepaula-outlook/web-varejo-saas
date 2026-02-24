import { API_BASE_URL, TASKS_API_BASE_URL } from '../config';

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

export type TarefaContagem = {
  id_tarefa: number;
  id_capa_inventario: number;
  id_usuario: number;
  id_supervisor: number;
  tipo_tarefa: string;
  status: string;
  data_inicio?: string | null;
  data_fim?: string | null;
  observacoes?: string | null;
  total_produtos?: number;
  produtos_contados?: number;
};

const buildUrl = (path: string) => `${API_BASE_URL}${path}`;
const buildTasksUrl = (path: string) => `${TASKS_API_BASE_URL}${path}`;
const TASKS_PRIMARY_BASE_URL = TASKS_API_BASE_URL || API_BASE_URL;
const TASKS_FALLBACK_BASE_URL = TASKS_PRIMARY_BASE_URL.includes('saas-multiempresas-new')
  ? TASKS_PRIMARY_BASE_URL.replace('saas-multiempresas-new', 'saas-multiempresas-api')
  : API_BASE_URL;

const isTasksRouteNotFound = (data: unknown) => {
  if (!data || typeof data !== 'object') return false;
  const message = (data as { message?: unknown }).message;
  return typeof message === 'string' && message.includes('api/inventario/tarefas');
};

const fetchTarefasApi = async (path: string, options: RequestInit) => {
  const primaryUrl = buildTasksUrl(path);
  if (typeof __DEV__ !== 'undefined' && __DEV__) {
    const method = options.method || 'GET';
    console.log(`[TAREFAS] ${method} ${primaryUrl}`);
  }
  const primaryResponse = await fetch(primaryUrl, options);
  const primaryData = await normalizeJson(primaryResponse);

  if (
    primaryResponse.status === 404 &&
    TASKS_FALLBACK_BASE_URL !== TASKS_PRIMARY_BASE_URL &&
    isTasksRouteNotFound(primaryData)
  ) {
    const retryUrl = `${TASKS_FALLBACK_BASE_URL}${path}`;
    if (typeof __DEV__ !== 'undefined' && __DEV__) {
      const method = options.method || 'GET';
      console.log(`[TAREFAS] RETRY ${method} ${retryUrl}`);
    }
    const retryResponse = await fetch(retryUrl, options);
    const retryData = await normalizeJson(retryResponse);
    return { response: retryResponse, data: retryData };
  }

  return { response: primaryResponse, data: primaryData };
};

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

const extractErrorMessage = (data: unknown, fallback: string) => {
  if (!data || typeof data !== 'object') return fallback;
  const maybeMessage = (data as { message?: unknown }).message;
  if (typeof maybeMessage === 'string' && maybeMessage.trim()) return maybeMessage;
  const maybeError = (data as { error?: unknown }).error;
  if (typeof maybeError === 'string' && maybeError.trim()) return maybeError;
  const maybeErrors = (data as { errors?: Record<string, string[] | string> }).errors;
  if (maybeErrors && typeof maybeErrors === 'object') {
    const messages = Object.values(maybeErrors)
      .flatMap((value) => (Array.isArray(value) ? value : [value]))
      .filter((value): value is string => typeof value === 'string' && value.trim());
    if (messages.length) return messages.join(' ');
  }
  return fallback;
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

const extractList = <T>(data: unknown): T[] => {
  if (Array.isArray(data)) return data as T[];
  if (!data || typeof data !== 'object') return [];
  const container = data as { data?: unknown; items?: unknown };
  if (Array.isArray(container.data)) return container.data as T[];
  if (Array.isArray(container.items)) return container.items as T[];
  const nested = (container.data as { data?: unknown } | undefined)?.data;
  if (Array.isArray(nested)) return nested as T[];
  return [];
};

export async function fetchTarefasPorInventario(
  idCapa: number,
  token: string,
  empresaId: number
): Promise<TarefaContagem[]> {
  const { response, data } = await fetchTarefasApi(
    `/api/inventario/tarefas/inventario/${idCapa}`,
    {
      method: 'GET',
      headers: getHeaders(token, empresaId),
    }
  );
  if (!response.ok) {
    throw new Error(extractErrorMessage(data, `Erro ao carregar tarefas (${response.status}).`));
  }
  return extractList<TarefaContagem>(data);
}

export async function fetchTarefasContagem(
  query: {
    status?: string;
    id_capa_inventario?: number;
    id_usuario?: number;
    data_inicio?: string;
    data_fim?: string;
    per_page?: number;
  },
  token: string,
  empresaId: number
): Promise<TarefaContagem[]> {
  const queryString = Object.entries(query)
    .filter(([, value]) => value !== undefined && value !== null && value !== '')
    .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`)
    .join('&');
  const path = queryString ? `/api/inventario/tarefas?${queryString}` : '/api/inventario/tarefas';
  const { response, data } = await fetchTarefasApi(path, {
    method: 'GET',
    headers: getHeaders(token, empresaId),
  });
  if (!response.ok) {
    throw new Error(extractErrorMessage(data, `Erro ao carregar tarefas (${response.status}).`));
  }
  return extractList<TarefaContagem>(data);
}

export async function criarTarefaContagem(
  payload: {
    id_capa_inventario: number;
    id_usuario: number;
    id_supervisor: number;
    tipo_tarefa: string;
    observacoes?: string | null;
    produtos?: number[];
  },
  query: {
    status?: string;
    id_capa_inventario?: number;
    id_usuario?: number;
    data_inicio?: string;
    data_fim?: string;
    per_page?: number;
  } | undefined,
  token: string,
  empresaId: number
): Promise<TarefaContagem> {
  const queryString = query
    ? Object.entries(query)
        .filter(([, value]) => value !== undefined && value !== null && value !== '')
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`)
        .join('&')
    : '';
  const path = queryString ? `/api/inventario/tarefas?${queryString}` : '/api/inventario/tarefas';
  const { response, data } = await fetchTarefasApi(path, {
    method: 'POST',
    headers: getHeaders(token, empresaId, true),
    body: JSON.stringify(payload),
  });
  if (!response.ok) {
    throw new Error(extractErrorMessage(data, `Erro ao criar tarefa (${response.status}).`));
  }
  return (data as { data?: TarefaContagem }).data || (data as TarefaContagem);
}

export async function iniciarTarefaContagem(
  idTarefa: number,
  token: string,
  empresaId: number,
  observacoes?: string
): Promise<TarefaContagem> {
  const { response, data } = await fetchTarefasApi(
    `/api/inventario/tarefas/${idTarefa}/iniciar`,
    {
      method: 'PUT',
      headers: getHeaders(token, empresaId, true),
      body: JSON.stringify({ observacoes }),
    }
  );
  if (!response.ok) {
    throw new Error(extractErrorMessage(data, `Erro ao iniciar tarefa (${response.status}).`));
  }
  return (data as { data?: TarefaContagem }).data || (data as TarefaContagem);
}

export async function retomarTarefaContagem(
  idTarefa: number,
  token: string,
  empresaId: number,
  observacoes?: string
): Promise<TarefaContagem> {
  const { response, data } = await fetchTarefasApi(
    `/api/inventario/tarefas/${idTarefa}/retomar`,
    {
      method: 'PUT',
      headers: getHeaders(token, empresaId, true),
      body: JSON.stringify({ observacoes }),
    }
  );
  if (!response.ok) {
    throw new Error(extractErrorMessage(data, `Erro ao retomar tarefa (${response.status}).`));
  }
  return (data as { data?: TarefaContagem }).data || (data as TarefaContagem);
}

export async function concluirTarefaContagem(
  idTarefa: number,
  token: string,
  empresaId: number,
  observacoes?: string,
  forcarConclusao = false
): Promise<TarefaContagem> {
  const { response, data } = await fetchTarefasApi(
    `/api/inventario/tarefas/${idTarefa}/concluir`,
    {
      method: 'PUT',
      headers: getHeaders(token, empresaId, true),
      body: JSON.stringify({ observacoes, forcar_conclusao: forcarConclusao }),
    }
  );
  if (!response.ok) {
    throw new Error(extractErrorMessage(data, `Erro ao concluir tarefa (${response.status}).`));
  }
  return (data as { data?: TarefaContagem }).data || (data as TarefaContagem);
}


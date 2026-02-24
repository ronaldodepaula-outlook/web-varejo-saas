import React, { useEffect, useMemo, useState } from 'react';
import { View, Text, StyleSheet, ActivityIndicator, TouchableOpacity, ScrollView } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../context/AuthContext';
import {
  Contagem,
  InventarioCapa,
  InventarioItem,
  TarefaContagem,
  criarTarefaContagem,
  fetchCapaInventario,
  fetchContagens,
  fetchItensInventario,
  fetchTarefasContagem,
  iniciarTarefaContagem,
  retomarTarefaContagem,
} from '../services/api';
import { theme } from '../styles/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'InventarioResumo'>;

const aplicarContagens = (item: InventarioItem, contagens: Contagem[]) => {
  const list = contagens.filter((c) => c.id_produto === item.id_produto);
  if (list.length === 0) {
    return Number(item.quantidade_fisica || 0);
  }
  const ordered = [...list].sort((a, b) => {
    const da = a.data_contagem ? new Date(a.data_contagem).getTime() : 0;
    const db = b.data_contagem ? new Date(b.data_contagem).getTime() : 0;
    return da - db;
  });
  let qty = 0;
  ordered.forEach((c) => {
    if (c.tipo_operacao === 'Adicionar') {
      qty += Number(c.quantidade || 0);
    } else if (c.tipo_operacao === 'Substituir') {
      qty = Number(c.quantidade || 0);
    } else if (c.tipo_operacao === 'Excluir') {
      qty = 0;
    } else {
      qty = Number(c.quantidade || 0);
    }
  });
  return qty;
};

const formatDateParam = (value?: string | null) => {
  if (!value) return undefined;
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return undefined;
  return date.toISOString().slice(0, 10);
};

const buildProdutosCongelados = (items: InventarioItem[]) =>
  Array.from(
    new Set(items.map((item) => item.id_produto).filter((id) => Number.isInteger(id) && id > 0))
  );

const getTarefaId = (tarefa: TarefaContagem | null) =>
  tarefa ? (tarefa as unknown as { id_tarefa?: number; id?: number }).id_tarefa ?? (tarefa as unknown as { id?: number }).id ?? null : null;

const normalizeStatus = (status?: string | null) => (status || '').toLowerCase();

const InventorySummaryScreen: React.FC<Props> = ({ navigation, route }) => {
  const { auth } = useAuth();
  const { idInventario } = route.params;
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [capa, setCapa] = useState<InventarioCapa | null>(null);
  const [items, setItems] = useState<InventarioItem[]>([]);
  const [contagens, setContagens] = useState<Contagem[]>([]);
  const [starting, setStarting] = useState(false);

  useEffect(() => {
    const load = async () => {
      if (!auth.token || !auth.empresaId) {
        setError('Sessão inválida.');
        setLoading(false);
        return;
      }
      setLoading(true);
      setError(null);
      try {
        const [capaData, itensData, contagensData] = await Promise.all([
          fetchCapaInventario(idInventario, auth.token, auth.empresaId),
          fetchItensInventario(idInventario, auth.token, auth.empresaId),
          fetchContagens(idInventario, auth.token, auth.empresaId),
        ]);
        setCapa(capaData);
        setItems(itensData);
        setContagens(contagensData);
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Erro ao carregar Inventário.';
        setError(message);
      } finally {
        setLoading(false);
      }
    };

    load();
  }, [auth.empresaId, auth.token, idInventario]);

  const resumo = useMemo(() => {
    const total = items.length;
    const contados = items.filter((item) => aplicarContagens(item, contagens) > 0).length;
    const pendentes = total - contados;
    const diferencas = items.filter((item) => {
      const sistema = Number(item.quantidade_sistema || 0);
      const contado = aplicarContagens(item, contagens);
      return contado !== 0 && contado - sistema !== 0;
    }).length;
    return { total, contados, pendentes, diferencas };
  }, [contagens, items]);

  return (
    <ScrollView contentContainerStyle={styles.container}>
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={theme.colors.primary} />
          <Text style={styles.info}>Carregando Inventário...</Text>
        </View>
      ) : (
        <>
          {capa && (
            <View style={styles.card}>
              <View style={styles.titleRow}>
                <MaterialCommunityIcons
                  name="clipboard-list-outline"
                  size={20}
                  color={theme.colors.primary}
                />
                <Text style={styles.title}>Inventário #{capa.id_capa_inventario}</Text>
              </View>
              <View style={styles.metaRow}>
                <MaterialCommunityIcons name="text-box-outline" size={16} color={theme.colors.textMuted} />
                <Text style={styles.metaText}>{capa.descricao}</Text>
              </View>
              <View style={styles.metaRow}>
                <MaterialCommunityIcons name="progress-check" size={16} color={theme.colors.textMuted} />
                <Text style={styles.metaText}>{capa.status}</Text>
              </View>
              <View style={styles.metaRow}>
                <MaterialCommunityIcons
                  name="storefront-outline"
                  size={16}
                  color={theme.colors.textMuted}
                />
                <Text style={styles.metaText}>{capa.filial?.nome_filial || 'N/A'}</Text>
              </View>
              <View style={styles.metaRow}>
                <MaterialCommunityIcons name="account-outline" size={16} color={theme.colors.textMuted} />
                <Text style={styles.metaText}>{capa.usuario?.nome || 'N/A'}</Text>
              </View>
            </View>
          )}

          <View style={styles.summaryRow}>
            <View style={styles.summaryCard}>
              <View style={styles.summaryHeader}>
                <MaterialCommunityIcons name="format-list-bulleted" size={16} color={theme.colors.textMuted} />
                <Text style={styles.summaryLabel}>Total</Text>
              </View>
              <Text style={styles.summaryValue}>{resumo.total}</Text>
            </View>
            <View style={styles.summaryCard}>
              <View style={styles.summaryHeader}>
                <MaterialCommunityIcons name="check-circle-outline" size={16} color={theme.colors.textMuted} />
                <Text style={styles.summaryLabel}>Contados</Text>
              </View>
              <Text style={styles.summaryValue}>{resumo.contados}</Text>
            </View>
            <View style={styles.summaryCard}>
              <View style={styles.summaryHeader}>
                <MaterialCommunityIcons name="clock-outline" size={16} color={theme.colors.textMuted} />
                <Text style={styles.summaryLabel}>Pendentes</Text>
              </View>
              <Text style={styles.summaryValue}>{resumo.pendentes}</Text>
            </View>
            <View style={styles.summaryCard}>
              <View style={styles.summaryHeader}>
                <MaterialCommunityIcons name="alert-circle-outline" size={16} color={theme.colors.textMuted} />
                <Text style={styles.summaryLabel}>Diferenças</Text>
              </View>
              <Text style={styles.summaryValue}>{resumo.diferencas}</Text>
            </View>
          </View>

          {error && <Text style={styles.error}>{error}</Text>}

          <TouchableOpacity
            style={styles.button}
            onPress={async () => {
              const token = auth.token;
              const empresaId = auth.empresaId;
              const userId = auth.userId;
              if (!token || !empresaId || !userId) {
                setError('Sessão inválida.');
                return;
              }
              setStarting(true);
              setError(null);
              try {
                let tarefaAtual: TarefaContagem | null = null;
                const baseQuery = {
                  id_capa_inventario: idInventario,
                  id_usuario: userId,
                  per_page: 15,
                };

                const fetchByStatus = async (status: string) => {
                  const tarefas = await fetchTarefasContagem(
                    {
                      ...baseQuery,
                      status,
                    },
                    token,
                    empresaId
                  );
                  if (!tarefas.length) return null;
                  const matched =
                    tarefas.find((tarefa) => {
                      const tarefaUser =
                        (tarefa as unknown as { id_usuario?: number }).id_usuario ??
                        (tarefa as unknown as { usuario?: { id_usuario?: number } }).usuario
                          ?.id_usuario;
                      return !tarefaUser || tarefaUser === userId;
                    }) || tarefas[0];
                  return matched || null;
                };

                try {
                  tarefaAtual = await fetchByStatus('em_andamento');
                  if (!tarefaAtual) {
                    tarefaAtual = await fetchByStatus('pausada');
                  }
                  if (!tarefaAtual) {
                    tarefaAtual = await fetchByStatus('pendente');
                  }

                  if (tarefaAtual) {
                    const statusAtual = normalizeStatus(tarefaAtual.status);
                    if (!statusAtual) {
                      tarefaAtual = { ...tarefaAtual, status: 'em_andamento' };
                    }
                  }
                } catch (fetchError) {
                  tarefaAtual = null;
                }

                if (!tarefaAtual) {
                  const produtosCongelados = buildProdutosCongelados(items);
                  const queryParams = {
                    status: 'pendente',
                    id_capa_inventario: idInventario,
                    id_usuario: userId,
                    data_inicio: formatDateParam(capa?.data_inicio),
                    data_fim: formatDateParam(capa?.data_fechamento),
                    per_page: 15,
                  };
                  const payload = {
                    id_capa_inventario: idInventario,
                    id_usuario: userId,
                    id_supervisor: userId,
                    tipo_tarefa: 'contagem_inicial',
                    observacoes: 'Criada via app',
                    produtos: produtosCongelados.length ? produtosCongelados : undefined,
                  };
                  tarefaAtual = await criarTarefaContagem(
                    payload,
                    queryParams,
                    token,
                    empresaId
                  );
                  if (!tarefaAtual.status) {
                    tarefaAtual = { ...tarefaAtual, status: 'pendente' };
                  }
                }

                if (!tarefaAtual) {
                  throw new Error('Não foi possível obter a tarefa de contagem.');
                }

                const tarefaIdBase = getTarefaId(tarefaAtual);
                if (!tarefaIdBase) {
                  throw new Error('Não foi possível identificar a tarefa retornada.');
                }
                const statusAtual = normalizeStatus(tarefaAtual.status);

                if (statusAtual === 'pendente') {
                  const resposta = await iniciarTarefaContagem(
                    tarefaIdBase,
                    token,
                    empresaId,
                    'Início da contagem via app'
                  );
                  tarefaAtual = {
                    ...tarefaAtual,
                    ...resposta,
                    id_tarefa: resposta.id_tarefa ?? tarefaIdBase,
                  };
                } else if (statusAtual === 'pausada') {
                  const resposta = await retomarTarefaContagem(
                    tarefaIdBase,
                    token,
                    empresaId,
                    'Retomada da contagem via app'
                  );
                  tarefaAtual = {
                    ...tarefaAtual,
                    ...resposta,
                    id_tarefa: resposta.id_tarefa ?? tarefaIdBase,
                  };
                }

                navigation.navigate('Contagem', {
                  idInventario,
                  idTarefa: getTarefaId(tarefaAtual) ?? undefined,
                });
              } catch (err) {
                const message =
                  err instanceof Error ? err.message : 'Erro ao iniciar contagem.';
                setError(message);
              } finally {
                setStarting(false);
              }
            }}
            disabled={starting}
          >
            {starting ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <View style={styles.buttonContent}>
                <MaterialCommunityIcons name="play-circle-outline" size={18} color="#fff" />
                <Text style={styles.buttonText}>Iniciar</Text>
              </View>
            )}
          </TouchableOpacity>
        </>
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flexGrow: 1,
    backgroundColor: theme.colors.bg,
    padding: 20,
    gap: 16,
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  info: {
    marginTop: 10,
    color: theme.colors.textMuted,
  },
  card: {
    backgroundColor: theme.colors.surface,
    borderRadius: 18,
    padding: 20,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.colors.text,
  },
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 6,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 4,
  },
  metaText: {
    color: theme.colors.text,
  },
  summaryRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  summaryCard: {
    flexBasis: '48%',
    backgroundColor: theme.colors.surface,
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  summaryLabel: {
    color: theme.colors.textMuted,
    fontSize: 12,
  },
  summaryHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  summaryValue: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.colors.text,
  },
  button: {
    backgroundColor: theme.colors.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
  },
  buttonText: {
    color: '#fff',
    fontWeight: '700',
  },
  buttonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  error: {
    color: theme.colors.danger,
  },
});

export default InventorySummaryScreen;



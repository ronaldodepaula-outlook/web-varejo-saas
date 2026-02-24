import React, { useCallback, useMemo, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../context/AuthContext';
import {
  Contagem,
  InventarioCapa,
  InventarioItem,
  TarefaContagem,
  concluirTarefaContagem,
  createContagem,
  fetchCapaInventario,
  fetchContagens,
  fetchItensInventario,
  fetchTarefasContagem,
  iniciarTarefaContagem,
  criarTarefaContagem,
  retomarTarefaContagem,
} from '../services/api';
import { theme } from '../styles/theme';
import ScannerModal from '../components/ScannerModal';
import CountModal, { CountAction } from '../components/CountModal';

type Props = NativeStackScreenProps<RootStackParamList, 'Contagem'>;

type ItemView = InventarioItem & {
  quantidade_atual: number;
};

const formatNow = () => {
  const now = new Date();
  const iso = now.toISOString().slice(0, 19).replace('T', ' ');
  return iso;
};

const applyContagens = (items: InventarioItem[], contagens: Contagem[]): ItemView[] => {
  const grouped = new Map<number, Contagem[]>();
  contagens.forEach((c) => {
    const list = grouped.get(c.id_produto) || [];
    list.push(c);
    grouped.set(c.id_produto, list);
  });

  const results: ItemView[] = items.map((item) => {
    const list = grouped.get(item.id_produto) || [];
    const sorted = [...list].sort((a, b) => {
      const da = a.data_contagem ? new Date(a.data_contagem).getTime() : 0;
      const db = b.data_contagem ? new Date(b.data_contagem).getTime() : 0;
      return da - db;
    });

    let qty = 0;

    if (sorted.length === 0) {
      qty = item.quantidade_fisica ? Number(item.quantidade_fisica) : 0;
    } else {
      sorted.forEach((c) => {
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
    }

    return {
      ...item,
      quantidade_atual: qty,
    };
  });

  return results;
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

const CountScreen: React.FC<Props> = ({ route, navigation }) => {
  const { auth } = useAuth();
  const { idInventario, idTarefa } = route.params;
  const [capa, setCapa] = useState<InventarioCapa | null>(null);
  const [items, setItems] = useState<ItemView[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [finishing, setFinishing] = useState(false);
  const [taskId, setTaskId] = useState<number | null>(idTarefa ?? null);
  const [taskLoading, setTaskLoading] = useState(!idTarefa);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [mode, setMode] = useState<'ean' | 'descricao'>('ean');
  const [scannerOpen, setScannerOpen] = useState(false);
  const [selected, setSelected] = useState<ItemView | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);
  const listRef = useRef<FlatList<ItemView>>(null);
  const searchInputRef = useRef<TextInput>(null);

  const loadData = useCallback(async () => {
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
      const applied = applyContagens(itensData, contagensData);
      setItems(applied);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao carregar dados.';
      setError(message);
    } finally {
      setLoading(false);
    }
  }, [auth.empresaId, auth.token, idInventario]);

  React.useEffect(() => {
    loadData();
  }, [loadData]);

  React.useEffect(() => {
    if (idTarefa) {
      setTaskId(idTarefa);
    }
  }, [idTarefa]);

  React.useEffect(() => {
    const ensureTask = async () => {
      if (loading) {
        return;
      }
      if (taskId) {
        setTaskLoading(false);
        return;
      }
      const token = auth.token;
      const empresaId = auth.empresaId;
      const userId = auth.userId;
      if (!token || !empresaId) {
        setTaskLoading(false);
        return;
      }
      if (!userId) {
        setError('Usuário não identificado. Faça login novamente.');
        setTaskLoading(false);
        return;
      }
      setTaskLoading(true);
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
                (tarefa as unknown as { usuario?: { id_usuario?: number } }).usuario?.id_usuario;
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
          tarefaAtual = await criarTarefaContagem(payload, queryParams, token, empresaId);
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

        setTaskId(getTarefaId(tarefaAtual));
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Erro ao iniciar tarefa.';
        setError(message);
      } finally {
        setTaskLoading(false);
      }
    };

    ensureTask();
  }, [auth.empresaId, auth.token, auth.userId, idInventario, taskId, loading, items, capa]);

  const filteredItems = useMemo(() => {
    if (!search) return items;
    const term = search.toLowerCase();
    return items.filter((item) => {
      const produto = item.produto;
      if (!produto) return false;
      if (mode === 'ean') {
        return (produto.codigo_barras || '').toLowerCase().includes(term);
      }
      return (
        (produto.descricao || '').toLowerCase().includes(term) ||
        String(produto.id_produto).includes(term)
      );
    });
  }, [items, mode, search]);

  const openFormByEan = useCallback(
    (value: string) => {
      const term = value.trim();
      if (!term) return;
      const normalized = term.toLowerCase().replace(/\s+/g, '');
      const match = items.find((item) => {
        const codigo = (item.produto?.codigo_barras || '').toLowerCase().replace(/\s+/g, '');
        return codigo && codigo === normalized;
      });

      if (!match) {
        setActionError('Esse produto não está congelado para contagem.');
        setSelected(null);
        setSearch(term);
        return;
      }

      setActionError(null);
      setSelected(match);
      setSearch('');
      listRef.current?.scrollToOffset({ offset: 0, animated: true });
    },
    [items]
  );

  const handleScan = (value: string) => {
    setScannerOpen(false);
    setMode('ean');
    openFormByEan(value);
  };

  const handleSave = async (action: CountAction) => {
    if (!selected || !auth.token || !auth.empresaId || !auth.userId) return;

    setSaving(true);
    try {
      const payload = {
        id_inventario: selected.id_inventario,
        id_empresa: auth.empresaId,
        id_filial: selected.id_filial,
        id_produto: selected.id_produto,
        tipo_operacao: action.operation,
        quantidade: action.quantity,
        observacao: action.observacao || null,
        id_usuario: auth.userId,
        data_contagem: formatNow(),
      };

      await createContagem(payload, auth.token, auth.empresaId);

      setSelected(null);
      setActionError(null);
      setSearch('');
      await loadData();
      setTimeout(() => searchInputRef.current?.focus(), 150);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao salvar contagem.';
      setError(message);
    } finally {
      setSaving(false);
    }
  };

  const handleFinish = () => {
    if (!taskId) {
      setError('Tarefa não identificada. Retorne e inicie a contagem novamente.');
      return;
    }
    const token = auth.token;
    const empresaId = auth.empresaId;
    if (!token || !empresaId) {
      setError('Sessão inválida.');
      return;
    }

    Alert.alert(
      'Finalizar contagem',
      'Deseja concluir esta tarefa de contagem?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Concluir',
          style: 'destructive',
          onPress: async () => {
            try {
              setFinishing(true);
              await concluirTarefaContagem(
                taskId,
                token,
                empresaId,
                'Contagem finalizada com sucesso',
                false
              );
              navigation.goBack();
            } catch (err) {
              const message = err instanceof Error ? err.message : 'Erro ao concluir tarefa.';
              setError(message);
            } finally {
              setFinishing(false);
            }
          },
        },
      ],
      { cancelable: true }
    );
  };

  React.useEffect(() => {
    navigation.setOptions({
      headerTitle: () => (
        <View style={styles.headerTitleRow}>
          <Text style={styles.headerTitleText}>Contagem</Text>
          <TouchableOpacity
            onPress={handleFinish}
            disabled={finishing || saving || taskLoading || !taskId}
            style={[
              styles.headerFinishButton,
              (finishing || saving || taskLoading || !taskId) && styles.headerFinishButtonDisabled,
            ]}
          >
            {finishing ? (
              <ActivityIndicator color="#fff" size="small" />
            ) : (
              <MaterialCommunityIcons name="check-circle-outline" size={20} color="#fff" />
            )}
          </TouchableOpacity>
        </View>
      ),
    });
  }, [navigation, finishing, saving, taskLoading, taskId, handleFinish]);

  const handleSelectItem = (item: ItemView) => {
    setActionError(null);
    setSelected(item);
    setSearch('');
    listRef.current?.scrollToOffset({ offset: 0, animated: true });
  };

  const handleEanSubmit = () => {
    if (mode !== 'ean') return;
    openFormByEan(search);
  };

  const renderItem = ({ item }: { item: ItemView }) => {
    const produto = item.produto;
    const sistema = Number(item.quantidade_sistema || 0);
    const diferenca = item.quantidade_atual - sistema;
    const statusLabel = item.quantidade_atual > 0 ? 'Contado' : 'Pendente';
    const statusColor = item.quantidade_atual > 0 ? theme.colors.success : theme.colors.accent;

    return (
      <View style={styles.itemCard}>
        <View style={styles.itemHeader}>
          <Text style={styles.itemTitle}>{produto?.descricao || 'Produto'}</Text>
          <View style={[styles.badge, { backgroundColor: statusColor }]}>
            <Text style={styles.badgeText}>{statusLabel}</Text>
          </View>
        </View>
        <View style={styles.itemMetaLine}>
          <View style={styles.itemMetaGroup}>
            <MaterialCommunityIcons name="barcode-scan" size={12} color={theme.colors.textMuted} />
            <Text style={styles.itemMeta}>EAN: {produto?.codigo_barras || 'N/A'}</Text>
          </View>
          <View style={styles.itemMetaGroup}>
            <MaterialCommunityIcons name="tag-outline" size={12} color={theme.colors.textMuted} />
            <Text style={styles.itemMeta}>Produto: {produto?.id_produto}</Text>
          </View>
        </View>
        <View style={styles.itemRow}>
          <View style={styles.itemValueRow}>
            <MaterialCommunityIcons name="database-outline" size={12} color={theme.colors.text} />
            <Text style={styles.itemValue}>Sistema: {sistema}</Text>
          </View>
          <View style={styles.itemValueRow}>
            <MaterialCommunityIcons name="check-circle-outline" size={12} color={theme.colors.text} />
            <Text style={styles.itemValue}>Contado: {item.quantidade_atual}</Text>
          </View>
          <View style={styles.itemValueRow}>
            <MaterialCommunityIcons
              name="alert-circle-outline"
              size={12}
              color={diferenca !== 0 ? theme.colors.danger : theme.colors.text}
            />
            <Text style={[styles.itemValue, diferenca !== 0 && styles.itemDiff]}>
              Dif: {diferenca >= 0 ? `+${diferenca}` : diferenca}
            </Text>
          </View>
        </View>
        <TouchableOpacity
          style={styles.itemButton}
          onPress={() => handleSelectItem(item)}
          disabled={saving}
        >
          <View style={styles.itemButtonContent}>
            <MaterialCommunityIcons name="plus-circle-outline" size={18} color="#fff" />
            <Text style={styles.itemButtonText}>Contar</Text>
          </View>
        </TouchableOpacity>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={theme.colors.primary} />
          <Text style={styles.info}>Carregando inventário...</Text>
        </View>
      ) : (
        <>
          {capa && (
            <View style={styles.headerCard}>
              <Text style={styles.headerTitle}>Inventário #{capa.id_capa_inventario}</Text>
              <View style={styles.headerMetaRow}>
                <View style={styles.headerMetaItem}>
                  <MaterialCommunityIcons
                    name="text-box-outline"
                    size={14}
                    color={theme.colors.textMuted}
                  />
                  <Text style={styles.headerMetaText}>{capa.descricao}</Text>
                </View>
                <View style={styles.headerMetaItem}>
                  <MaterialCommunityIcons
                    name="storefront-outline"
                    size={14}
                    color={theme.colors.textMuted}
                  />
                  <Text style={styles.headerMetaText}>
                    {capa.filial?.nome_filial || 'N/A'}
                  </Text>
                </View>
              </View>
            </View>
          )}

          {taskLoading && <Text style={styles.taskHint}>Preparando tarefa...</Text>}
          {!taskId && !taskLoading && !error && (
            <Text style={styles.taskHint}>
              Tarefa não identificada. Volte e inicie a contagem novamente.
            </Text>
          )}

          <View style={styles.searchCard}>
            <View style={styles.toggleRow}>
              <TouchableOpacity
                style={[styles.toggleButton, mode === 'ean' && styles.toggleActive]}
                onPress={() => setMode('ean')}
              >
                <View style={styles.toggleContent}>
                  <MaterialCommunityIcons
                    name="barcode-scan"
                    size={16}
                    color={mode === 'ean' ? '#fff' : theme.colors.text}
                  />
                  <Text style={[styles.toggleText, mode === 'ean' && styles.toggleTextActive]}>
                    EAN
                  </Text>
                </View>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.toggleButton, mode === 'descricao' && styles.toggleActive]}
                onPress={() => setMode('descricao')}
              >
                <View style={styles.toggleContent}>
                  <MaterialCommunityIcons
                    name="text-box-outline"
                    size={16}
                    color={mode === 'descricao' ? '#fff' : theme.colors.text}
                  />
                  <Text style={[styles.toggleText, mode === 'descricao' && styles.toggleTextActive]}>
                    Descrição
                  </Text>
                </View>
              </TouchableOpacity>
            </View>

            <View style={styles.searchRow}>
              <TextInput
                value={search}
                onChangeText={(value) => {
                  setSearch(value);
                  if (actionError) setActionError(null);
                }}
                placeholder={mode === 'ean' ? 'Digite o EAN' : 'Buscar por descrição'}
                style={styles.searchInput}
                ref={searchInputRef}
                returnKeyType={mode === 'ean' ? 'search' : 'done'}
                onSubmitEditing={handleEanSubmit}
              />
              <TouchableOpacity style={styles.scanButton} onPress={() => setScannerOpen(true)}>
                <View style={styles.scanButtonContent}>
                  <MaterialCommunityIcons name="qrcode-scan" size={18} color="#fff" />
                </View>
              </TouchableOpacity>
            </View>
          </View>

          {error && <Text style={styles.error}>{error}</Text>}
          {actionError && <Text style={styles.error}>{actionError}</Text>}

          <FlatList
            ref={listRef}
            data={filteredItems}
            keyExtractor={(item) => String(item.id_inventario)}
            renderItem={renderItem}
            contentContainerStyle={styles.list}
            ListEmptyComponent={<Text style={styles.info}>Nenhum item encontrado.</Text>}
          />
        </>
      )}

      <ScannerModal
        visible={scannerOpen}
        onClose={() => setScannerOpen(false)}
        onScanned={handleScan}
      />

      <CountModal
        visible={!!selected}
        produtoLabel={selected?.produto?.descricao || 'Produto'}
        quantidadeAtual={selected?.quantidade_atual || 0}
        onClose={() => setSelected(null)}
        onSubmit={handleSave}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.colors.bg,
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
  headerCard: {
    backgroundColor: theme.colors.surface,
    margin: 16,
    padding: 16,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.colors.text,
  },
  headerMetaRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 6,
  },
  headerMetaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  headerMetaText: {
    fontSize: 12,
    color: theme.colors.textMuted,
  },
  headerTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  headerTitleText: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.colors.text,
  },
  headerFinishButton: {
    backgroundColor: theme.colors.success,
    width: 30,
    height: 30,
    borderRadius: 999,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerFinishButtonDisabled: {
    opacity: 0.6,
  },
  taskHint: {
    marginHorizontal: 16,
    marginBottom: 8,
    color: theme.colors.textMuted,
    fontSize: 12,
    textAlign: 'center',
  },
  searchCard: {
    backgroundColor: theme.colors.surface,
    margin: 16,
    padding: 14,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  toggleRow: {
    flexDirection: 'row',
    gap: 8,
  },
  toggleButton: {
    flex: 1,
    paddingVertical: 8,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: theme.colors.border,
    alignItems: 'center',
  },
  toggleActive: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  toggleText: {
    color: theme.colors.text,
    fontWeight: '600',
  },
  toggleContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  toggleTextActive: {
    color: '#fff',
  },
  searchRow: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 10,
  },
  searchInput: {
    flex: 1,
    backgroundColor: theme.colors.bg,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  scanButton: {
    backgroundColor: theme.colors.primary,
    borderRadius: 12,
    paddingHorizontal: 12,
    width: 44,
    alignItems: 'center',
    justifyContent: 'center',
  },
  scanButtonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  list: {
    paddingHorizontal: 16,
    paddingBottom: 24,
  },
  itemCard: {
    backgroundColor: theme.colors.surface,
    borderRadius: 12,
    padding: 10,
    borderWidth: 1,
    borderColor: theme.colors.border,
    marginBottom: 8,
  },
  itemHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  itemTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: theme.colors.text,
    flex: 1,
    marginRight: 8,
  },
  itemMeta: {
    color: theme.colors.textMuted,
    fontSize: 12,
  },
  itemMetaLine: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 8,
    marginTop: 4,
  },
  itemMetaGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    flex: 1,
  },
  itemRow: {
    marginTop: 6,
    flexDirection: 'row',
    justifyContent: 'space-between',
    flexWrap: 'wrap',
    gap: 6,
  },
  itemValue: {
    color: theme.colors.text,
    fontWeight: '600',
    fontSize: 12,
  },
  itemValueRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  itemDiff: {
    color: theme.colors.danger,
  },
  itemButton: {
    marginTop: 8,
    backgroundColor: theme.colors.primaryDark,
    paddingVertical: 8,
    borderRadius: 12,
    alignItems: 'center',
  },
  itemButtonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  itemButtonText: {
    color: '#fff',
    fontWeight: '700',
  },
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 999,
  },
  badgeText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 10,
  },
  error: {
    color: theme.colors.danger,
    marginHorizontal: 16,
    marginBottom: 8,
  },
});

export default CountScreen;

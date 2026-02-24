import React, { useCallback, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../context/AuthContext';
import {
  Contagem,
  InventarioCapa,
  InventarioItem,
  createContagem,
  fetchCapaInventario,
  fetchContagens,
  fetchItensInventario,
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

const CountScreen: React.FC<Props> = ({ route }) => {
  const { auth } = useAuth();
  const { idInventario } = route.params;
  const [capa, setCapa] = useState<InventarioCapa | null>(null);
  const [items, setItems] = useState<ItemView[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [mode, setMode] = useState<'ean' | 'descricao'>('ean');
  const [scannerOpen, setScannerOpen] = useState(false);
  const [selected, setSelected] = useState<ItemView | null>(null);

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

  const summary = useMemo(() => {
    const total = items.length;
    const contados = items.filter((i) => i.quantidade_atual > 0).length;
    const pendentes = total - contados;
    const diferencas = items.filter((i) => {
      const sistema = Number(i.quantidade_sistema || 0);
      return i.quantidade_atual !== 0 && i.quantidade_atual - sistema !== 0;
    }).length;
    return { total, contados, pendentes, diferencas };
  }, [items]);

  const handleScan = (value: string) => {
    setSearch(value);
    setMode('ean');
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
      await loadData();
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao salvar contagem.';
      setError(message);
    } finally {
      setSaving(false);
    }
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
        <Text style={styles.itemMeta}>EAN: {produto?.codigo_barras || 'N/A'}</Text>
        <Text style={styles.itemMeta}>Produto: {produto?.id_produto}</Text>
        <View style={styles.itemRow}>
          <Text style={styles.itemValue}>Sistema: {sistema}</Text>
          <Text style={styles.itemValue}>Contado: {item.quantidade_atual}</Text>
          <Text style={[styles.itemValue, diferenca !== 0 && styles.itemDiff]}>
            Dif: {diferenca >= 0 ? `+${diferenca}` : diferenca}
          </Text>
        </View>
        <TouchableOpacity
          style={styles.itemButton}
          onPress={() => setSelected(item)}
          disabled={saving}
        >
          <Text style={styles.itemButtonText}>Contar</Text>
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
              <Text style={styles.headerText}>{capa.descricao}</Text>
              <Text style={styles.headerText}>Filial: {capa.filial?.nome_filial || 'N/A'}</Text>
            </View>
          )}

          <View style={styles.summaryRow}>
            <View style={styles.summaryCard}>
              <Text style={styles.summaryLabel}>Total</Text>
              <Text style={styles.summaryValue}>{summary.total}</Text>
            </View>
            <View style={styles.summaryCard}>
              <Text style={styles.summaryLabel}>Contados</Text>
              <Text style={styles.summaryValue}>{summary.contados}</Text>
            </View>
            <View style={styles.summaryCard}>
              <Text style={styles.summaryLabel}>Pendentes</Text>
              <Text style={styles.summaryValue}>{summary.pendentes}</Text>
            </View>
            <View style={styles.summaryCard}>
              <Text style={styles.summaryLabel}>Diferenças</Text>
              <Text style={styles.summaryValue}>{summary.diferencas}</Text>
            </View>
          </View>

          <View style={styles.searchCard}>
            <View style={styles.toggleRow}>
              <TouchableOpacity
                style={[styles.toggleButton, mode === 'ean' && styles.toggleActive]}
                onPress={() => setMode('ean')}
              >
                <Text style={[styles.toggleText, mode === 'ean' && styles.toggleTextActive]}>EAN</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.toggleButton, mode === 'descricao' && styles.toggleActive]}
                onPress={() => setMode('descricao')}
              >
                <Text style={[styles.toggleText, mode === 'descricao' && styles.toggleTextActive]}>
                  Descrição
                </Text>
              </TouchableOpacity>
            </View>

            <View style={styles.searchRow}>
              <TextInput
                value={search}
                onChangeText={setSearch}
                placeholder={mode === 'ean' ? 'Digite o EAN' : 'Buscar por descrição'}
                style={styles.searchInput}
              />
              <TouchableOpacity style={styles.scanButton} onPress={() => setScannerOpen(true)}>
                <Text style={styles.scanButtonText}>Scanner</Text>
              </TouchableOpacity>
            </View>
          </View>

          {error && <Text style={styles.error}>{error}</Text>}

          <FlatList
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
  headerText: {
    marginTop: 4,
    color: theme.colors.textMuted,
  },
  summaryRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginHorizontal: 16,
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
  summaryValue: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.colors.text,
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
    paddingHorizontal: 14,
    justifyContent: 'center',
  },
  scanButtonText: {
    color: '#fff',
    fontWeight: '700',
  },
  list: {
    paddingHorizontal: 16,
    paddingBottom: 24,
  },
  itemCard: {
    backgroundColor: theme.colors.surface,
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
    borderColor: theme.colors.border,
    marginBottom: 12,
  },
  itemHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  itemTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: theme.colors.text,
    flex: 1,
    marginRight: 8,
  },
  itemMeta: {
    color: theme.colors.textMuted,
    marginTop: 4,
  },
  itemRow: {
    marginTop: 10,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  itemValue: {
    color: theme.colors.text,
    fontWeight: '600',
  },
  itemDiff: {
    color: theme.colors.danger,
  },
  itemButton: {
    marginTop: 10,
    backgroundColor: theme.colors.primaryDark,
    paddingVertical: 10,
    borderRadius: 12,
    alignItems: 'center',
  },
  itemButtonText: {
    color: '#fff',
    fontWeight: '700',
  },
  badge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 999,
  },
  badgeText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 11,
  },
  error: {
    color: theme.colors.danger,
    marginHorizontal: 16,
    marginBottom: 8,
  },
});

export default CountScreen;

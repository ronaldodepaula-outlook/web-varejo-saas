import React, { useEffect, useMemo, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  ScrollView,
} from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../context/AuthContext';
import {
  fetchCapasInventarioEmpresa,
  fetchCapaInventario,
  InventarioCapa,
} from '../services/api';
import { theme } from '../styles/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Inventario'>;

const InventoryScreen: React.FC<Props> = ({ navigation }) => {
  const { auth } = useAuth();
  const [numero, setNumero] = useState('');
  const [loading, setLoading] = useState(false);
  const [loadingCapas, setLoadingCapas] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [capa, setCapa] = useState<InventarioCapa | null>(null);
  const [capasEmpresa, setCapasEmpresa] = useState<InventarioCapa[]>([]);

  useEffect(() => {
    const loadCapas = async () => {
      if (!auth.token || !auth.empresaId) return;
      setLoadingCapas(true);
      setError(null);
      try {
        const list = await fetchCapasInventarioEmpresa(auth.empresaId, auth.token);
        setCapasEmpresa(list);
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Erro ao carregar inventários.';
        setError(message);
      } finally {
        setLoadingCapas(false);
      }
    };

    loadCapas();
  }, [auth.empresaId, auth.token]);

  const idsPermitidos = useMemo(
    () => new Set(capasEmpresa.map((item) => item.id_capa_inventario)),
    [capasEmpresa]
  );

  const handleLoad = async () => {
    if (!numero) {
      setError('Informe o número do inventário.');
      return;
    }
    setLoading(true);
    setError(null);
    setCapa(null);
    try {
      const id = Number(numero);
      if (!auth.token || !auth.empresaId) {
        throw new Error('Sessão inválida.');
      }
      if (idsPermitidos.size > 0 && !idsPermitidos.has(id)) {
        throw new Error('Inventário inválido.');
      }
      const data = await fetchCapaInventario(id, auth.token, auth.empresaId);
      setCapa(data);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao carregar inventário.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  const handleStart = () => {
    if (!capa) return;
    navigation.navigate('Contagem', { idInventario: capa.id_capa_inventario });
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <View style={styles.card}>
        <Text style={styles.title}>Selecionar Inventário</Text>
        <Text style={styles.subtitle}>Informe o número do inventário para iniciar a contagem.</Text>
        {loadingCapas && <Text style={styles.info}>Carregando inventários da empresa...</Text>}

        <View style={styles.field}>
          <Text style={styles.label}>Número do inventário</Text>
          <TextInput
            value={numero}
            onChangeText={setNumero}
            keyboardType="numeric"
            placeholder="Ex: 123"
            style={styles.input}
          />
        </View>

        {error && <Text style={styles.error}>{error}</Text>}

        <TouchableOpacity style={styles.button} onPress={handleLoad} disabled={loading}>
          {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>Carregar</Text>}
        </TouchableOpacity>
      </View>

      {capa && (
        <View style={styles.detailsCard}>
          <Text style={styles.detailsTitle}>Inventário #{capa.id_capa_inventario}</Text>
          <Text style={styles.detailsText}>Descrição: {capa.descricao}</Text>
          <Text style={styles.detailsText}>Status: {capa.status}</Text>
          <Text style={styles.detailsText}>Filial: {capa.filial?.nome_filial || 'N/A'}</Text>
          <Text style={styles.detailsText}>Responsável: {capa.usuario?.nome || 'N/A'}</Text>

          <TouchableOpacity style={[styles.button, styles.startButton]} onPress={handleStart}>
            <Text style={styles.buttonText}>Iniciar Contagem</Text>
          </TouchableOpacity>
        </View>
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
  card: {
    backgroundColor: theme.colors.surface,
    borderRadius: 18,
    padding: 20,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 10,
    elevation: 2,
  },
  title: {
    fontSize: 20,
    fontWeight: '700',
    color: theme.colors.text,
  },
  subtitle: {
    color: theme.colors.textMuted,
    marginTop: 6,
    marginBottom: 16,
  },
  info: {
    color: theme.colors.textMuted,
    marginBottom: 8,
  },
  field: {
    marginBottom: 12,
  },
  label: {
    fontSize: 13,
    color: theme.colors.textMuted,
    marginBottom: 6,
  },
  input: {
    backgroundColor: theme.colors.bg,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 12,
    fontSize: 16,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  button: {
    backgroundColor: theme.colors.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
  },
  startButton: {
    marginTop: 16,
  },
  buttonText: {
    color: '#fff',
    fontWeight: '700',
  },
  error: {
    color: theme.colors.danger,
    marginBottom: 8,
  },
  detailsCard: {
    backgroundColor: theme.colors.surface,
    borderRadius: 18,
    padding: 20,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  detailsTitle: {
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
    color: theme.colors.text,
  },
  detailsText: {
    color: theme.colors.text,
    marginBottom: 4,
  },
});

export default InventoryScreen;

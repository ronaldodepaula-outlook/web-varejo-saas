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
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../context/AuthContext';
import { fetchCapasInventarioEmpresa, InventarioCapa } from '../services/api';
import { theme } from '../styles/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Inventario'>;

const InventoryScreen: React.FC<Props> = ({ navigation }) => {
  const { auth } = useAuth();
  const [numero, setNumero] = useState('');
  const [loading, setLoading] = useState(false);
  const [loadingCapas, setLoadingCapas] = useState(false);
  const [error, setError] = useState<string | null>(null);
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
    try {
      const id = Number(numero);
      if (!auth.token || !auth.empresaId) {
        throw new Error('Sessão inválida.');
      }
      if (idsPermitidos.size > 0 && !idsPermitidos.has(id)) {
        throw new Error('Inventário não pertence à sua empresa.');
      }
      navigation.navigate('InventarioResumo', { idInventario: id });
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Erro ao carregar inventário.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <View style={styles.card}>
        <View style={styles.titleRow}>
          <MaterialCommunityIcons
            name="clipboard-list-outline"
            size={22}
            color={theme.colors.primary}
          />
          <Text style={styles.title}>Selecionar Inventário</Text>
        </View>
        <Text style={styles.subtitle}>Informe o número do inventário para iniciar a contagem.</Text>
        {loadingCapas && <Text style={styles.info}>Carregando inventários da empresa...</Text>}

        <View style={styles.field}>
          <Text style={styles.label}>Número do inventário</Text>
          <View style={styles.inputRow}>
            <MaterialCommunityIcons name="barcode-scan" size={18} color={theme.colors.textMuted} />
            <TextInput
              value={numero}
              onChangeText={setNumero}
              keyboardType="numeric"
              placeholder="Ex: 123"
              style={styles.input}
            />
          </View>
        </View>

        {error && <Text style={styles.error}>{error}</Text>}

        <TouchableOpacity style={styles.button} onPress={handleLoad} disabled={loading}>
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <View style={styles.buttonContent}>
              <MaterialCommunityIcons name="play-circle-outline" size={18} color="#fff" />
              <Text style={styles.buttonText}>Continuar</Text>
            </View>
          )}
        </TouchableOpacity>
      </View>
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
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
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
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: theme.colors.bg,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  input: {
    flex: 1,
    fontSize: 16,
    color: theme.colors.text,
  },
  button: {
    backgroundColor: theme.colors.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
  },
  buttonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  buttonText: {
    color: '#fff',
    fontWeight: '700',
  },
  error: {
    color: theme.colors.danger,
    marginBottom: 8,
  },
});

export default InventoryScreen;

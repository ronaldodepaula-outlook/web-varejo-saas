import React, { useEffect, useState } from 'react';
import {
  Modal,
  View,
  Text,
  TouchableOpacity,
  TextInput,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { theme } from '../styles/theme';

export type CountAction = {
  operation: 'Adicionar' | 'Substituir' | 'Excluir';
  quantity: number;
  observacao: string;
};

type CountModalProps = {
  visible: boolean;
  produtoLabel: string;
  quantidadeAtual: number;
  onClose: () => void;
  onSubmit: (action: CountAction) => void;
};

const CountModal: React.FC<CountModalProps> = ({
  visible,
  produtoLabel,
  quantidadeAtual,
  onClose,
  onSubmit,
}) => {
  const [operation, setOperation] = useState<CountAction['operation']>('Adicionar');
  const [quantity, setQuantity] = useState('');
  const [observacao, setObservacao] = useState('');
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (visible) {
      setOperation('Adicionar');
      setQuantity('');
      setObservacao('');
      setError(null);
    }
  }, [visible]);

  const handleSubmit = () => {
    const parsed = Number(quantity.replace(',', '.'));
    const safeQuantity = Number.isFinite(parsed) ? parsed : 0;
    if (operation !== 'Excluir' && safeQuantity <= 0) {
      setError('Informe uma quantidade válida.');
      return;
    }
    onSubmit({
      operation,
      quantity: operation === 'Excluir' ? 0 : safeQuantity,
      observacao: observacao.trim(),
    });
  };

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.modal}
        >
          <Text style={styles.title}>Contar Item</Text>
          <Text style={styles.subtitle}>{produtoLabel}</Text>
          <Text style={styles.current}>Quantidade atual: {quantidadeAtual}</Text>

          <View style={styles.row}>
            {(['Adicionar', 'Substituir', 'Excluir'] as CountAction['operation'][]).map((op) => (
              <TouchableOpacity
                key={op}
                onPress={() => {
                  setOperation(op);
                  setError(null);
                }}
                style={[styles.choice, operation === op && styles.choiceActive]}
              >
                <Text style={[styles.choiceText, operation === op && styles.choiceTextActive]}>
                  {op}
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          {operation !== 'Excluir' && (
            <View style={styles.field}>
              <Text style={styles.label}>Quantidade</Text>
              <TextInput
                value={quantity}
                onChangeText={(value) => {
                  setQuantity(value);
                  if (error) setError(null);
                }}
                placeholder="0"
                keyboardType="numeric"
                style={styles.input}
              />
            </View>
          )}

          {error && <Text style={styles.error}>{error}</Text>}

          <View style={styles.field}>
            <Text style={styles.label}>Observação (opcional)</Text>
            <TextInput
              value={observacao}
              onChangeText={setObservacao}
              placeholder="Ex: caixa danificada"
              style={[styles.input, styles.inputMultiline]}
              multiline
              numberOfLines={3}
            />
          </View>

          <View style={styles.actions}>
            <TouchableOpacity style={[styles.button, styles.secondary]} onPress={onClose}>
              <Text style={styles.secondaryText}>Cancelar</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.button, styles.primary]} onPress={handleSubmit}>
              <Text style={styles.primaryText}>Salvar</Text>
            </TouchableOpacity>
          </View>
        </KeyboardAvoidingView>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.35)',
    justifyContent: 'flex-end',
  },
  modal: {
    backgroundColor: theme.colors.surface,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 20,
  },
  title: {
    fontSize: 20,
    fontWeight: '700',
    color: theme.colors.text,
  },
  subtitle: {
    marginTop: 4,
    color: theme.colors.textMuted,
  },
  current: {
    marginTop: 8,
    fontWeight: '600',
    color: theme.colors.text,
  },
  row: {
    flexDirection: 'row',
    marginTop: 16,
    gap: 8,
  },
  choice: {
    flex: 1,
    borderWidth: 1,
    borderColor: theme.colors.border,
    paddingVertical: 10,
    borderRadius: 10,
    alignItems: 'center',
  },
  choiceActive: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  choiceText: {
    color: theme.colors.text,
    fontWeight: '600',
    fontSize: 13,
  },
  choiceTextActive: {
    color: '#fff',
  },
  field: {
    marginTop: 16,
  },
  label: {
    fontSize: 13,
    color: theme.colors.textMuted,
    marginBottom: 6,
  },
  input: {
    backgroundColor: theme.colors.bg,
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 16,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  inputMultiline: {
    minHeight: 70,
    textAlignVertical: 'top',
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 20,
    gap: 12,
  },
  error: {
    marginTop: 8,
    color: theme.colors.danger,
  },
  button: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
  },
  primary: {
    backgroundColor: theme.colors.primary,
  },
  secondary: {
    backgroundColor: theme.colors.bg,
  },
  primaryText: {
    color: '#fff',
    fontWeight: '700',
  },
  secondaryText: {
    color: theme.colors.text,
    fontWeight: '600',
  },
});

export default CountModal;

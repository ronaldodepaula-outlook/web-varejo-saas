import React, { useEffect, useState } from 'react';
import { Modal, View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { theme } from '../styles/theme';

type ScannerModalProps = {
  visible: boolean;
  onClose: () => void;
  onScanned: (value: string) => void;
};

const ScannerModal: React.FC<ScannerModalProps> = ({ visible, onClose, onScanned }) => {
  const [permission, requestPermission] = useCameraPermissions();
  const [scanned, setScanned] = useState(false);

  useEffect(() => {
    let mounted = true;
    if (visible) {
      (async () => {
        await requestPermission();
        if (mounted) {
          setScanned(false);
        }
      })();
    }
    return () => {
      mounted = false;
    };
  }, [requestPermission, visible]);

  const handleBarCodeScanned = ({ data }: { data: string }) => {
    if (scanned) return;
    setScanned(true);
    onScanned(data);
    onClose();
  };

  return (
    <Modal visible={visible} animationType="slide">
      <View style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.title}>Escanear EAN</Text>
          <TouchableOpacity onPress={onClose}>
            <Text style={styles.close}>Fechar</Text>
          </TouchableOpacity>
        </View>

        {!permission && (
          <View style={styles.center}>
            <Text style={styles.info}>Solicitando permissão da câmera...</Text>
          </View>
        )}
        {permission?.granted === false && (
          <View style={styles.center}>
            <Text style={styles.info}>Permissão da câmera negada.</Text>
            <TouchableOpacity style={styles.button} onPress={onClose}>
              <Text style={styles.buttonText}>Voltar</Text>
            </TouchableOpacity>
          </View>
        )}
        {permission?.granted && (
          <CameraView
            onBarcodeScanned={handleBarCodeScanned}
            style={StyleSheet.absoluteFillObject}
            barcodeScannerSettings={{
              barcodeTypes: ['ean13', 'ean8', 'upc_a', 'upc_e', 'code128'],
            }}
          />
        )}
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  header: {
    height: 80,
    paddingTop: 32,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: 'rgba(0,0,0,0.6)',
    zIndex: 2,
  },
  title: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '700',
  },
  close: {
    color: '#fff',
    fontWeight: '600',
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    backgroundColor: theme.colors.bg,
  },
  info: {
    color: theme.colors.text,
    fontSize: 16,
    textAlign: 'center',
    marginBottom: 16,
  },
  button: {
    backgroundColor: theme.colors.primary,
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: theme.radius.md,
  },
  buttonText: {
    color: '#fff',
    fontWeight: '600',
  },
});

export default ScannerModal;

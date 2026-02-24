import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { TouchableOpacity, Text } from 'react-native';
import { useAuth } from '../context/AuthContext';
import LoginScreen from '../screens/LoginScreen';
import InventoryScreen from '../screens/InventoryScreen';
import InventorySummaryScreen from '../screens/InventorySummaryScreen';
import CountScreen from '../screens/CountScreen';
import { theme } from '../styles/theme';

export type RootStackParamList = {
  Login: undefined;
  Inventario: undefined;
  InventarioResumo: { idInventario: number };
  Contagem: { idInventario: number; idTarefa?: number };
};

const Stack = createNativeStackNavigator<RootStackParamList>();

const LogoutButton: React.FC = () => {
  const { logout } = useAuth();
  return (
    <TouchableOpacity onPress={logout} style={{ paddingHorizontal: 12, paddingVertical: 6 }}>
      <Text style={{ color: theme.colors.danger, fontWeight: '600' }}>Sair</Text>
    </TouchableOpacity>
  );
};

const AppNavigator: React.FC = () => {
  const { auth } = useAuth();

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: theme.colors.surface },
        headerTintColor: theme.colors.text,
        headerTitleStyle: { fontWeight: '700' },
      }}
    >
      {!auth.token ? (
        <Stack.Screen name="Login" component={LoginScreen} options={{ headerShown: false }} />
      ) : (
        <>
          <Stack.Screen
            name="Inventario"
            component={InventoryScreen}
            options={{
              title: 'Inventário',
              headerRight: () => <LogoutButton />,
            }}
          />
          <Stack.Screen
            name="InventarioResumo"
            component={InventorySummaryScreen}
            options={{
              title: 'Resumo do Inventário',
              headerRight: () => <LogoutButton />,
            }}
          />
          <Stack.Screen
            name="Contagem"
            component={CountScreen}
            options={{
              title: 'Contagem',
              headerRight: () => <LogoutButton />,
            }}
          />
        </>
      )}
    </Stack.Navigator>
  );
};

export default AppNavigator;

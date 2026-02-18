# NexusFlow - Sistema SaaS Multi-Empresas

![NexusFlow Logo](assets/images/logo.png)

## Documentacao
Arquivos principais:
- docs/DEV_GUIDE.md
- docs/CONSULTOR_GUIDE.md
- docs/OPS_GUIDE.md
- docs/SYSTEM_FLOW.md
- docs/SALES_DECK.md
- docs/GITHUB.md

## 📋 Sobre o Projeto

**NexusFlow** é um sistema SaaS (Software as a Service) multi-empresas desenvolvido para gerenciar múltiplas organizações dentro de uma única plataforma. O sistema oferece controle granular de usuários, filiais, licenciamento e funcionalidades específicas para diferentes segmentos de mercado.

## 🚀 Características Principais

### Multi-Tenant Architecture
- **Isolamento de dados** por empresa
- **Administração centralizada** para super admins
- **Gestão independente** para cada empresa
- **Escalabilidade** para múltiplas organizações

### Gestão de Usuários e Permissões
- **Papéis hierárquicos**: Super Admin, Admin Empresa, Gerente, Operador, Visualizador
- **Controle granular** de permissões por funcionalidade
- **Gestão por filiais** com usuários distribuídos geograficamente
- **Sistema de convites** e ativação por e-mail

### Licenciamento Inteligente
- **Período trial** de 3 meses gratuitos
- **Controle automático** de expiração de licenças
- **Planos flexíveis** adaptáveis ao tamanho da empresa
- **Faturamento integrado** com notificações

### Multi-Segmentos
Suporte nativo para diversos segmentos:
- 🏪 **Varejo**
- 🏭 **Indústria**
- 🏗️ **Construção**
- 🏦 **Financeiro**
- 📢 **Marketing**
- 💻 **Tecnologia**
- 🏥 **Saúde**
- 📚 **Educação**

## 🛠️ Tecnologias Utilizadas

### Frontend
- **HTML5** - Estrutura semântica moderna
- **CSS3** - Estilos customizados com variáveis CSS
- **JavaScript ES6+** - Funcionalidades interativas
- **Bootstrap 5.3** - Framework CSS responsivo
- **Bootstrap Icons** - Biblioteca de ícones
- **Chart.js** - Gráficos e visualizações

### Fontes e Design
- **Google Fonts (Inter)** - Tipografia moderna
- **Design System** - Paleta de cores consistente
- **Responsive Design** - Adaptável a todos os dispositivos

## 📁 Estrutura do Projeto

```
nexusflow-saas/
├── assets/
│   ├── css/
│   │   └── nexusflow.css          # Estilos principais
│   ├── js/
│   │   └── nexusflow.js           # JavaScript principal
│   ├── images/                    # Imagens e logos
│   └── fonts/                     # Fontes customizadas
├── components/
│   └── base-layout.html           # Template base
├── pages/
│   ├── auth/
│   │   ├── login.html             # Página de login
│   │   ├── register.html          # Cadastro de empresa
│   │   └── forgot-password.html   # Recuperação de senha
│   ├── admin/
│   │   ├── companies.html         # Gestão de empresas
│   │   ├── system-users.html      # Usuários do sistema
│   │   └── billing.html           # Faturamento
│   ├── company/
│   │   ├── users.html             # Gestão de usuários
│   │   ├── branches.html          # Gestão de filiais
│   │   └── settings.html          # Configurações
│   ├── user/
│   │   └── profile.html           # Perfil do usuário
│   ├── dashboard.html             # Dashboard principal
│   ├── reports.html               # Relatórios
│   └── analytics.html             # Analytics
├── docs/                          # Documentação
└── README.md                      # Este arquivo
```

## 🎨 Identidade Visual

### Paleta de Cores
- **Azul Escuro (#2C3E50)**: Elementos primários, navegação
- **Azul Ciano (#3498DB)**: Botões, links, destaques
- **Cinza Claro (#ECF0F1)**: Fundos, áreas de conteúdo
- **Verde Sucesso (#2ECC71)**: Mensagens positivas
- **Vermelho Alerta (#E74C3C)**: Mensagens de erro
- **Laranja Aviso (#F39C12)**: Mensagens de atenção

### Logo e Branding
- **Nome**: NexusFlow
- **Conceito**: Conexão e fluxo contínuo de informações
- **Ícone**: Diagrama de conexões estilizado
- **Tipografia**: Inter (Google Fonts)

## 🔐 Níveis de Acesso

### Super Admin (Administrador Geral)
- Visualizar e gerenciar todas as empresas
- Controlar licenças e faturamento
- Gerenciar usuários do sistema
- Acessar relatórios globais

### Admin Empresa (Administrador da Empresa)
- Gerenciar usuários da própria empresa
- Configurar filiais e departamentos
- Controlar permissões internas
- Acessar relatórios da empresa

### Gerente
- Gerenciar usuários de sua filial/departamento
- Visualizar relatórios específicos
- Executar operações limitadas

### Operador
- Executar operações do dia a dia
- Acessar funcionalidades específicas
- Visualizar dados permitidos

### Visualizador
- Apenas visualização de dados
- Sem permissões de edição
- Acesso limitado a relatórios

## 🚀 Como Usar

### 1. Configuração Inicial
1. Faça o download do projeto
2. Extraia os arquivos em seu servidor web
3. Configure as URLs das APIs no arquivo `assets/js/nexusflow.js`
4. Ajuste as configurações de e-mail para envio de convites

### 2. Primeiro Acesso
1. Acesse a página de cadastro (`pages/auth/register.html`)
2. Cadastre a primeira empresa (receberá status de Super Admin)
3. Configure as informações básicas da empresa
4. Comece a adicionar usuários e filiais

### 3. Integração com APIs
O sistema foi desenvolvido como template frontend, preparado para integração com APIs REST. Os pontos de integração estão marcados nos arquivos JavaScript com comentários `// Integração com API`.

#### Endpoints Sugeridos:
```
POST /api/auth/login
POST /api/auth/register
GET  /api/companies
POST /api/companies
GET  /api/users
POST /api/users
GET  /api/branches
POST /api/branches
GET  /api/reports
```

## 📱 Responsividade

O sistema é totalmente responsivo e funciona perfeitamente em:
- **Desktop** (1920px+)
- **Laptop** (1366px - 1919px)
- **Tablet** (768px - 1365px)
- **Mobile** (320px - 767px)

## 🔧 Personalização

### Cores
Edite as variáveis CSS no arquivo `assets/css/nexusflow.css`:
```css
:root {
    --primary-dark: #2C3E50;
    --primary-blue: #3498DB;
    /* ... outras variáveis */
}
```

### Logo
Substitua o ícone no componente `.logo-icon` ou adicione sua própria imagem em `assets/images/`.

### Funcionalidades
Adicione novas páginas seguindo a estrutura existente e utilizando o template base em `components/base-layout.html`.

## 📊 Funcionalidades Implementadas

### ✅ Autenticação
- [x] Login com e-mail e senha
- [x] Cadastro de empresa com wizard
- [x] Recuperação de senha
- [x] Integração com Google/Microsoft (preparado)

### ✅ Dashboard
- [x] Métricas principais
- [x] Gráficos interativos
- [x] Atividades recentes
- [x] Ações rápidas

### ✅ Gestão de Empresas
- [x] Lista de empresas
- [x] Filtros avançados
- [x] Status de licenças
- [x] Ações em massa

### ✅ Gestão de Usuários
- [x] CRUD completo de usuários
- [x] Sistema de papéis e permissões
- [x] Convites por e-mail
- [x] Gestão por filiais

### ✅ Gestão de Filiais
- [x] Cadastro de filiais
- [x] Visualização em cards
- [x] Estatísticas por filial
- [x] Integração com mapa (preparado)

## 🔮 Próximos Passos

### Backend Integration
- [ ] Implementar APIs REST
- [ ] Configurar banco de dados
- [ ] Sistema de autenticação JWT
- [ ] Upload de arquivos

### Funcionalidades Avançadas
- [ ] Relatórios avançados
- [ ] Notificações push
- [ ] Integração com mapas
- [ ] Sistema de backup

### Mobile App
- [ ] Aplicativo React Native
- [ ] Sincronização offline
- [ ] Notificações móveis

## 📞 Suporte

Para dúvidas, sugestões ou problemas:
- **E-mail**: suporte@nexusflow.com
- **Documentação**: Consulte a pasta `docs/`
- **Issues**: Reporte problemas no repositório

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

**NexusFlow** - Conectando empresas, simplificando gestão.

*Desenvolvido com ❤️ para facilitar a administração de múltiplas empresas.*

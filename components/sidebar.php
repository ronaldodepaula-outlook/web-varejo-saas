<?php
// Sidebar padrao para toda a navegacao
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$segmento = $segmento ?? ($_SESSION['segmento'] ?? 'varejo');
$currentView = $_GET['view'] ?? 'home';

function normalize_view_name($value) {
    $value = is_string($value) ? $value : '';
    $value = str_replace('-', '_', $value);
    return strtolower($value);
}

function is_active_view($view, $currentView) {
    return normalize_view_name($view) === normalize_view_name($currentView);
}

$iconMap = [
    'home' => 'bi-house',
    'home_admin' => 'bi-grid',
    'home_admins' => 'bi-grid-1x2',
    'home_dashboardpdv' => 'bi-speedometer2',
    'admin_dashboard_marcenaria' => 'bi-grid',
    'admin_marcenarias' => 'bi-house-gear',
    'admin_minhas_filiais' => 'bi-building-check',
    'admin_orcamentos' => 'bi-journal-text',
    'admin_ordens_producao' => 'bi-diagram-3',
    'admin_estoque' => 'bi-box',
    'gestao_estoque' => 'bi-box-seam',
    'admin_estoque_auditorias' => 'bi-clipboard-check',
    'admin_inventarios' => 'bi-clipboard-data',
    'admin_tarefas_contagem' => 'bi-list-check',
    'admin_mercadorias' => 'bi-boxes',
    'admin_produtos' => 'bi-bag',
    'admin_vendasassistidas' => 'bi-cart-check',
    'admin_nfs' => 'bi-receipt',
    'admin_empresas' => 'bi-building',
    'admin_filiais' => 'bi-diagram-3',
    'admin_filiais_empresa' => 'bi-building-fill',
    'adm_clientes' => 'bi-people',
    'adm_fornecedores' => 'bi-truck',
    'admin_contas_pagar' => 'bi-wallet2',
    'admin_contas_receber' => 'bi-wallet',
    'financeiro' => 'bi-cash-coin',
    'compras' => 'bi-bag-check',
    'compras_cotacoes' => 'bi-clipboard-check',
    'compras_pedidos' => 'bi-file-earmark-text',
    'compras_recebimentos' => 'bi-box-arrow-in-down',
    'precificacao' => 'bi-tags',
    'precificacao_historico' => 'bi-clock-history',
    'precificacao_atualizacoes' => 'bi-arrow-repeat',
    'precificacao_promocoes' => 'bi-lightning-charge',
    'precificacao_precos_vigentes' => 'bi-currency-dollar',
    'gestao_usuarios' => 'bi-people',
];

$menus = [
    'marcenaria' => [
        'header' => 'Segmento Marcenaria',
        'description' => 'Gestao de producao, orcamentos e materiais.',
        'items' => [
            ['view' => 'admin-dashboard_marcenaria', 'label' => 'Dashboard'],
            ['view' => 'admin-marcenarias', 'label' => 'Marcenarias'],
            ['view' => 'admin-minhas-filiais', 'label' => 'Minhas Filiais'],
            ['view' => 'adm-clientes', 'label' => 'Clientes'],
            ['view' => 'adm-fornecedores', 'label' => 'Fornecedores'],
            [
                'label' => 'Financeiro',
                'icon' => 'financeiro',
                'children' => [
                    ['view' => 'admin-contas_pagar', 'label' => 'Contas a Pagar'],
                    ['view' => 'admin-contas_receber', 'label' => 'Contas a Receber'],
                ],
            ],
            ['view' => 'admin-orcamentos', 'label' => 'Orcamentos'],
            ['view' => 'admin-ordens_producao', 'label' => 'Ordens de Producao'],
            [
                'label' => 'Gestao Estoque',
                'icon' => 'gestao_estoque',
                'children' => [
                    ['view' => 'admin-estoque', 'label' => 'Estoque'],
                    ['view' => 'admin-estoque-auditorias', 'label' => 'Auditorias'],
                ],
            ],
            [
                'label' => 'Precificacao',
                'icon' => 'precificacao',
                'children' => [
                    ['view' => 'precificacao-historico', 'label' => 'Historico de Precos'],
                    ['view' => 'precificacao-atualizacoes', 'label' => 'Atualizacoes de Precos'],
                    ['view' => 'precificacao-promocoes', 'label' => 'Promocoes'],
                    ['view' => 'precificacao-precos-vigentes', 'label' => 'Precos Vigentes'],
                ],
            ],
            ['view' => 'admin-inventarios', 'label' => 'Inventarios'],
            ['view' => 'admin-tarefas-contagem', 'label' => 'Tarefas de Contagem'],
            ['view' => 'admin-mercadorias', 'label' => 'Mercadorias'],
            ['view' => 'admin-produtos', 'label' => 'Produtos'],
        ],
    ],
    'varejo' => [
        'header' => 'Segmento Varejo',
        'description' => 'Operacao de vendas, PDV e financeiro.',
        'items' => [
            ['view' => 'home-DashboardPDV', 'label' => 'Dashboard PDV'],
            ['view' => 'admin-vendasAssistidas', 'label' => 'Vendas Assistidas'],
            ['view' => 'adm-clientes', 'label' => 'Clientes'],
            ['view' => 'adm-fornecedores', 'label' => 'Fornecedores'],
            [
                'label' => 'Compras',
                'icon' => 'compras',
                'children' => [
                    ['view' => 'compras-cotacoes', 'label' => 'Cotacoes'],
                    ['view' => 'compras-pedidos', 'label' => 'Pedido de Compra'],
                    ['view' => 'compras-recebimentos', 'label' => 'Recebimento'],
                ],
            ],
            [
                'label' => 'Financeiro',
                'icon' => 'financeiro',
                'children' => [
                    ['view' => 'admin-contas_pagar', 'label' => 'Contas a Pagar'],
                    ['view' => 'admin-contas_receber', 'label' => 'Contas a Receber'],
                ],
            ],
            ['view' => 'admin-minhas-filiais', 'label' => 'Minhas Filiais'],
            ['view' => 'home', 'label' => 'Home'],
            ['view' => 'admin-empresas', 'label' => 'Empresas'],
            [
                'label' => 'Gestao Estoque',
                'icon' => 'gestao_estoque',
                'children' => [
                    ['view' => 'admin-estoque', 'label' => 'Estoque'],
                    ['view' => 'admin-estoque-auditorias', 'label' => 'Auditorias'],
                ],
            ],
            [
                'label' => 'Precificacao',
                'icon' => 'precificacao',
                'children' => [
                    ['view' => 'precificacao-historico', 'label' => 'Historico de Precos'],
                    ['view' => 'precificacao-atualizacoes', 'label' => 'Atualizacoes de Precos'],
                    ['view' => 'precificacao-promocoes', 'label' => 'Promocoes'],
                    ['view' => 'precificacao-precos-vigentes', 'label' => 'Precos Vigentes'],
                ],
            ],
            ['view' => 'admin-inventarios', 'label' => 'Inventarios'],
            ['view' => 'admin-tarefas-contagem', 'label' => 'Tarefas de Contagem'],
            ['view' => 'admin-mercadorias', 'label' => 'Mercadorias'],
            ['view' => 'admin-nfs', 'label' => 'Notas Fiscais'],
            ['view' => 'admin-produtos', 'label' => 'Produtos'],
        ],
    ],
    'admin' => [
        'header' => 'Administracao do Sistema',
        'description' => 'Configuracoes e controle global.',
        'items' => [
            ['view' => 'home-admins', 'label' => 'Dashboard Geral'],
            ['view' => 'admin-empresas', 'label' => 'Empresas'],
            ['view' => 'admin-filiais', 'label' => 'Filiais'],
            ['view' => 'gestao_usuarios', 'label' => 'Usuarios'],
            ['view' => 'home-admin', 'label' => 'Painel Admin Empresa'],
        ],
    ],
];

$menu = $menus[$segmento] ?? $menus['varejo'];

function resolve_icon($item, $iconMap) {
    if (isset($item['icon']) && $item['icon'] !== '') {
        $icon = (string)$item['icon'];
        if (strpos($icon, 'bi-') === 0) {
            return $icon;
        }
        $key = normalize_view_name($icon);
        return $iconMap[$key] ?? 'bi-circle';
    }

    if (isset($item['view']) && $item['view'] !== '') {
        $key = normalize_view_name($item['view']);
        return $iconMap[$key] ?? 'bi-circle';
    }

    return 'bi-circle';
}

function render_menu_items($items, $iconMap, $currentView) {
    static $dropdownIndex = 0;
    foreach ($items as $item) {
        $children = $item['children'] ?? null;
        if (is_array($children) && count($children) > 0) {
            $label = $item['label'] ?? 'Menu';
            $icon = resolve_icon($item, $iconMap);
            $childActive = false;
            $dropdownIndex += 1;

            foreach ($children as $child) {
                if (isset($child['view']) && is_active_view($child['view'], $currentView)) {
                    $childActive = true;
                    break;
                }
            }

            $open = $childActive ? ' open' : '';
            $active = $childActive ? ' active' : '';
            $ariaExpanded = $childActive ? 'true' : 'false';
            $submenuId = 'submenu-' . $dropdownIndex;

            echo '<details class="nav-item nav-dropdown"' . $open . '>';
            echo '<summary class="nav-link' . $active . '" aria-expanded="' . $ariaExpanded . '" aria-controls="' . $submenuId . '" aria-haspopup="true">';
            echo '<i class="bi ' . htmlspecialchars($icon) . ' nav-icon"></i>';
            echo '<span class="nav-text">' . htmlspecialchars($label) . '</span>';
            echo '<i class="bi bi-chevron-down nav-caret"></i>';
            echo '</summary>';
            echo '<div class="nav-submenu" id="' . $submenuId . '" aria-label="' . htmlspecialchars($label) . '">';

            foreach ($children as $child) {
                if (!isset($child['view'])) {
                    continue;
                }
                $childView = $child['view'];
                $childLabel = $child['label'] ?? $childView;
                $href = '?view=' . htmlspecialchars($childView);
                $childIcon = resolve_icon($child, $iconMap);
                $childActiveClass = is_active_view($childView, $currentView) ? ' active' : '';
                $aria = is_active_view($childView, $currentView) ? ' aria-current="page"' : '';

                echo '<a href="' . $href . '" class="nav-link nav-sublink' . $childActiveClass . '"' . $aria . '>';
                echo '<i class="bi ' . htmlspecialchars($childIcon) . ' nav-icon"></i>';
                echo '<span class="nav-text">' . htmlspecialchars($childLabel) . '</span>';
                echo '</a>';
            }

            echo '</div>';
            echo '</details>';
            continue;
        }

        $view = $item['view'];
        $label = $item['label'] ?? $view;
        $href = '?view=' . htmlspecialchars($view);
        $icon = resolve_icon($item, $iconMap);
        $active = is_active_view($view, $currentView) ? ' active' : '';
        $aria = is_active_view($view, $currentView) ? ' aria-current="page"' : '';

        echo '<div class="nav-item">';
        echo '<a href="' . $href . '" class="nav-link' . $active . '"' . $aria . '>';
        echo '<i class="bi ' . htmlspecialchars($icon) . ' nav-icon"></i>';
        echo '<span class="nav-text">' . htmlspecialchars($label) . '</span>';
        echo '</a>';
        echo '</div>';
    }
}
?>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-icon">N</div>
            <div class="logo-text">NexusFlow</div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-header"><?php echo htmlspecialchars($menu['header']); ?></div>
            <div class="px-3 pb-2" style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                <?php echo htmlspecialchars($menu['description']); ?>
            </div>
            <?php render_menu_items($menu['items'], $iconMap, $currentView); ?>
        </div>
    </div>
</nav>

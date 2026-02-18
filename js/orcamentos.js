// Variáveis globais para os modais
let modalOrcamento;
let modalVisualizar;

// Funções de inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modais Bootstrap
    modalOrcamento = new bootstrap.Modal(document.getElementById('modalOrcamento'));
    modalVisualizar = new bootstrap.Modal(document.getElementById('modalVisualizarOrcamento'));
    
    // Carregar dados iniciais
    carregarOrcamentos();
    
    // Configurar eventos
    document.getElementById('searchInputOrcamentos').addEventListener('input', filtrarOrcamentos);
    document.getElementById('filterStatusOrcamentos').addEventListener('change', filtrarOrcamentos);
    
    // Configurar evento de logout
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault();
        fazerLogoff();
    });
});

// Funções de carregamento e manipulação de dados
async function carregarOrcamentos() {
    mostrarLoading(true);
    
    try {
        const response = await fetch(API_CONFIG.ORCAMENTOS_BASE(), {
            method: 'GET',
            headers: API_CONFIG.getHeaders()
        });
        
        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        const dadosPaginados = data.data;
        orcamentos = Array.isArray(dadosPaginados.data) ? dadosPaginados.data : [];
        
        exibirOrcamentos(orcamentos);
        
    } catch (error) {
        console.error('Erro ao carregar orçamentos:', error);
        mostrarNotificacao('Erro ao carregar orçamentos: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Funções auxiliares
function mostrarLoading(show) {
    const loader = document.getElementById('loading');
    if (loader) {
        loader.style.display = show ? 'flex' : 'none';
    }
}

function mostrarNotificacao(mensagem, tipo) {
    // Verifica se já existe uma notificação
    let notificacao = document.querySelector('.notificacao');
    if (!notificacao) {
        notificacao = document.createElement('div');
        notificacao.className = 'notificacao';
        document.body.appendChild(notificacao);
    }

    // Define a classe baseada no tipo
    notificacao.className = `notificacao ${tipo}`;
    notificacao.textContent = mensagem;

    // Mostra a notificação
    notificacao.style.display = 'block';

    // Esconde após 3 segundos
    setTimeout(() => {
        notificacao.style.display = 'none';
    }, 3000);
}

// Funções de formatação
function getStatusBadgeClass(status) {
    const statusClasses = {
        'rascunho': 'bg-secondary',
        'aguardando_aprovacao': 'bg-warning text-dark',
        'aprovado': 'bg-success',
        'em_producao': 'bg-primary',
        'concluido': 'bg-info',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function formatarStatus(status) {
    const statusFormatado = {
        'rascunho': 'Rascunho',
        'aguardando_aprovacao': 'Aguardando Aprovação',
        'aprovado': 'Aprovado',
        'em_producao': 'Em Produção',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statusFormatado[status] || status;
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Funções de manipulação de orçamentos
async function novoOrcamento() {
    document.getElementById('formOrcamento').reset();
    document.getElementById('orcamentoId').value = '';
    document.getElementById('modalOrcamentoTitle').textContent = 'Novo Orçamento';
    modalOrcamento.show();
}

async function editarOrcamento(id) {
    if (!id) {
        console.error('ID do orçamento não fornecido para edição');
        mostrarNotificacao('Erro: ID do orçamento não fornecido', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_CONFIG.ORCAMENTOS_BASE()}/${id}`, {
            method: 'GET',
            headers: API_CONFIG.getHeaders()
        });

        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        const orcamento = data.data;

        // Preencher o formulário
        document.getElementById('orcamentoId').value = orcamento.id_orcamento;
        document.getElementById('id_filial').value = orcamento.id_filial;
        document.getElementById('id_cliente').value = orcamento.id_cliente;
        document.getElementById('tipo_movel').value = orcamento.tipo_movel;
        document.getElementById('categoria_movel').value = orcamento.categoria_movel;
        document.getElementById('descricao_detalhada').value = orcamento.descricao_detalhada;
        document.getElementById('margem_lucro').value = orcamento.margem_lucro;
        document.getElementById('prazo_entrega').value = orcamento.prazo_entrega;
        document.getElementById('observacoes_cliente').value = orcamento.observacoes_cliente || '';
        document.getElementById('observacoes_internas').value = orcamento.observacoes_internas || '';
        document.getElementById('valor_orcado').value = orcamento.valor_orcado;

        // Preencher medidas
        if (orcamento.medidas_gerais) {
            document.getElementById('largura').value = orcamento.medidas_gerais.largura;
            document.getElementById('altura').value = orcamento.medidas_gerais.altura;
            document.getElementById('profundidade').value = orcamento.medidas_gerais.profundidade;
        }

        document.getElementById('modalOrcamentoTitle').textContent = 'Editar Orçamento';
        modalOrcamento.show();

    } catch (error) {
        console.error('Erro ao carregar orçamento:', error);
        mostrarNotificacao('Erro ao carregar orçamento: ' + error.message, 'error');
    }
}

async function salvarOrcamento() {
    const formData = {
        id_filial: document.getElementById('id_filial').value,
        id_cliente: document.getElementById('id_cliente').value,
        tipo_movel: document.getElementById('tipo_movel').value,
        categoria_movel: document.getElementById('categoria_movel').value,
        medidas_gerais: {
            largura: parseFloat(document.getElementById('largura').value),
            altura: parseFloat(document.getElementById('altura').value),
            profundidade: parseFloat(document.getElementById('profundidade').value),
            unidades: "cm"
        },
        descricao_detalhada: document.getElementById('descricao_detalhada').value,
        margem_lucro: parseFloat(document.getElementById('margem_lucro').value),
        prazo_entrega: parseInt(document.getElementById('prazo_entrega').value),
        observacoes_cliente: document.getElementById('observacoes_cliente').value,
        observacoes_internas: document.getElementById('observacoes_internas').value
    };

    const orcamentoId = document.getElementById('orcamentoId').value;
    const isEdit = orcamentoId !== '';
    
    try {
        const response = await fetch(
            isEdit ? `${API_CONFIG.ORCAMENTOS_BASE()}/${orcamentoId}` : API_CONFIG.ORCAMENTOS_BASE(),
            {
                method: isEdit ? 'PUT' : 'POST',
                headers: API_CONFIG.getHeaders(),
                body: JSON.stringify(formData)
            }
        );

        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }

        modalOrcamento.hide();
        carregarOrcamentos();
        mostrarNotificacao(
            isEdit ? 'Orçamento atualizado com sucesso!' : 'Orçamento criado com sucesso!',
            'success'
        );
    } catch (error) {
        console.error('Erro ao salvar orçamento:', error);
        mostrarNotificacao('Erro ao salvar orçamento: ' + error.message, 'error');
    }
}

async function verDetalhesOrcamento(id) {
    if (!id) {
        console.error('ID do orçamento não fornecido');
        mostrarNotificacao('Erro: ID do orçamento não fornecido', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${API_CONFIG.ORCAMENTOS_BASE()}/${id}`, {
            method: 'GET',
            headers: API_CONFIG.getHeaders()
        });

        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        const orcamento = data.data;

        document.getElementById('currentOrcamentoId').value = id;
        
        document.getElementById('detalhesOrcamento').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informações Gerais</h6>
                    <p><strong>Código:</strong> ${orcamento.codigo_orcamento}</p>
                    <p><strong>Cliente:</strong> ${orcamento.cliente?.nome_cliente || 'N/A'}</p>
                    <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(orcamento.status)}">${formatarStatus(orcamento.status)}</span></p>
                    <p><strong>Tipo:</strong> ${orcamento.tipo_movel}</p>
                    <p><strong>Categoria:</strong> ${orcamento.categoria_movel}</p>
                </div>
                <div class="col-md-6">
                    <h6>Detalhes Financeiros</h6>
                    <p><strong>Valor Sugerido:</strong> ${formatarMoeda(orcamento.valor_sugerido)}</p>
                    <p><strong>Valor Orçado:</strong> ${formatarMoeda(orcamento.valor_orcado)}</p>
                    <p><strong>Margem Lucro:</strong> ${orcamento.margem_lucro}%</p>
                    <p><strong>Prazo Entrega:</strong> ${orcamento.prazo_entrega} dias</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6>Medidas</h6>
                    <p><strong>Largura:</strong> ${orcamento.medidas_gerais.largura}cm</p>
                    <p><strong>Altura:</strong> ${orcamento.medidas_gerais.altura}cm</p>
                    <p><strong>Profundidade:</strong> ${orcamento.medidas_gerais.profundidade}cm</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6>Descrição</h6>
                    <p>${orcamento.descricao_detalhada}</p>
                </div>
            </div>
        `;

        // Controla a visibilidade dos botões baseado no status
        const btnAprovar = document.getElementById('btnAprovarOrcamento');
        const btnOrdemProducao = document.getElementById('btnCriarOrdemProducao');
        
        btnAprovar.style.display = orcamento.status === 'rascunho' ? 'inline-block' : 'none';
        btnOrdemProducao.style.display = orcamento.status === 'aprovado' ? 'inline-block' : 'none';
        
        // Atualiza os eventos dos botões com o ID correto
        btnAprovar.onclick = () => aprovarOrcamento(id);
        btnOrdemProducao.onclick = () => criarOrdemProducao(id);

        modalVisualizar.show();
        
    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        mostrarNotificacao('Erro ao carregar detalhes do orçamento: ' + error.message, 'error');
    }
}

async function aprovarOrcamento(id) {
    if (!id) {
        console.error('ID do orçamento não fornecido para aprovação');
        mostrarNotificacao('Erro: ID do orçamento não fornecido', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_CONFIG.ORCAMENTOS_BASE()}/${id}/aprovar`, {
            method: 'POST',
            headers: API_CONFIG.getHeaders()
        });

        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }

        modalVisualizar.hide();
        carregarOrcamentos();
        mostrarNotificacao('Orçamento aprovado com sucesso!', 'success');
    } catch (error) {
        console.error('Erro ao aprovar orçamento:', error);
        mostrarNotificacao('Erro ao aprovar orçamento: ' + error.message, 'error');
    }
}

async function criarOrdemProducao(id) {
    if (!id) {
        console.error('ID do orçamento não fornecido para criar ordem de produção');
        mostrarNotificacao('Erro: ID do orçamento não fornecido', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_CONFIG.ORCAMENTOS_BASE()}/${id}/criar-ordem-producao`, {
            method: 'POST',
            headers: API_CONFIG.getHeaders()
        });

        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }

        modalVisualizar.hide();
        carregarOrcamentos();
        mostrarNotificacao('Ordem de produção criada com sucesso!', 'success');
    } catch (error) {
        console.error('Erro ao criar ordem de produção:', error);
        mostrarNotificacao('Erro ao criar ordem de produção: ' + error.message, 'error');
    }
}
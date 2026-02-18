<!-- Modal de Novo/Editar Orçamento -->
<div class="modal fade" id="modalOrcamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOrcamentoTitle">Novo Orçamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formOrcamento">
                    <input type="hidden" id="orcamentoId">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" id="id_cliente" required>
                                <option value="1">João Silva</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Filial</label>
                            <select class="form-select" id="id_filial" required>
                                <option value="12">MATRIZ - GRUPO VAREJO</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo do Móvel</label>
                            <input type="text" class="form-control" id="tipo_movel" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <input type="text" class="form-control" id="categoria_movel" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Largura (cm)</label>
                            <input type="number" class="form-control" id="largura" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Altura (cm)</label>
                            <input type="number" class="form-control" id="altura" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Profundidade (cm)</label>
                            <input type="number" class="form-control" id="profundidade" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Margem Lucro (%)</label>
                            <input type="number" class="form-control" id="margem_lucro" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição Detalhada</label>
                        <textarea class="form-control" id="descricao_detalhada" rows="3" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Prazo de Entrega (dias)</label>
                            <input type="number" class="form-control" id="prazo_entrega" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valor Orçado</label>
                            <input type="number" class="form-control" id="valor_orcado" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações do Cliente</label>
                        <textarea class="form-control" id="observacoes_cliente" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações Internas</label>
                        <textarea class="form-control" id="observacoes_internas" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarOrcamento()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização -->
<div class="modal fade" id="modalVisualizarOrcamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Orçamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <input type="hidden" id="currentOrcamentoId">
            <div class="modal-body" id="detalhesOrcamento">
                <!-- Conteúdo será preenchido dinamicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnAprovarOrcamento" onclick="aprovarOrcamento()">Aprovar</button>
                <button type="button" class="btn btn-primary" id="btnCriarOrdemProducao" onclick="criarOrdemProducao()">Criar Ordem de Produção</button>
            </div>
        </div>
    </div>
</div>
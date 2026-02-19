<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}
$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresa = $_SESSION['empresa'];
$id_empresa = $_SESSION['empresa_id'];
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';
$nomeUsuario = is_array($usuario) ? ($usuario['nome'] ?? $usuario['name'] ?? 'Usuário') : (string)$usuario;
$id_usuario = $_SESSION['user_id'] ?? ($usuario['id'] ?? 1);
$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Contas Receber - SaaS Multiempresas'; include __DIR__ . '/../components/app-head.php'; } ?>

<style>
:root{--primary-color:#3498DB;--secondary-color:#2C3E50;--success-color:#27AE60;--warning-color:#F39C12;--danger-color:#E74C3C;--info-color:#17A2B8;--light-color:#ECF0F1}
body{background-color:#f8f9fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
.main-content{margin-left:250px;padding:20px;transition:all .3s}
.main-header{background:#fff;padding:15px 30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.05);margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
.card-custom{background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
.card-header-custom{padding:15px 20px;border-bottom:1px solid #eee;background:rgba(248,249,250,.8)}
.user-avatar{width:40px;height:40px;border-radius:50%;background:var(--primary-color);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:bold;cursor:pointer}
.page-title{font-weight:700;color:var(--secondary-color);margin-bottom:5px}
.page-subtitle{color:#6c757d;margin-bottom:0}
.status-badge{padding:4px 8px;border-radius:20px;font-size:.75rem;font-weight:600}
.loading-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:9999}
.spinner-border{width:3rem;height:3rem}
.table thead th{font-size:.85rem;color:#6c757d;text-transform:uppercase}
@media (max-width:768px){.main-content{margin-left:0}}
</style>
</head>
<body>
<main class="main-content">
<header class="main-header">
  <div class="header-left">
    <button class="sidebar-toggle" type="button"><i class="bi bi-list"></i></button>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Contas Receber</li>
      </ol>
    </nav>
  </div>
  <div class="header-right">
    <div class="dropdown user-dropdown">
      <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false"><?php echo $inicialUsuario; ?></div>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><h6 class="dropdown-header user-name"><?php echo htmlspecialchars($nomeUsuario); ?></h6></li>
        <li><small class="dropdown-header text-muted user-email">
          <?php if (is_array($usuario)) { echo htmlspecialchars($usuario['email'] ?? $usuario['email_empresa'] ?? ''); } else { echo htmlspecialchars($usuario); } ?>
        </small></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="?view=perfil"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
      </ul>
    </div>
  </div>
</header>
<div class="content-area">
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="page-title">Contas a Receber</h1>
    <p class="page-subtitle">Gestão de recebimentos e inadimplência</p>
  </div>
</div>
<div class="card-custom">
  <div class="card-header-custom d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Títulos</h5>
    <button class="btn btn-outline-info btn-sm" onclick="carregarEstatisticas()">Estatísticas</button>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light"><tr>
          <th>#</th><th>Cliente</th><th>Descrição</th><th>Vencimento</th><th>Valor</th><th>Status</th><th>Ações</th>
        </tr></thead>
        <tbody id="tbodyCR"><tr><td colspan="7" class="text-center text-muted py-4">Carregando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal pagamento -->
<div class="modal fade" id="modalReceber" tabindex="-1">
 <div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Registrar Pagamento</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="row g-2">
      <div class="col-md-6"><label class="form-label">Valor</label><input type="number" step="0.01" id="rc_valor" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Data</label><input type="date" id="rc_data" class="form-control" required></div>
      <input type="hidden" id="rc_id">
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success" onclick="salvarRecebimento()">Confirmar</button></div>
 </div></div>
</div>

<script>
const ENDPOINTS_CR={
  LIST:`${BASE_URL}/api/v1/marcenaria/contas-receber`,
  SHOW:id=>`${BASE_URL}/api/v1/marcenaria/contas-receber/${id}`,
  RECEBER:id=>`${BASE_URL}/api/v1/marcenaria/contas-receber/${id}/registrar-pagamento`,
  STATS:`${BASE_URL}/api/v1/marcenaria/contas-receber/estatisticas`
};
async function carregarCR(){
  showLoading(true);
  try{
    const r=await fetch(ENDPOINTS_CR.LIST,{headers:API_HEADERS});
    const data=await r.json();
    document.getElementById('tbodyCR').innerHTML=(data||[]).map(t=>`
      <tr>
        <td>#${t.id??t.id_conta}</td>
        <td>${t.cliente?.nome??'-'}</td>
        <td>${t.descricao??'-'}</td>
        <td>${t.data_vencimento? new Date(t.data_vencimento).toLocaleDateString('pt-BR'):'-'}</td>
        <td>${(t.valor_total??t.valor??0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'})}</td>
        <td><span class="badge ${t.status==='pago'?'bg-success': 'bg-warning'}">${t.status??'pendente'}</span></td>
        <td><button class="btn btn-outline-success btn-sm" onclick="abrirReceber(${t.id??t.id_conta},${t.valor??t.valor_total??0})"><i class="bi bi-cash-coin"></i></button></td>
      </tr>
    `).join('');
  }catch(e){ notify('Erro ao listar CR: '+e.message,'error'); }
  finally{ showLoading(false); }
}
function abrirReceber(id,valor){
  document.getElementById('rc_id').value=id;
  document.getElementById('rc_valor').value=valor||0;
  document.getElementById('rc_data').value=new Date().toISOString().split('T')[0];
  new bootstrap.Modal(document.getElementById('modalReceber')).show();
}
async function salvarRecebimento(){
  const id=document.getElementById('rc_id').value;
  const payload={ valor: Number(document.getElementById('rc_valor').value||0), data_pagamento: document.getElementById('rc_data').value };
  showLoading(true);
  try{
    const r=await fetch(ENDPOINTS_CR.RECEBER(id),{method:'POST',headers:API_HEADERS,body:JSON.stringify(payload)});
    if(!r.ok){ const j=await r.json().catch(()=>({})); throw new Error('HTTP '+r.status+' '+JSON.stringify(j)); }
    notify('Pagamento registrado!','success'); bootstrap.Modal.getInstance(document.getElementById('modalReceber')).hide(); carregarCR();
  }catch(e){ notify('Erro ao registrar pagamento: '+e.message,'error'); }
  finally{ showLoading(false); }
}
async function carregarEstatisticas(){
  showLoading(true);
  try{ const r=await fetch(ENDPOINTS_CR.STATS,{headers:API_HEADERS}); const d=await r.json(); console.log('stats CR',d); notify('Estatísticas carregadas.','info'); } 
  catch(e){ notify('Erro ao carregar estatísticas: '+e.message,'error'); } finally{ showLoading(false); }
}
document.addEventListener('DOMContentLoaded',carregarCR);
</script>
</div>
</main>
<div class="loading-overlay d-none" id="loadingOverlay">
  <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>
</div>
<script>
const BASE_URL='<?= addslashes($config['api_base']) ?>';
const idEmpresa=<?php echo $id_empresa; ?>;
const idUsuario=<?php echo $id_usuario; ?>;
const token='<?php echo $token; ?>';
const API_HEADERS={
  'Authorization': `Bearer ${token}`,
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'X-ID-EMPRESA': String(idEmpresa)
};
function showLoading(v){document.getElementById('loadingOverlay').classList.toggle('d-none',!v)}
function notify(msg,type='info'){
  const n=document.createElement('div');
  n.className=`alert alert-${type==='error'?'danger':type} alert-dismissible fade show position-fixed`;
  n.style.cssText='top:20px;right:20px;z-index:9999;min-width:300px;';
  n.innerHTML=`${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
  document.body.appendChild(n); setTimeout(()=>{n.remove();},5000);
}
</script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body></html>












<?php
$config = $config ?? require __DIR__ . '/../funcoes/config.php';
$pageTitle = $pageTitle ?? 'NexusFlow';
$assetBase = defined('APP_ASSET_BASE')
    ? APP_ASSET_BASE
    : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/view/') !== false) ? '../assets' : 'assets');
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="<?php echo htmlspecialchars($assetBase); ?>/css/nexusflow.css" rel="stylesheet">

<script>
    window.NEXUSFLOW_API_BASE_URL = "<?php echo addslashes($config['api_base']); ?>";
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($assetBase); ?>/js/nexusflow.js"></script>

<?php
// log_viewer.php
$logFile = 'webhook_logs.txt';

// Função para formatar o log para HTML
function formatLogForHTML($logContent) {
    $lines = explode("\n", $logContent);
    $html = '';
    
    foreach ($lines as $line) {
        if (strpos($line, '=== NOVA REQUISIÇÃO WEBHOOK ===') !== false) {
            $html .= '<div class="webhook-entry"><h3 style="color: #2563eb; margin: 20px 0 10px 0;">' . htmlspecialchars($line) . '</h3>';
        } elseif (strpos($line, '=== FIM DA REQUISIÇÃO ===') !== false) {
            $html .= '<p style="color: #059669; font-weight: bold;">' . htmlspecialchars($line) . '</p></div><hr style="margin: 20px 0; border: 1px solid #e5e7eb;">';
        } elseif (strpos($line, '--- ') !== false) {
            $html .= '<h4 style="color: #7c3aed; margin: 15px 0 5px 0;">' . htmlspecialchars($line) . '</h4>';
        } elseif (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line)) {
            $html .= '<p style="margin: 5px 0; font-family: monospace; background: #f3f4f6; padding: 5px; border-radius: 3px;">' . htmlspecialchars($line) . '</p>';
        } elseif (!empty(trim($line))) {
            $html .= '<p style="margin: 3px 0; font-family: monospace; padding-left: 10px;">' . htmlspecialchars($line) . '</p>';
        }
    }
    
    return $html;
}

// Limpar logs se solicitado
if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
    file_put_contents($logFile, '');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Verificar se o arquivo de log existe
if (!file_exists($logFile)) {
    $logContent = 'Nenhum webhook recebido ainda.';
} else {
    $logContent = file_get_contents($logFile);
    if (empty($logContent)) {
        $logContent = 'Log vazio.';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhook Logs</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            color: #1f2937;
        }
        .controls {
            margin: 15px 0;
        }
        .btn {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 10px;
            cursor: pointer;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .logs-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .webhook-entry {
            margin-bottom: 20px;
        }
        .stats {
            background: #eff6ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔗 Webhook Logs</h1>
        <p>Monitoramento de webhooks recebidos</p>
        
        <div class="stats">
            <?php
            $fileSize = file_exists($logFile) ? filesize($logFile) : 0;
            $lastModified = file_exists($logFile) ? date('d/m/Y H:i:s', filemtime($logFile)) : 'N/A';
            $webhookCount = substr_count($logContent, '=== NOVA REQUISIÇÃO WEBHOOK ===');
            ?>
            <strong>Estatísticas:</strong><br>
            📊 Total de webhooks: <?php echo $webhookCount; ?><br>
            📁 Tamanho do arquivo: <?php echo number_format($fileSize / 1024, 2); ?> KB<br>
            🕒 Última modificação: <?php echo $lastModified; ?>
        </div>
        
        <div class="controls">
            <button class="btn" onclick="location.reload()">🔄 Atualizar</button>
            <a href="?clear=true" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja limpar todos os logs?')">🗑️ Limpar Logs</a>
            <a href="<?php echo $logFile; ?>" class="btn" download>📥 Download</a>
        </div>
    </div>

    <div class="logs-container">
        <?php if ($webhookCount > 0): ?>
            <?php echo formatLogForHTML($logContent); ?>
        <?php else: ?>
            <p style="text-align: center; color: #6b7280; padding: 40px;">
                📭 Nenhum webhook recebido ainda.<br>
                <small>Os logs aparecerão aqui quando webhooks forem enviados para webhook_receiver.php</small>
            </p>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh a cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
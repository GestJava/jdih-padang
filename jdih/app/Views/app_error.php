<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - JDIH Kota Padang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }

        .error-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .error-title {
            color: #dc3545;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .error-content {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Terjadi Kesalahan</h1>

        <div class="error-content">
            <?php if (isset($content)): ?>
                <p><?= esc($content) ?></p>
            <?php else: ?>
                <p>Maaf, terjadi kesalahan tidak terduga pada sistem.</p>
            <?php endif; ?>
        </div>

        <div style="margin: 20px 0;">
            <a href="<?= base_url() ?>" class="btn">Kembali ke Beranda</a>
            <a href="<?= base_url('login') ?>" class="btn" style="background-color: #28a745;">Login</a>
        </div>

        <?php if (ENVIRONMENT === 'development' && isset($debug_info)): ?>
            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                <?php if (is_array($debug_info)): ?>
                    <?php foreach ($debug_info as $key => $value): ?>
                        <strong><?= esc($key) ?>:</strong> <?= esc(is_array($value) ? json_encode($value) : $value) ?><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= esc($debug_info) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
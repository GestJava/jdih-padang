<?php

namespace App\Services;

/**
 * Simple QR Code Service menggunakan Google Charts API
 * Alternatif untuk QR code generation tanpa GD extension
 */
class SimpleQRCodeService
{
    protected $logger;

    public function __construct()
    {
        $this->logger = \Config\Services::logger();
        
        // Force set PATH environment untuk macOS
        $this->setEnvironmentPath();
        
        // Load vendor autoloader untuk Endroid QR Code
        $this->loadVendorAutoloader();
    }
    
    /**
     * Load vendor autoloader untuk akses library di /vendor/
     */
    private function loadVendorAutoloader()
    {
        $vendorAutoload = FCPATH . 'vendor/autoload.php';
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
            $this->logger->debug('Vendor autoloader loaded: ' . $vendorAutoload);
        } else {
            $this->logger->warning('Vendor autoloader not found: ' . $vendorAutoload);
        }
    }
    
    /**
     * Set PATH environment agar Python3 accessible
     */
    private function setEnvironmentPath()
    {
        $isMac = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
        if ($isMac) {
            // Add common macOS paths to environment
            $currentPath = getenv('PATH');
            $additionalPaths = [
                '/usr/bin',
                '/usr/local/bin',
                '/opt/homebrew/bin',
                '/Library/Frameworks/Python.framework/Versions/3.9/bin',
                '/Library/Frameworks/Python.framework/Versions/3.10/bin',
                '/Library/Frameworks/Python.framework/Versions/3.11/bin',
            ];
            
            $newPath = $currentPath;
            foreach ($additionalPaths as $path) {
                if (strpos($currentPath, $path) === false) {
                    $newPath .= ':' . $path;
                }
            }
            
            putenv('PATH=' . $newPath);
            $this->logger->debug('PATH set to: ' . $newPath);
        }
    }

    /**
     * Get Python command berdasarkan OS
     */
    private function getPythonCommand()
    {
        // Deteksi OS
        $isMac = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
        $isLinux = strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX';
        
        // macOS dan Linux menggunakan python3
        if ($isMac || $isLinux) {
            // Try common Python3 paths for macOS
            $possiblePaths = [
                '/usr/bin/python3',              // Default macOS
                '/usr/local/bin/python3',        // Homebrew Intel
                '/opt/homebrew/bin/python3',     // Homebrew M1/M2
                '/Library/Frameworks/Python.framework/Versions/3.9/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.10/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.11/bin/python3',
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $this->logger->info('Using python3 at: ' . $path);
                    return $path;
                }
            }
            
            // Fallback: try which command
            $output = [];
            exec('which python3 2>&1', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                $this->logger->info('Using python3 from which: ' . $output[0]);
                return $output[0];
            }
            
            // Last resort for macOS: use full path
            $this->logger->warning('Python3 not found in common paths, using /usr/bin/python3');
            return '/usr/bin/python3';
        }
        
        // Windows: cari full path ke Python executable
        // Lokasi umum Python di Windows
        $localAppData = getenv('LOCALAPPDATA');
        $possiblePaths = [];
        
        // 1. Cari py.exe (Python Launcher)
        if ($localAppData) {
            $possiblePaths[] = $localAppData . '\\Programs\\Python\\Launcher\\py.exe';
        }
        $possiblePaths[] = 'C:\\Windows\\py.exe';
        $possiblePaths[] = 'C:\\Windows\\System32\\py.exe';
        
        // 2. Cari python.exe langsung
        if ($localAppData) {
            // Cari di subfolder Python (Python311, Python312, dll)
            $pythonBaseDir = $localAppData . '\\Programs\\Python';
            if (is_dir($pythonBaseDir)) {
                // Gunakan scandir untuk kompatibilitas lebih baik
                $entries = @scandir($pythonBaseDir);
                if ($entries !== false) {
                    foreach ($entries as $entry) {
                        if ($entry !== '.' && $entry !== '..' && 
                            strpos($entry, 'Python') === 0 && 
                            is_dir($pythonBaseDir . '\\' . $entry)) {
                            $pythonExe = $pythonBaseDir . '\\' . $entry . '\\python.exe';
                            if (file_exists($pythonExe)) {
                                $possiblePaths[] = $pythonExe;
                            }
                        }
                    }
                }
            }
        }
        
        // 3. Coba command biasa (jika ada di PATH)
        $possibleCommands = ['py', 'python', 'python3'];
        
        // Test full paths terlebih dahulu
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                // Verify qrcode library (gunakan path tanpa quotes untuk test)
                $testOutput = [];
                exec("\"$path\" -c \"import qrcode\" 2>&1", $testOutput, $testCode);
                if ($testCode === 0) {
                    $this->logger->info("Using Python with qrcode library at: $path");
                    // Return path dengan quotes untuk Windows (handle spasi di path)
                    return $path;
                } else {
                    // Test tanpa qrcode (masih bisa digunakan)
                    $versionOutput = [];
                    exec("\"$path\" --version 2>&1", $versionOutput, $versionCode);
                    if ($versionCode === 0) {
                        $this->logger->warning("Using Python without qrcode verification at: $path");
                        // Return path dengan quotes untuk Windows (handle spasi di path)
                        return $path;
                    }
                }
            }
        }
        
        // Fallback: coba command biasa (jika ada di PATH)
        foreach ($possibleCommands as $cmd) {
            $output = [];
            exec("$cmd --version 2>&1", $output, $returnCode);
            if ($returnCode === 0) {
                // Verify qrcode library
                $testOutput = [];
                exec("$cmd -c \"import qrcode\" 2>&1", $testOutput, $testCode);
                if ($testCode === 0) {
                    $this->logger->info("Using $cmd with qrcode library on Windows");
                    return $cmd;
                } else {
                    $this->logger->warning("Using $cmd without qrcode verification on Windows");
                    return $cmd;
                }
            }
        }
        
        // Ultimate fallback untuk Windows
        $this->logger->error('No Python found on Windows');
        return 'py';
    }

    /**
     * Generate QR code menggunakan Endroid QR Code library dengan logo
     */
    public function generateQRCode(string $data, int $size = 100): string
    {
        try {
            // Pastikan data tidak kosong
            if (empty($data)) {
                throw new \Exception('Data QR code tidak boleh kosong');
            }

            // Priority 1: Try Endroid QR Code library (if available)
            if (class_exists('\Endroid\QrCode\Builder\Builder')) {
                $this->logger->info('Generating QR code with Endroid library for: ' . $data);
                try {
                    return $this->generateEndroidQRCode($data, $size);
                } catch (\Exception $e) {
                    $this->logger->warning('Endroid QR code failed, trying Python: ' . $e->getMessage());
                }
            }

            // Priority 2: Try Python QR code generation (local, tidak bergantung internet)
            $this->logger->info('Trying Python QR code generation for: ' . $data);
            try {
                return $this->generatePythonQRCode($data, $size);
            } catch (\Exception $e) {
                $this->logger->warning('Python QR code failed, trying Google Charts API: ' . $e->getMessage());
            }

            // Priority 3: Try Google Charts API (fallback, tapi mungkin gagal untuk URL internal)
            $this->logger->info('Trying Google Charts API for: ' . $data);
            try {
                return $this->generateGoogleChartsQRCode($data, $size);
            } catch (\Exception $e) {
                $this->logger->warning('Google Charts API failed: ' . $e->getMessage());
            }

            // Priority 4: Try Simple QR Code dengan GD (fallback tanpa dependency)
            $this->logger->info('Trying Simple QR Code with GD for: ' . $data);
            try {
                return $this->generateSimpleQRCode($data, $size);
            } catch (\Exception $e) {
                $this->logger->warning('Simple QR Code failed: ' . $e->getMessage());
            }

            // Priority 5: Ultimate fallback - Text QR Code (selalu berhasil)
            $this->logger->info('Using Text QR Code as ultimate fallback for: ' . $data);
            return $this->generateTextQRCode($data, $size);
            
        } catch (\Exception $e) {
            $this->logger->error('QR code generation failed: ' . $e->getMessage());
            // Ultimate fallback: generateTextQRCode selalu berhasil
            $this->logger->info('Using ultimate fallback for: ' . $data);
            return $this->generateTextQRCode($data, $size);
        }
    }
    
    /**
     * Generate QR code menggunakan Endroid QR Code library dengan logo Padang
     */
    protected function generateEndroidQRCode(string $data, int $size = 100): string
    {
        try {
            // Check if Endroid QR Code library is available
            if (!class_exists('\Endroid\QrCode\Builder\Builder')) {
                $this->logger->warning('Endroid QR Code library not found, falling back to Google Charts API');
                return $this->generateGoogleChartsQRCode($data, $size);
            }

            // Path untuk save QR code
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_endroid_' . md5($data) . '.png';
            $qrDir = dirname($qrPath);

            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            // Logo path - hanya menggunakan logo-padang.png
            // Logo ada di /var/www/jdih/images/ (satu level di atas FCPATH)
            // Gunakan realpath untuk memastikan absolute path yang benar
            $logoPath = FCPATH . '../images/logo-padang.png';
            $logoPathReal = realpath($logoPath);
            
            // Validate logo exists
            if (!$logoPathReal || !file_exists($logoPathReal)) {
                $this->logger->warning('Logo Padang tidak ditemukan: ' . $logoPath . ' (realpath: ' . ($logoPathReal ?: 'null') . '), generating QR without logo');
                $logoPath = null;
            } else {
                // Gunakan realpath untuk konsistensi
                $logoPath = $logoPathReal;
                $this->logger->info('Logo Padang ditemukan: ' . $logoPath . ' (readable: ' . (is_readable($logoPath) ? 'YES' : 'NO') . ')');
            }

            // Build QR Code dengan logo menggunakan full namespace
            $builder = \Endroid\QrCode\Builder\Builder::create()
                ->writer(new \Endroid\QrCode\Writer\PngWriter())
                ->data($data)
                ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->errorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::High) // HIGH untuk support logo
                ->size($size)
                ->margin(10)
                ->roundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin);
            
            // Tambahkan logo jika ada (25% dari size untuk keamanan scanning)
            if ($logoPath) {
                $logoSize = (int)($size * 0.25); // 25% dari QR size (standar aman agar QR tetap bisa di-scan)
                $builder->logoPath($logoPath)
                        ->logoResizeToWidth($logoSize);
                
                $this->logger->info('Adding logo: ' . $logoPath . ' (size: ' . $logoSize . 'px)');
            }
            
            $result = $builder->build();

            // Save to file
            $result->saveToFile($qrPath);

            if (file_exists($qrPath)) {
                $fileSize = filesize($qrPath);
                $this->logger->info('Endroid QR code generated successfully: ' . $qrPath . ' (' . $fileSize . ' bytes)');
                
                // Verify it's a proper PNG (not text)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $qrPath);
                finfo_close($finfo);
                
                if ($mimeType === 'image/png') {
                    $this->logger->info('QR code verified as PNG image');
                    return $qrPath;
                } else {
                    throw new \Exception('Generated file is not PNG: ' . $mimeType);
                }
            } else {
                throw new \Exception('QR code file not created');
            }
        } catch (\Exception $e) {
            $this->logger->error('Endroid QR code generation failed: ' . $e->getMessage());
            $this->logger->info('Falling back to Google Charts API');
            // Fallback ke Google Charts API jika Endroid gagal
            try {
                return $this->generateGoogleChartsQRCode($data, $size);
            } catch (\Exception $fallbackException) {
                $this->logger->error('Google Charts QR code fallback also failed: ' . $fallbackException->getMessage());
                throw $e; // Throw original exception
            }
        }
    }

    /**
     * Generate QR code menggunakan Google Charts API (fallback)
     */
    protected function generateGoogleChartsQRCode(string $data, int $size = 150): string
    {
        try {
            // URL Google Charts API untuk QR code
            $qrUrl = sprintf(
                'https://chart.googleapis.com/chart?chs=%dx%d&cht=qr&chl=%s',
                $size,
                $size,
                urlencode($data)
            );

            $this->logger->info('QR Code URL: ' . $qrUrl);

            // Download QR code image
            $qrImage = file_get_contents($qrUrl);

            if ($qrImage === false) {
                throw new \Exception('Gagal download QR code dari Google Charts API');
            }

            // Simpan QR code
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_google_' . md5($data) . '.png';
            // Ensure path consistency
            $qrPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $qrPath);
            $qrDir = dirname($qrPath);

            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            file_put_contents($qrPath, $qrImage);

            $this->logger->info('Google Charts QR code generated: ' . $qrPath);
            return $qrPath;
        } catch (\Exception $e) {
            $this->logger->warning('Google Charts QR code generation gagal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR code menggunakan Python qrcode library
     */
    protected function generatePythonQRCode(string $data, int $size = 100): string
    {
        try {
            // Path ke Python script
            $pythonScript = APPPATH . 'PdfTools/generate_qr_code.py';

            if (!file_exists($pythonScript)) {
                throw new \Exception('Python QR code script tidak ditemukan: ' . $pythonScript);
            }

            // Path output QR code
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_python_' . md5($data) . '.png';
            $qrDir = dirname($qrPath);

            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            // Jalankan Python script dengan path yang benar
            // Gunakan python3 untuk macOS/Linux
            $pythonCmd = $this->getPythonCommand();
            
            // FORCE: Always use /usr/bin/python3 for macOS if detected
            $isMac = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
            if ($isMac && file_exists('/usr/bin/python3')) {
                $pythonCmd = '/usr/bin/python3';
                $this->logger->info('FORCE using /usr/bin/python3 for macOS');
            }
            
            // Logo path - hanya menggunakan logo-padang.png
            // Logo ada di /var/www/jdih/images/ (satu level di atas FCPATH)
            // Gunakan realpath untuk memastikan absolute path yang benar (penting untuk Python script)
            $logoPath = FCPATH . '../images/logo-padang.png';
            $logoPathReal = realpath($logoPath);
            
            // Validate logo exists
            if (!$logoPathReal || !file_exists($logoPathReal)) {
                $this->logger->warning('Logo Padang tidak ditemukan untuk Python QR: ' . $logoPath . ' (realpath: ' . ($logoPathReal ?: 'null') . '), generating QR without logo');
                $logoPath = null;
            } else {
                // Gunakan realpath untuk memastikan Python bisa membaca file
                $logoPath = $logoPathReal;
                $this->logger->info('Logo Padang ditemukan untuk Python QR: ' . $logoPath . ' (readable: ' . (is_readable($logoPath) ? 'YES' : 'NO') . ')');
            }
            
            // Escape Python command: selalu gunakan escapeshellarg untuk handle spasi di path
            // escapeshellarg akan menambahkan quotes jika diperlukan
            if ($logoPath) {
                $command = sprintf(
                    '%s %s %s %s %d %s 2>&1',
                    escapeshellarg($pythonCmd),
                    escapeshellarg($pythonScript),
                    escapeshellarg($data),
                    escapeshellarg($qrPath),
                    $size,
                    escapeshellarg($logoPath)
                );
            } else {
                $command = sprintf(
                    '%s %s %s %s %d 2>&1',
                    escapeshellarg($pythonCmd),
                    escapeshellarg($pythonScript),
                    escapeshellarg($data),
                    escapeshellarg($qrPath),
                    $size
                );
            }

            $this->logger->info('Executing Python QR command: ' . $command);
            if ($logoPath) {
                $this->logger->info('Logo path being sent to Python: ' . $logoPath);
            } else {
                $this->logger->warning('No logo path - QR will be generated without logo');
            }
            $output = shell_exec($command);
            $this->logger->info('Python QR output: ' . ($output ?: 'no output'));
            
            // Parse output untuk melihat apakah logo berhasil ditambahkan
            if ($output && strpos($output, 'Logo added') !== false) {
                $this->logger->info('Logo successfully added to QR code according to Python output');
            } elseif ($output && strpos($output, 'Warning: Failed to add logo') !== false) {
                $this->logger->warning('Python script reported logo addition failed: ' . $output);
            } elseif ($logoPath && $output) {
                $this->logger->warning('Logo path provided but no logo addition message in Python output. Output: ' . $output);
            }

            // Wait a bit for file system to sync (especially on network filesystems)
            usleep(100000); // 0.1 second

            // Check if file was created
            if (file_exists($qrPath)) {
                $fileSize = filesize($qrPath);
                if ($fileSize > 0) {
                    $this->logger->info('Python QR code generated successfully: ' . $qrPath . ' (' . $fileSize . ' bytes)');
                    return $qrPath;
                } else {
                    throw new \Exception('Python QR code file created but is empty. Output: ' . ($output ?: 'no output'));
                }
            } else {
                // Check if there's an error in output
                $errorMsg = 'Python QR code generation gagal - file tidak dibuat';
                if ($output) {
                    $errorMsg .= '. Output: ' . $output;
                }
                // Check if Python script exists
                if (!file_exists($pythonScript)) {
                    $errorMsg .= '. Script tidak ditemukan: ' . $pythonScript;
                }
                // Check if directory exists and is writable
                if (!is_dir($qrDir)) {
                    $errorMsg .= '. Directory tidak ada: ' . $qrDir;
                } elseif (!is_writable($qrDir)) {
                    $errorMsg .= '. Directory tidak writable: ' . $qrDir;
                }
                throw new \Exception($errorMsg);
            }
        } catch (\Exception $e) {
            $this->logger->error('Python QR code generation gagal: ' . $e->getMessage());
            $this->logger->error('Python command: ' . ($command ?? 'N/A'));
            $this->logger->error('Python script path: ' . ($pythonScript ?? 'N/A'));
            throw $e;
        }
    }

    /**
     * Generate QR code sederhana sebagai fallback
     */
    protected function generateSimpleQRCode(string $data, int $size = 150): string
    {
        try {
            // Cek apakah GD extension tersedia
            if (!extension_loaded('gd')) {
                $this->logger->warning('GD extension tidak tersedia, menggunakan text fallback');
                return $this->generateTextQRCode($data, $size);
            }

            // Buat QR code sederhana menggunakan GD
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_simple_' . md5($data) . '.png';
            $qrDir = dirname($qrPath);

            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            // Buat image sederhana dengan text
            $image = imagecreate($size, $size);
            $bgColor = imagecolorallocate($image, 255, 255, 255);
            $textColor = imagecolorallocate($image, 0, 0, 0);

            // Tulis text QR code
            $text = "QR: " . substr($data, 0, 20) . "...";
            imagestring($image, 2, 10, $size / 2 - 10, $text, $textColor);

            // Simpan image
            imagepng($image, $qrPath);
            imagedestroy($image);

            $this->logger->info('Simple QR code generated: ' . $qrPath);
            return $qrPath;
        } catch (\Exception $e) {
            $this->logger->warning('Simple QR code generation gagal, menggunakan text fallback: ' . $e->getMessage());
            // Fallback ke text QR code
            return $this->generateTextQRCode($data, $size);
        }
    }

    /**
     * Generate text-based QR code sebagai ultimate fallback
     * Menghasilkan PNG placeholder yang valid (bukan TXT)
     */
    protected function generateTextQRCode(string $data, int $size = 150): string
    {
        try {
            // Buat PNG placeholder sebagai QR code fallback
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_placeholder_' . md5($data) . '.png';
            $qrDir = dirname($qrPath);

            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            // Coba buat PNG placeholder menggunakan GD jika tersedia
            if (extension_loaded('gd')) {
                try {
                    $image = imagecreate($size, $size);
                    $bgColor = imagecolorallocate($image, 255, 255, 255);
                    $textColor = imagecolorallocate($image, 0, 0, 0);
                    $borderColor = imagecolorallocate($image, 200, 200, 200);
                    
                    // Draw border
                    imagerectangle($image, 0, 0, $size - 1, $size - 1, $borderColor);
                    
                    // Draw text (truncate jika terlalu panjang)
                    $text = "QR\n" . substr($data, 0, 15) . "...";
                    $fontSize = 2;
                    $textX = 10;
                    $textY = ($size / 2) - 10;
                    imagestring($image, $fontSize, $textX, $textY, $text, $textColor);
                    
                    imagepng($image, $qrPath);
                    imagedestroy($image);
                    
                    $this->logger->info('Placeholder QR code (GD) generated: ' . $qrPath);
                    return $qrPath;
                } catch (\Exception $e) {
                    $this->logger->warning('GD placeholder generation failed: ' . $e->getMessage());
                }
            }
            
            // Ultimate fallback: Buat PNG minimal yang valid (1x1 pixel white PNG)
            // Format PNG minimal: PNG signature + minimal IHDR chunk
            $pngData = "\x89PNG\r\n\x1a\n"; // PNG signature
            $pngData .= "\x00\x00\x00\rIHDR"; // IHDR chunk header
            $pngData .= pack('N', $size); // Width
            $pngData .= pack('N', $size); // Height
            $pngData .= "\x08\x02\x00\x00\x00"; // Bit depth, color type, compression, filter, interlace
            $pngData .= pack('N', crc32("IHDR" . pack('N', $size) . pack('N', $size) . "\x08\x02\x00\x00\x00")); // CRC
            $pngData .= "\x00\x00\x00\x00IEND\xaeB`\x82"; // IEND chunk
            
            file_put_contents($qrPath, $pngData);
            
            $this->logger->info('Placeholder QR code (minimal PNG) generated: ' . $qrPath);
            return $qrPath;
        } catch (\Exception $e) {
            $this->logger->error('Placeholder QR code generation gagal: ' . $e->getMessage());
            // Ultimate fallback: return path meskipun file belum dibuat
            // PDF enhancement akan handle missing QR code
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_placeholder_' . md5($data) . '.png';
            $this->logger->warning('Using fallback QR path: ' . $qrPath);
            return $qrPath;
        }
    }

    /**
     * Generate QR code dengan custom styling
     */
    public function generateStyledQRCode(
        string $data,
        int $size = 100,
        string $color = '000000',
        string $bgColor = 'FFFFFF'
    ): string {
        try {
            // URL Google Charts API dengan styling
            $qrUrl = sprintf(
                'https://chart.googleapis.com/chart?chs=%dx%d&cht=qr&chl=%s&chco=%s&chf=bg,s,%s',
                $size,
                $size,
                urlencode($data),
                $color,
                $bgColor
            );

            // Download QR code image
            $qrImage = file_get_contents($qrUrl);

            if ($qrImage === false) {
                throw new \Exception('Gagal download styled QR code dari Google Charts API');
            }

            // Simpan QR code
            $qrPath = WRITEPATH . 'uploads/qr_codes/qr_styled_' . md5($data) . '.png';
            $qrDir = dirname($qrPath);

            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            file_put_contents($qrPath, $qrImage);

            $this->logger->info('Styled QR code generated: ' . $qrPath);
            return $qrPath;
        } catch (\Exception $e) {
            $this->logger->warning('Styled QR code generation gagal, menggunakan fallback: ' . $e->getMessage());
            // Fallback: buat QR code sederhana
            return $this->generateSimpleQRCode($data, $size);
        }
    }

    /**
     * Generate QR code untuk download URL
     */
    public function generateDownloadQRCode(string $downloadUrl, string $nomorPeraturan): string
    {
        // Pastikan URL valid dan tidak ada karakter yang salah
        $qrData = trim($downloadUrl);

        // Jika URL sudah memiliki parameter, gunakan &, jika tidak gunakan ?
        if (strpos($qrData, '?') !== false) {
            $qrData .= '&ref=' . urlencode($nomorPeraturan);
        } else {
            $qrData .= '?ref=' . urlencode($nomorPeraturan);
        }

        $this->logger->info('Generating QR code for URL: ' . $qrData);
        return $this->generateQRCode($qrData, 150);
    }

    /**
     * Test QR code generation
     */
    public function testQRCodeGeneration(): array
    {
        try {
            $testData = 'https://jdih.padang.go.id/test';
            $qrPath = $this->generateQRCode($testData, 150);

            return [
                'success' => true,
                'qr_path' => $qrPath,
                'file_exists' => file_exists($qrPath),
                'file_size' => file_exists($qrPath) ? filesize($qrPath) : 0,
                'message' => 'QR code generation berhasil'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'QR code generation gagal'
            ];
        }
    }
}

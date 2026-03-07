<?php

namespace App\Services;

use Config\TTEPositionConfig;
use Exception;

class PdfEnhancementService
{
    private $positionConfig;

    public function __construct()
    {
        $this->positionConfig = new TTEPositionConfig();
        
        // Force set PATH environment untuk macOS
        $this->setEnvironmentPath();
        
        // Load vendor autoloader untuk dependencies
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
            log_message('debug', 'Vendor autoloader loaded: ' . $vendorAutoload);
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
            log_message('debug', 'PATH set to: ' . $newPath);
        }
    }

    /**
     * Get Python command berdasarkan OS
     * Prioritize Python installations that have PyMuPDF installed
     */
    private function getPythonCommand()
    {
        // Deteksi OS
        $isMac = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
        $isLinux = strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX';
        
        // macOS dan Linux menggunakan python3
        if ($isMac || $isLinux) {
            // Prioritize Homebrew Python on macOS (usually has PyMuPDF installed)
            $possiblePaths = [
                '/opt/homebrew/bin/python3',     // Homebrew M1/M2 (highest priority)
                '/usr/local/bin/python3',        // Homebrew Intel
                '/usr/bin/python3',              // System Python
                '/Library/Frameworks/Python.framework/Versions/3.13/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.12/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.11/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.10/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.9/bin/python3',
            ];
            
            // First pass: Find Python with PyMuPDF installed
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    // Verify this Python can import fitz (PyMuPDF)
                    $testOutput = [];
                    exec("$path -c 'import fitz' 2>&1", $testOutput, $testCode);
                    if ($testCode === 0) {
                        log_message('info', 'Using python3 with PyMuPDF at: ' . $path);
                        return $path;
                    }
                }
            }
            
            // Second pass: Find any available Python (fallback)
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    log_message('warning', 'Using python3 without PyMuPDF check at: ' . $path);
                    return $path;
                }
            }
            
            // Fallback: try which command
            $output = [];
            exec('which python3 2>&1', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                $pythonPath = trim($output[0]);
                // Verify PyMuPDF if possible
                $testOutput = [];
                exec("$pythonPath -c 'import fitz' 2>&1", $testOutput, $testCode);
                if ($testCode === 0) {
                    log_message('info', 'Using python3 with PyMuPDF from which: ' . $pythonPath);
                } else {
                    log_message('warning', 'Using python3 from which (PyMuPDF not verified): ' . $pythonPath);
                }
                return $pythonPath;
            }
            
            // Last resort for macOS: use full path
            log_message('warning', 'Python3 not found in common paths, using /usr/bin/python3');
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
                // Verify PyMuPDF
                $testOutput = [];
                exec("\"$path\" -c \"import fitz\" 2>&1", $testOutput, $testCode);
                if ($testCode === 0) {
                    log_message('info', "Using Python with PyMuPDF at: $path");
                    // Return path tanpa quotes, escapeshellarg() akan handle quotes
                    return $path;
                } else {
                    // Test tanpa PyMuPDF (masih bisa digunakan)
                    $versionOutput = [];
                    exec("\"$path\" --version 2>&1", $versionOutput, $versionCode);
                    if ($versionCode === 0) {
                        log_message('warning', "Using Python without PyMuPDF at: $path");
                        // Return path tanpa quotes, escapeshellarg() akan handle quotes
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
                // Verify PyMuPDF jika memungkinkan
                $testOutput = [];
                exec("$cmd -c \"import fitz\" 2>&1", $testOutput, $testCode);
                if ($testCode === 0) {
                    log_message('info', "Using $cmd with PyMuPDF on Windows");
                    return $cmd;
                } else {
                    log_message('warning', "Using $cmd without PyMuPDF on Windows");
                    return $cmd;
                }
            }
        }
        
        // Ultimate fallback untuk Windows
        log_message('error', 'No Python found on Windows');
        return 'py';
    }

    public function enhancePdf($sourcePath, $nomorPeraturan, $tanggalPenetapan, $downloadUrl, $quality = 80)
    {
        try {
            log_message('info', 'Starting PDF enhancement process');
            log_message('info', 'Source path: ' . $sourcePath);
            log_message('info', 'Nomor peraturan: ' . $nomorPeraturan);
            log_message('info', 'Tanggal penetapan: ' . $tanggalPenetapan);
            log_message('info', 'Download URL: ' . $downloadUrl);
            log_message('info', 'Quality: ' . $quality);

            // Check if source file exists
            if (!file_exists($sourcePath)) {
                log_message('error', 'Source PDF file not found: ' . $sourcePath);
                return false;
            }

            // Generate QR code
            log_message('info', 'Generating QR code...');
            $qrCodePath = $this->generateQRCode($downloadUrl, $quality);

            if (!$qrCodePath) {
                log_message('error', 'QR code generation failed');
                return false;
            }

            log_message('info', 'QR code generated: ' . $qrCodePath);

            // Enhance PDF dengan Python script
            log_message('info', 'Enhancing PDF with Python...');
            $enhancedPath = $this->enhanceWithPython($sourcePath, $nomorPeraturan, $tanggalPenetapan, $qrCodePath, $quality);

            if ($enhancedPath && file_exists($enhancedPath)) {
                log_message('info', 'PDF enhancement successful: ' . $enhancedPath);
                return $enhancedPath;
            }

            log_message('warning', 'Python enhancement failed, trying TCPDF fallback...');
            // Fallback ke TCPDF jika Python gagal
            $fallbackPath = $this->enhanceWithTCPDF($sourcePath, $nomorPeraturan, $tanggalPenetapan, $qrCodePath);

            if ($fallbackPath && file_exists($fallbackPath)) {
                log_message('info', 'TCPDF fallback successful: ' . $fallbackPath);
                return $fallbackPath;
            }

            log_message('error', 'Both Python and TCPDF enhancement failed');
            return false;
        } catch (Exception $e) {
            log_message('error', 'PDF Enhancement Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    private function generateQRCode($downloadUrl, $quality = 80)
    {
        try {
            // Gunakan ukuran QR code dari config (dalam points)
            $qrSizeConfig = $this->positionConfig->qrCodeSize;
            $qrSizePoints = max($qrSizeConfig[0], $qrSizeConfig[1]); // Ambil ukuran terbesar untuk square QR code
            
            // Generate QR code dengan resolusi tinggi untuk menghindari blur
            // PDF biasanya menggunakan 72 DPI, jadi 1 point = 1 pixel pada 72 DPI
            // Untuk kualitas lebih baik, generate dengan 2-3x ukuran (untuk downscale yang lebih halus)
            // Misalnya: 80 points -> generate 200-240 pixels untuk hasil yang tajam
            $qrSizePixels = max(200, $qrSizePoints * 3); // Minimum 200px, atau 3x ukuran points
            
            log_message('debug', 'Generating QR code - Config size: ' . json_encode($qrSizeConfig) . ' points (' . $qrSizePoints . ' points), Generating at: ' . $qrSizePixels . ' pixels (high resolution for quality)');
            
            // Gunakan SimpleQRCodeService yang sudah ada
            $qrService = new \App\Services\SimpleQRCodeService();
            $qrCodePath = $qrService->generateQRCode($downloadUrl, $qrSizePixels);

            if (!$qrCodePath || !file_exists($qrCodePath)) {
                throw new Exception('QR Code generation failed');
            }

            log_message('info', 'QR Code generated successfully: ' . $qrCodePath . ' (size: ' . $qrSizePixels . 'x' . $qrSizePixels . ' pixels, will be inserted at ' . $qrSizePoints . 'x' . $qrSizePoints . ' points)');
            return $qrCodePath;
        } catch (Exception $e) {
            log_message('error', 'QR Code Generation Error: ' . $e->getMessage());
            return false;
        }
    }

    private function enhanceWithPython($sourcePath, $nomorPeraturan, $tanggalPenetapan, $qrCodePath, $quality = 80)
    {
        try {
            log_message('info', 'Starting Python enhancement process');

            $scriptDir = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR;
            // Ensure path consistency - normalize to forward slashes for Windows compatibility
            $scriptDir = str_replace(['/', '\\'], '/', $scriptDir);
            if (!is_dir($scriptDir)) {
                mkdir($scriptDir, 0777, true);
                log_message('info', 'Created temp directory: ' . $scriptDir);
            }

            $script = $this->createEnhancementScript();
            $scriptPath = $scriptDir . 'enhancement_script.py';
            // Ensure path consistency
            $scriptPath = str_replace(['/', '\\'], '/', $scriptPath);
            $bytesWritten = file_put_contents($scriptPath, $script);

            if ($bytesWritten === false) {
                log_message('error', 'Failed to write Python script to: ' . $scriptPath);
                return false;
            }

            log_message('info', 'Python script written: ' . $scriptPath . ' (' . $bytesWritten . ' bytes)');

            $outputPath = $scriptDir . 'enhanced_' . time() . '.pdf';
            // Ensure path consistency
            $outputPath = str_replace(['/', '\\'], '/', $outputPath);
            log_message('info', 'Output path: ' . $outputPath);

            // Ensure source path consistency
            $sourcePath = str_replace(['/', '\\'], '/', $sourcePath);

            // Ensure QR code path consistency
            $qrCodePath = str_replace(['/', '\\'], '/', $qrCodePath);

            // Execute Python script dengan path yang benar
            // getPythonCommand() already prioritizes Python with PyMuPDF installed
            $pythonCmd = $this->getPythonCommand();
            
            // Gunakan escapeshellarg untuk semua parameter (termasuk path Python)
            // escapeshellarg akan menambahkan quotes jika diperlukan
            $command = sprintf(
                '%s %s %s %s %s %s %s %d 2>&1',
                escapeshellarg($pythonCmd),
                escapeshellarg($scriptPath),
                escapeshellarg($sourcePath),
                escapeshellarg($outputPath),
                escapeshellarg($nomorPeraturan),
                escapeshellarg($tanggalPenetapan),
                escapeshellarg($qrCodePath),
                $quality
            );

            log_message('info', 'Executing Python command: ' . $command);

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            log_message('info', 'Python return code: ' . $returnCode);
            log_message('info', 'Python command output: ' . implode("\n", $output));

            // Check multiple possible output paths due to path normalization issues
            $possiblePaths = [
                $outputPath,
                str_replace('/', '\\', $outputPath),
                str_replace('\\', '/', $outputPath),
                realpath($outputPath) ?: $outputPath
            ];

            $actualOutputPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $actualOutputPath = $path;
                    log_message('info', 'Found output file at: ' . $path);
                    break;
                }
            }

            if ($returnCode === 0 && $actualOutputPath) {
                log_message('info', 'Python enhancement successful: ' . $actualOutputPath);
                return $actualOutputPath;
            }

            log_message('error', 'Python enhancement failed. Return code: ' . $returnCode);
            log_message('error', 'Tried alternative paths: ' . implode(', ', $possiblePaths));
            log_message('error', 'Python command output: ' . implode("\n", $output));
            if (!$actualOutputPath) {
                log_message('error', 'Output file not created in any expected location');
            }

            return false;
        } catch (Exception $e) {
            log_message('error', 'Python Enhancement Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    private function createEnhancementScript()
    {
        $nomorFallback = json_encode($this->positionConfig->nomorPeraturanFallback);
        $nomorKeywords = json_encode($this->positionConfig->nomorPeraturanKeywords);
        $tanggalFallback = json_encode($this->positionConfig->tanggalPenetapanFallback);
        $tanggalKeywords = json_encode($this->positionConfig->tanggalPenetapanKeywords);
        $qrPosition = json_encode($this->positionConfig->qrCodePosition);
        $qrCodeSize = json_encode($this->positionConfig->qrCodeSize);
        $qrKeywords = json_encode($this->positionConfig->qrCodeKeywords);
        
        // Get font file path
        $fontPath = APPPATH . 'PdfTools/fonts/BOOKOS.TTF';
        $fontPath = str_replace(['/', '\\'], '/', $fontPath);

        return "
import fitz
import os
from datetime import datetime

# Font file path
FONT_FILE_PATH = " . json_encode($fontPath) . "

# Konfigurasi posisi dari PHP
NOMOR_FALLBACK = $nomorFallback
NOMOR_FONT_SIZE = {$this->positionConfig->nomorPeraturanFontSize}
NOMOR_FONT_FAMILY = '{$this->positionConfig->nomorPeraturanFontFamily}'
NOMOR_COLOR = " . json_encode($this->positionConfig->nomorPeraturanColor) . "
NOMOR_OFFSET_Y = {$this->positionConfig->nomorPeraturanOffsetY}
NOMOR_KEYWORDS = $nomorKeywords

TANGGAL_FALLBACK = $tanggalFallback
TANGGAL_FONT_SIZE = {$this->positionConfig->tanggalPenetapanFontSize}
TANGGAL_FONT_FAMILY = '{$this->positionConfig->tanggalPenetapanFontFamily}'
TANGGAL_COLOR = " . json_encode($this->positionConfig->tanggalPenetapanColor) . "
TANGGAL_OFFSET_Y = {$this->positionConfig->tanggalPenetapanOffsetY}
TANGGAL_KEYWORDS = $tanggalKeywords
TANGGAL_PREFIX = '{$this->positionConfig->tanggalPenetapanPrefix}'

# Konfigurasi Footer TTE
ENABLE_TTE_FOOTER = " . ($this->positionConfig->enableTteFooter ? 'True' : 'False') . "
TTE_FOOTER_LINE1 = " . json_encode($this->positionConfig->tteFooterLine1) . "
TTE_FOOTER_LINE2 = " . json_encode($this->positionConfig->tteFooterLine2) . "
TTE_FOOTER_FONT_SIZE = {$this->positionConfig->tteFooterFontSize}
TTE_FOOTER_COLOR = " . json_encode($this->positionConfig->tteFooterColor) . "
TTE_FOOTER_MARGIN_BOTTOM = {$this->positionConfig->tteFooterMarginBottom}
TTE_FOOTER_MARGIN_BOTTOM_LINE2 = {$this->positionConfig->tteFooterMarginBottomLine2}
TTE_FOOTER_MARGIN_LEFT = {$this->positionConfig->tteFooterMarginLeft}

QR_POSITION = $qrPosition
QR_CODE_SIZE = $qrCodeSize
QR_KEYWORDS = $qrKeywords
USE_SMART_POSITIONING = " . ($this->positionConfig->useSmartPositioning ? 'True' : 'False') . "
USE_FALLBACK_POSITIONING = " . ($this->positionConfig->useFallbackPositioning ? 'True' : 'False') . "
CASE_SENSITIVE = " . ($this->positionConfig->caseSensitiveSearch ? 'True' : 'False') . "

def enhance_pdf_preserve():
    try:
        import sys
        
        if len(sys.argv) < 6:
            print('Usage: python script.py input.pdf output.pdf nomor tanggal qr_path quality')
            return False
            
        input_path = sys.argv[1]
        output_path = sys.argv[2]
        nomor_peraturan = sys.argv[3]
        tanggal_penetapan = sys.argv[4]
        qr_path = sys.argv[5]
        quality = int(sys.argv[6]) if len(sys.argv) > 6 else 80
        
        print(f'Processing: {input_path}')
        print(f'Output: {output_path}')
        print(f'Nomor: {nomor_peraturan}')
        print(f'Tanggal: {tanggal_penetapan}')
        print(f'QR: {qr_path}')
        
        # Buka dokumen PDF
        if not os.path.exists(input_path):
            print(f'Error: Input file not found: {input_path}')
            return False
            
        doc = fitz.open(input_path)
        print(f'PDF opened successfully. Pages: {len(doc)}')
        
        # Load Bookman Old Style font from file
        bookman_font = None
        if os.path.exists(FONT_FILE_PATH):
            try:
                bookman_font = fitz.Font(fontfile=FONT_FILE_PATH)
                print(f'Bookman Old Style font loaded successfully from: {FONT_FILE_PATH}')
            except Exception as e:
                print(f'Warning: Could not load font file {FONT_FILE_PATH}: {e}')
                print('Will use default font as fallback')
        else:
            print(f'Warning: Font file not found: {FONT_FILE_PATH}')
            print('Will use default font as fallback')
        
        # Cari posisi untuk nomor, tanggal, dan QR di halaman yang tepat
        nomor_pos = None
        tanggal_pos = None
        qr_pos = None
        nomor_page = None
        tanggal_page = None
        qr_page = None
        
        # Cari placeholder yang tepat di semua halaman (STRICT VALIDATION)
        print('=== STRICT PLACEHOLDER VALIDATION ===')
        print('Only searching for: $' + '{NO}, $' + '{TGL}, $' + '{QR}')
        
        nomor_positions = []  # List untuk multiple placement
        tanggal_positions = []  # List untuk multiple placement
        qr_positions = []  # List untuk multiple placement
        
        for page_num in range(len(doc)):
            page = doc[page_num]
            print('Searching page ' + str(page_num + 1) + '/' + str(len(doc)))
            
            # Cari placeholder di halaman ini
            nomor_pos = find_nomor_position_strict(page, nomor_peraturan)
            if nomor_pos:
                nomor_positions.append({
                    'page': page,
                    'page_num': page_num,
                    'position': nomor_pos
                })
                print('Found NO placeholder in page ' + str(page_num + 1))
            
            # Cari TGL placeholder di halaman ini
            tanggal_pos = find_tanggal_position_strict(page, tanggal_penetapan)
            if tanggal_pos:
                tanggal_positions.append({
                    'page': page,
                    'page_num': page_num,
                    'position': tanggal_pos
                })
                print('Found TGL placeholder in page ' + str(page_num + 1))
            
            # Cari QR placeholder di halaman ini
            qr_pos = find_qr_position_strict(page)
            if qr_pos:
                qr_positions.append({
                    'page': page,
                    'page_num': page_num,
                    'position': qr_pos
                })
                print('Found QR placeholder in page ' + str(page_num + 1))
        
        # VALIDASI KETAT: Gagal jika tidak ada placeholder yang ditemukan
        if not nomor_positions:
            print('ERROR: NO placeholder not found in any page!')
            return False
        
        if not tanggal_positions:
            print('ERROR: TGL placeholder not found in any page!')
            return False
        
        if not qr_positions:
            print('ERROR: QR placeholder not found in any page!')
            return False
        
        print('=== STRICT VALIDATION PASSED ===')
        print('Found NO in ' + str(len(nomor_positions)) + ' page(s)')
        print('Found TGL in ' + str(len(tanggal_positions)) + ' page(s)')
        print('Found QR in ' + str(len(qr_positions)) + ' page(s)')
        
        # MULTIPLE PLACEMENT: Insert elemen di semua halaman yang ditemukan
        print('=== MULTIPLE PLACEMENT PROCESSING ===')
        
        # Insert nomor peraturan di semua halaman yang ditemukan
        for nomor_item in nomor_positions:
            page = nomor_item['page']
            page_num = nomor_item['page_num']
            position = nomor_item['position']
            print('Inserting nomor in page ' + str(page_num + 1) + ' at: ' + str(position))
            # Insert nomor dengan Bookman Old Style font
            try:
                if bookman_font and os.path.exists(FONT_FILE_PATH):
                    page.insert_text((position[0], position[1]), nomor_peraturan, fontsize=NOMOR_FONT_SIZE, fontfile=FONT_FILE_PATH, color=NOMOR_COLOR)
                else:
                    # Fallback to default font
                    page.insert_text((position[0], position[1]), nomor_peraturan, fontsize=NOMOR_FONT_SIZE, color=NOMOR_COLOR)
            except Exception as e:
                print('Font error, using fallback: ' + str(e))
                page.insert_text((position[0], position[1]), nomor_peraturan, fontsize=NOMOR_FONT_SIZE, color=NOMOR_COLOR)
        
        # Insert tanggal penetapan di semua halaman yang ditemukan
        for tanggal_item in tanggal_positions:
            page = tanggal_item['page']
            page_num = tanggal_item['page_num']
            position = tanggal_item['position']
            print('Inserting tanggal in page ' + str(page_num + 1) + ' at: ' + str(position))
            # Insert tanggal dengan Bookman Old Style font
            try:
                if bookman_font and os.path.exists(FONT_FILE_PATH):
                    page.insert_text((position[0], position[1]), TANGGAL_PREFIX + tanggal_penetapan, fontsize=TANGGAL_FONT_SIZE, fontfile=FONT_FILE_PATH, color=TANGGAL_COLOR)
                else:
                    # Fallback to default font
                    page.insert_text((position[0], position[1]), TANGGAL_PREFIX + tanggal_penetapan, fontsize=TANGGAL_FONT_SIZE, color=TANGGAL_COLOR)
            except Exception as e:
                print('Font error, using fallback: ' + str(e))
                page.insert_text((position[0], position[1]), TANGGAL_PREFIX + tanggal_penetapan, fontsize=TANGGAL_FONT_SIZE, color=TANGGAL_COLOR)
        
        # Insert QR code di semua halaman yang ditemukan
        if os.path.exists(qr_path):
            for qr_item in qr_positions:
                page = qr_item['page']
                page_num = qr_item['page_num']
                position = qr_item['position']
                print('Inserting QR code in page ' + str(page_num + 1) + ' at: ' + str(position))
                print('QR code size from config: ' + str(QR_CODE_SIZE))
                
                # Pastikan position adalah rect dengan ukuran yang benar
                # position bisa berupa tuple (x0, y0, x1, y1) atau (x0, y0)
                if isinstance(position, tuple) and len(position) == 4:
                    # Sudah dalam format rect (x0, y0, x1, y1)
                    rect = fitz.Rect(position[0], position[1], position[2], position[3])
                    print('Using rect for QR code: ' + str(rect))
                    page.insert_image(rect, filename=qr_path)
                elif isinstance(position, tuple) and len(position) == 2:
                    # Hanya posisi (x0, y0), buat rect dengan ukuran dari config
                    qr_width = QR_CODE_SIZE[0]
                    qr_height = QR_CODE_SIZE[1]
                    rect = fitz.Rect(position[0], position[1], position[0] + qr_width, position[1] + qr_height)
                    print('Creating rect from position: ' + str(position) + ' with size: ' + str(QR_CODE_SIZE))
                    page.insert_image(rect, filename=qr_path)
                else:
                    # Fallback: gunakan position langsung
                    print('Using position directly (fallback): ' + str(position))
                    page.insert_image(position, filename=qr_path)
        else:
            print('WARNING: QR code file not found: ' + qr_path)
        
        # Tambahkan footer TTE di setiap halaman
        print('=== ADDING TTE FOOTER TO ALL PAGES ===')
        for page_num in range(len(doc)):
            page = doc[page_num]
            print('Adding TTE footer to page ' + str(page_num + 1))
            add_tte_footer(page)
        
        # Simpan dokumen
        try:
            doc.save(output_path)
            doc.close()
            
            if os.path.exists(output_path):
                print(f'PDF enhancement completed successfully: {output_path}')
                print(f'Output file size: {os.path.getsize(output_path)} bytes')
                return True
            else:
                print(f'Error: Output file not created: {output_path}')
                return False
        except Exception as e:
            print(f'Error saving PDF: {str(e)}')
            return False
        
    except Exception as e:
        print(f'Error: {str(e)}')
        return False
        
    return True

def find_and_remove_nomor_placeholder(page, nomor_peraturan):
    '''Cari dan hapus placeholder NO, return posisi untuk insert'''
    try:
        print('STRICT: Searching for NO placeholder to remove')
        
        # Hanya cari placeholder yang tepat (case sensitive)
        keyword = '$' + '{NO}'
        text_instances = page.search_for(keyword)
        print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
        
        if text_instances:
            # Ambil posisi pertama yang ditemukan
            rect = text_instances[0]
            print('Found NO placeholder at position: ' + str(rect))
            
            # HAPUS placeholder dengan mengganti dengan teks kosong
            page.add_redact_annot(rect, fill=(1, 1, 1))  # White fill
            page.apply_redactions()
            print('NO placeholder removed successfully')
            
            # Return posisi dengan offset yang dinaikkan 1 point
            return (rect.x0, rect.y0 + NOMOR_OFFSET_Y + 1)
        
        print('NO placeholder not found in this page')
        return None
        
    except Exception as e:
        print('Error finding and removing nomor position: ' + str(e))
        return None

def find_nomor_position_strict(page, nomor_peraturan):
    '''Legacy function - redirect to new version'''
    return find_and_remove_nomor_placeholder(page, nomor_peraturan)

def find_nomor_position(page, nomor_peraturan):
    '''Legacy function - redirect to strict version'''
    return find_nomor_position_strict(page, nomor_peraturan)

def find_and_remove_tanggal_placeholder(page, tanggal_penetapan):
    '''Cari dan hapus placeholder TGL, return posisi untuk insert'''
    try:
        print('STRICT: Searching for TGL placeholder to remove')
        
        # Hanya cari placeholder yang tepat (case sensitive)
        keyword = '$' + '{TGL}'
        text_instances = page.search_for(keyword)
        print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
        
        if text_instances:
            # Ambil posisi pertama yang ditemukan
            rect = text_instances[0]
            print('Found TGL placeholder at position: ' + str(rect))
            
            # HAPUS placeholder dengan mengganti dengan teks kosong
            page.add_redact_annot(rect, fill=(1, 1, 1))  # White fill
            page.apply_redactions()
            print('TGL placeholder removed successfully')
            
            # Return posisi dengan offset yang dinaikkan 1 point
            return (rect.x0, rect.y0 + TANGGAL_OFFSET_Y + 1)
        
        print('TGL placeholder not found in this page')
        return None
        
    except Exception as e:
        print('Error finding and removing tanggal position: ' + str(e))
        return None

def find_tanggal_position_strict(page, tanggal_penetapan):
    '''Legacy function - redirect to new version'''
    return find_and_remove_tanggal_placeholder(page, tanggal_penetapan)

def find_and_remove_qr_placeholder(page):
    '''Cari dan hapus placeholder QR, return posisi untuk insert dengan ukuran dari config'''
    try:
        print('STRICT: Searching for QR placeholder to remove')
        
        # Hanya cari placeholder yang tepat (case sensitive)
        keyword = '$' + '{QR}'
        text_instances = page.search_for(keyword)
        print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
        
        if text_instances:
            rect = text_instances[0]
            print('Found QR placeholder at position: ' + str(rect))
            
            # HAPUS placeholder dengan mengganti dengan teks kosong
            page.add_redact_annot(rect, fill=(1, 1, 1))  # White fill
            page.apply_redactions()
            print('QR placeholder removed successfully')
            
            # Return rectangle untuk QR code dengan ukuran dari konfigurasi
            # Gunakan ukuran dari config, bukan dari placeholder
            qr_width = QR_CODE_SIZE[0]
            qr_height = QR_CODE_SIZE[1]
            print('QR code will be inserted at size: ' + str(qr_width) + 'x' + str(qr_height) + ' points (from config)')
            # Posisi: gunakan x0, y0 dari placeholder, tapi ukuran dari config
            return (rect.x0, rect.y0, rect.x0 + qr_width, rect.y0 + qr_height)
        
        print('QR placeholder not found in this page')
        return None
        
    except Exception as e:
        print('Error finding and removing QR position: ' + str(e))
        return None

def find_qr_position_strict(page):
    '''Legacy function - redirect to new version'''
    return find_and_remove_qr_placeholder(page)

def find_tanggal_position(page, tanggal_penetapan):
    '''Cari posisi yang tepat untuk insert tanggal penetapan'''
    try:
        print('Searching for tanggal keywords: ' + str(TANGGAL_KEYWORDS))
        
        # Cari berdasarkan placeholder yang unik
        for keyword in TANGGAL_KEYWORDS:
            print('Searching for keyword: ' + keyword)
            # Untuk placeholder, gunakan exact match (case sensitive)
            text_instances = page.search_for(keyword)
            print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
            
            if text_instances:
                # Ambil posisi pertama yang ditemukan
                rect = text_instances[0]
                print('Found tanggal placeholder: ' + keyword + ' at position: ' + str(rect))
                return (rect.x0, rect.y0 + TANGGAL_OFFSET_Y)
        
        # Jika placeholder tidak ditemukan, cari kata tanggal sebagai fallback
        print('No tanggal placeholder found, searching for tanggal keyword...')
        tanggal_fallback_keywords = ['tanggal', 'Tanggal', 'TANGGAL', 'Tanggal Penetapan', 'tanggal penetapan']
        
        for keyword in tanggal_fallback_keywords:
            print('Searching for fallback keyword: ' + keyword)
            text_instances = page.search_for(keyword)
            print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
            
            if text_instances:
                rect = text_instances[0]
                print('Found tanggal fallback keyword: ' + keyword + ' at position: ' + str(rect))
                return (rect.x0, rect.y0 + TANGGAL_OFFSET_Y)
        
        print('No tanggal keywords found, using fallback position')
        return None
        
    except Exception as e:
        print(f'Error finding tanggal position: {str(e)}')
        return None

def find_qr_position(page):
    '''Cari posisi yang tepat untuk insert QR code'''
    try:
        print(f'Searching for QR keywords: {QR_KEYWORDS}')
        
        # Cari berdasarkan placeholder yang unik
        for keyword in QR_KEYWORDS:
            print('Searching for keyword: ' + keyword)
            # Untuk placeholder, gunakan exact match (case sensitive)
            text_instances = page.search_for(keyword)
            print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
            
            if text_instances:
                rect = text_instances[0]
                print('Found QR placeholder: ' + keyword + ' at position: ' + str(rect))
                # Return rectangle untuk QR code (x1, y1, x2, y2)
                qr_size = 150  # Default QR code size
                return (rect.x0, rect.y0, rect.x0 + qr_size, rect.y0 + qr_size)
        
        # Jika placeholder tidak ditemukan, cari kata qr atau QR sebagai fallback
        print('No QR placeholder found, searching for QR fallback keywords...')
        qr_fallback_keywords = ['qr', 'QR', 'qrcode', 'QRCode', 'QR Code', 'qr code']
        
        for keyword in qr_fallback_keywords:
            print('Searching for QR fallback keyword: ' + keyword)
            text_instances = page.search_for(keyword)
            print('Found ' + str(len(text_instances)) + ' instances for ' + keyword)
            
            if text_instances:
                rect = text_instances[0]
                print('Found QR fallback keyword: ' + keyword + ' at position: ' + str(rect))
                qr_size = 150
                return (rect.x0, rect.y0, rect.x0 + qr_size, rect.y0 + qr_size)
        
        print('No QR keywords found, using fallback position')
        return None
        
    except Exception as e:
        print(f'Error finding QR position: {str(e)}')
        return None

def add_tte_footer(page):
    '''Tambahkan footer TTE di halaman PDF'''
    try:
        if not ENABLE_TTE_FOOTER:
            print('TTE Footer disabled, skipping...')
            return True
            
        print('Adding TTE Footer...')
        
        # Dapatkan ukuran halaman
        page_rect = page.rect
        page_width = page_rect.width
        page_height = page_rect.height
        
        # Hitung posisi footer (dari bawah halaman)
        footer_y1 = page_height - TTE_FOOTER_MARGIN_BOTTOM
        footer_y2 = page_height - TTE_FOOTER_MARGIN_BOTTOM_LINE2
        
        # Hitung lebar teks untuk center alignment
        # Estimasi lebar teks berdasarkan font size
        char_width = TTE_FOOTER_FONT_SIZE * 0.48  # Estimasi 0.48 point per karakter untuk font 10
        
        # Baris 1
        text_width1 = len(TTE_FOOTER_LINE1) * char_width
        center_x1 = (page_width - text_width1) / 2 + TTE_FOOTER_MARGIN_LEFT
        
        # Baris 2
        text_width2 = len(TTE_FOOTER_LINE2) * char_width
        center_x2 = (page_width - text_width2) / 2 + TTE_FOOTER_MARGIN_LEFT
        
        # Pastikan posisi tidak negatif
        if center_x1 < 0:
            center_x1 = 10
        if center_x2 < 0:
            center_x2 = 10
        
        # Insert footer text di tengah halaman dengan Bookman Old Style font
        try:
            if os.path.exists(FONT_FILE_PATH):
                page.insert_text(
                    (center_x1, footer_y1),
                    TTE_FOOTER_LINE1,
                    fontsize=TTE_FOOTER_FONT_SIZE,
                    fontfile=FONT_FILE_PATH,
                    color=TTE_FOOTER_COLOR,
                    overlay=True
                )
                
                page.insert_text(
                    (center_x2, footer_y2),
                    TTE_FOOTER_LINE2,
                    fontsize=TTE_FOOTER_FONT_SIZE,
                    fontfile=FONT_FILE_PATH,
                    color=TTE_FOOTER_COLOR,
                    overlay=True
                )
            else:
                # Fallback to default font
                page.insert_text(
                    (center_x1, footer_y1),
                    TTE_FOOTER_LINE1,
                    fontsize=TTE_FOOTER_FONT_SIZE,
                    color=TTE_FOOTER_COLOR,
                    overlay=True
                )
                
                page.insert_text(
                    (center_x2, footer_y2),
                    TTE_FOOTER_LINE2,
                    fontsize=TTE_FOOTER_FONT_SIZE,
                    color=TTE_FOOTER_COLOR,
                    overlay=True
                )
        except Exception as e:
            print(f'Font error in footer, using fallback: {e}')
            # Fallback to default font
            page.insert_text(
                (center_x1, footer_y1),
                TTE_FOOTER_LINE1,
                fontsize=TTE_FOOTER_FONT_SIZE,
                color=TTE_FOOTER_COLOR,
                overlay=True
            )
            
            page.insert_text(
                (center_x2, footer_y2),
                TTE_FOOTER_LINE2,
                fontsize=TTE_FOOTER_FONT_SIZE,
                color=TTE_FOOTER_COLOR,
                overlay=True
            )
        
        print('TTE Footer added successfully')
        print('Line 1: ' + TTE_FOOTER_LINE1 + ' at (' + str(center_x1) + ', ' + str(footer_y1) + ')')
        print('Line 2: ' + TTE_FOOTER_LINE2 + ' at (' + str(center_x2) + ', ' + str(footer_y2) + ')')
        return True
        
    except Exception as e:
        print(f'Error adding TTE footer: {str(e)}')
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    enhance_pdf_preserve()
";
    }

    private function enhanceWithTCPDF($sourcePath, $nomorPeraturan, $tanggalPenetapan, $qrCodePath)
    {
        try {
            log_message('info', 'Starting TCPDF fallback enhancement process');

            // For now, return false as TCPDF cannot easily overlay existing PDFs
            // This would require FPDI library or similar for proper PDF overlay
            log_message('warning', 'TCPDF fallback not implemented - requires FPDI library for PDF overlay');
            return false;
        } catch (Exception $e) {
            log_message('error', 'TCPDF Enhancement Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get next number for jenis peraturan
     */
    public function getNextNumberForJenis($namaJenis)
    {
        $db = \Config\Database::connect();

        // Get current year
        $currentYear = date('Y');

        // Get last number for this jenis in current year
        $query = $db->table('harmonisasi_nomor_peraturan')
            ->where('jenis_peraturan', $namaJenis)
            ->where('tahun', $currentYear)
            ->orderBy('urutan', 'DESC')
            ->limit(1)
            ->get();

        if ($query->getNumRows() > 0) {
            $lastRecord = $query->getRow();
            return $lastRecord->urutan + 1;
        }

        return 1; // First number for this jenis in current year
    }

    /**
     * Generate nomor peraturan
     */
    public function generateNomorPeraturan($namaJenis, $urutan)
    {
        // Format nomor peraturan hanya urutan saja
        return (string) $urutan;
    }

    /**
     * Format tanggal Indonesia
     */
    public function formatTanggalIndonesia()
    {
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $hari = date('d');
        $bulanNum = (int)date('m');
        $tahun = date('Y');

        return "{$hari} {$bulan[$bulanNum]} {$tahun}";
    }

    /**
     * Validasi dan perbaiki tanggal pengesahan dari database
     */
    public function validateAndFixTanggalPengesahan($tanggalPengesahan)
    {
        // Jika tanggal tidak valid, gunakan tanggal hari ini
        if (
            empty($tanggalPengesahan) ||
            $tanggalPengesahan === '0000-00-00' ||
            $tanggalPengesahan === '0000-00-00 00:00:00' ||
            strpos($tanggalPengesahan, '0000-00-00') !== false
        ) {

            log_message('warning', 'Tanggal pengesahan tidak valid: ' . $tanggalPengesahan . ', menggunakan tanggal hari ini');
            return $this->formatTanggalIndonesia();
        }

        // Jika tanggal valid, format ke Indonesia
        try {
            $date = new \DateTime($tanggalPengesahan);
            $bulan = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

            $hari = $date->format('d');
            $bulanNum = (int)$date->format('m');
            $tahun = $date->format('Y');

            return "{$hari} {$bulan[$bulanNum]} {$tahun}";
        } catch (\Exception $e) {
            log_message('error', 'Error parsing tanggal: ' . $tanggalPengesahan . ', menggunakan tanggal hari ini');
            return $this->formatTanggalIndonesia();
        }
    }
}

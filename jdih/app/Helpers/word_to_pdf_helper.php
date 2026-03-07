<?php

if (!function_exists('convertWordToPdf')) {
    /**
     * Convert Word document to PDF using LibreOffice
     * @param string $inputPath Path to input Word document
     * @param string $outputPath Path where PDF should be saved
     * @return bool True on success, throws exception on failure
     */
    function convertWordToPdf($inputPath, $outputPath)
    {
        // Normalize path
        $inputPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $inputPath);
        $outputPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $outputPath);
        
        // Log input/output paths untuk debugging
        log_message('debug', 'Input path: ' . $inputPath);
        log_message('debug', 'Output path: ' . $outputPath);
        
        // Cek file input
        if (!file_exists($inputPath)) {
            throw new \Exception("File input tidak ditemukan: " . $inputPath);
        }
        
        // Cek file lock dengan retry
        $maxRetries = 5;
        $waitMs = 300; // 300ms antar percobaan
        $retry = 0;
        while ($retry < $maxRetries) {
            $fp = @fopen($inputPath, "r+");
            if ($fp !== false) {
                fclose($fp);
                break; // file tidak locked
            }
            if ($retry == $maxRetries - 1) {
                throw new \Exception('File sedang digunakan oleh proses lain. Silakan tutup file tersebut.');
            }
            usleep($waitMs * 1000); // tunggu sebelum retry
            $retry++;
        }

        try {
            if (!file_exists($inputPath)) {
                throw new \Exception("Input file not found: " . $inputPath);
            }

            // Get the directories
            $inputDir = dirname($inputPath);
            $outputDir = dirname($outputPath);
            
            // Ensure output directory exists and is writable
            if (!is_dir($outputDir)) {
                if (!mkdir($outputDir, 0777, true)) {
                    throw new \Exception("Gagal membuat direktori output: " . $outputDir);
                }
            } elseif (!is_writable($outputDir)) {
                throw new \Exception("Direktori output tidak bisa ditulisi: " . $outputDir);
            }

            // Detect operating system and set LibreOffice paths
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            if ($isWindows) {
                // Windows LibreOffice paths
                $libreOfficePaths = [
                    'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                    'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
                    'C:\\Program Files\\LibreOffice 7\\program\\soffice.exe',
                    'C:\\Program Files (x86)\\LibreOffice 7\\program\\soffice.exe'
                ];
            } else {
                // Linux LibreOffice paths
                $libreOfficePaths = [
                    '/usr/bin/soffice',
                    '/usr/bin/libreoffice',
                    '/usr/lib/libreoffice/program/soffice',
                    '/opt/libreoffice/program/soffice'
                ];
            }

            // Find LibreOffice executable
            $soffice = null;
            foreach ($libreOfficePaths as $path) {
                if (file_exists($path)) {
                    $soffice = $path;
                    break;
                }
            }

            // If not found in common paths, try using command directly (if in PATH)
            if (!$soffice) {
                $command = $isWindows ? 'where soffice' : 'which soffice';
                exec($command, $output, $returnVar);
                if ($returnVar === 0 && !empty($output[0])) {
                    $soffice = $output[0];
                }
            }

            if (!$soffice) {
                throw new \Exception('LibreOffice tidak ditemukan. Untuk Linux, install dengan: sudo apt-get install libreoffice');
            }

            // Prepare command
            if ($isWindows) {
                $command = '"' . $soffice . '" --headless --convert-to pdf:writer_pdf_Export --outdir ' . 
                          escapeshellarg($outputDir) . ' ' . escapeshellarg($inputPath);
            } else {
                $command = escapeshellarg($soffice) . ' --headless --convert-to pdf:writer_pdf_Export --outdir ' . 
                          escapeshellarg($outputDir) . ' ' . escapeshellarg($inputPath);
            }
            
            // Execute conversion
            $output = [];
            $returnVar = 0;
            log_message('debug', '[Word2PDF] Menjalankan command: ' . $command);
            exec($command . ' 2>&1', $output, $returnVar);
            log_message('debug', '[Word2PDF] ReturnVar: ' . $returnVar);
            log_message('debug', '[Word2PDF] Output: ' . implode("\n", $output));

            if ($returnVar !== 0) {
                log_message('error', 'Word to PDF conversion failed. Command: ' . $command);
                log_message('error', 'Error output: ' . implode("\n", $output));
                throw new \Exception('Gagal mengkonversi dokumen Word ke PDF. Error: ' . implode("\n", $output));
            }

            // LibreOffice akan menghasilkan nama file dengan ekstensi .pdf
            $inputFilename = pathinfo($inputPath, PATHINFO_FILENAME);
            $expectedPdfName = rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR . $inputFilename . '.pdf';
            
            // Normalize paths untuk konsistensi
            $expectedPdfName = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $expectedPdfName);
            $outputPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $outputPath);
            
            // Rename jika nama file output berbeda
            if ($expectedPdfName !== $outputPath && file_exists($expectedPdfName)) {
                if (file_exists($outputPath)) {
                    @unlink($outputPath);
                }
                if (!@rename($expectedPdfName, $outputPath)) {
                    throw new \Exception("Gagal mengganti nama file PDF");
                }
            }

            // Verifikasi file output
            if (!file_exists($outputPath)) {
                // Coba cari file dengan ekstensi .pdf di direktori output
                $pdfFiles = glob($outputDir . DIRECTORY_SEPARATOR . '*.pdf');
                if (!empty($pdfFiles)) {
                    $outputPath = $pdfFiles[0];
                } else {
                    throw new \Exception("File PDF tidak berhasil dibuat. Pastikan LibreOffice terinstall dengan benar.");
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error converting Word to PDF: ' . $e->getMessage());
            throw $e;
        }
    }
}
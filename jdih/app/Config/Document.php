<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Document extends BaseConfig
{
    /**
     * Path ke Python executable
     * Untuk Windows: gunakan full path atau 'py' command
     * Untuk macOS/Linux: gunakan 'python3' atau full path
     */
    public string $pythonPath = '';
    
    /**
     * Auto-detect Python path berdasarkan OS
     */
    private function detectPythonPath(): string
    {
        // Jika sudah di-set manual, gunakan itu
        if (!empty($this->pythonPath)) {
            return $this->pythonPath;
        }
        
        // Deteksi OS
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $isMac = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
        $isLinux = strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX';
        
        // Windows: cari full path ke Python
        if ($isWindows) {
            $localAppData = getenv('LOCALAPPDATA');
            if ($localAppData) {
                // Cari py.exe (Python Launcher)
                $pyLauncher = $localAppData . '\\Programs\\Python\\Launcher\\py.exe';
                if (file_exists($pyLauncher)) {
                    return '"' . $pyLauncher . '"';
                }
                
                // Cari python.exe langsung
                $pythonBaseDir = $localAppData . '\\Programs\\Python';
                if (is_dir($pythonBaseDir)) {
                    $entries = @scandir($pythonBaseDir);
                    if ($entries !== false) {
                        foreach ($entries as $entry) {
                            if ($entry !== '.' && $entry !== '..' && 
                                strpos($entry, 'Python') === 0 && 
                                is_dir($pythonBaseDir . '\\' . $entry)) {
                                $pythonExe = $pythonBaseDir . '\\' . $entry . '\\python.exe';
                                if (file_exists($pythonExe)) {
                                    return '"' . $pythonExe . '"';
                                }
                            }
                        }
                    }
                }
            }
            
            // Fallback: coba command biasa
            $possibleCommands = ['py', 'python', 'python3'];
            foreach ($possibleCommands as $cmd) {
                $output = [];
                exec("$cmd --version 2>&1", $output, $returnCode);
                if ($returnCode === 0) {
                    return $cmd;
                }
            }
            
            return 'py'; // Ultimate fallback
        }
        
        // macOS/Linux: gunakan python3
        if ($isMac || $isLinux) {
            $possiblePaths = [
                '/usr/bin/python3',
                '/usr/local/bin/python3',
                '/opt/homebrew/bin/python3',
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
            
            return 'python3'; // Fallback
        }
        
        // Default fallback
        return 'python';
    }
    
    /**
     * Konfigurasi TTE API
     */
    public array $tteConfig = [
        'apiUrl' => 'https://api-tteservice.example.com/sign',
        'apiKey' => 'your-api-key-here',
        'timeout' => 30
    ];
    
    /**
     * Path untuk penyimpanan dokumen
     */
    public array $paths = [
        'draft' => WRITEPATH . 'documents/draft',
        'processed' => WRITEPATH . 'documents/processed',
        'signed' => WRITEPATH . 'documents/signed',
        'temp' => WRITEPATH . 'temp'
    ];
    
    /**
     * Format penomoran dokumen
     */
    public array $numberingFormats = [
        'default' => '{PREFIX}/{NUMBER}/{YEAR}',
        'peraturan_walikota' => 'PERWALI/{NUMBER}/{YEAR}',
        'peraturan_daerah' => 'PERDA/{NUMBER}/{YEAR}',
        'keputusan_walikota' => 'KEPWALI/{NUMBER}/{YEAR}'
    ];
    
    public function __construct()
    {
        parent::__construct();
        
        // Auto-detect Python path jika belum di-set
        if (empty($this->pythonPath)) {
            $this->pythonPath = $this->detectPythonPath();
        }
        
        // Buat direktori jika belum ada
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }
    
    /**
     * Get Python path (dengan auto-detection)
     */
    public function getPythonPath(): string
    {
        if (empty($this->pythonPath)) {
            $this->pythonPath = $this->detectPythonPath();
        }
        return $this->pythonPath;
    }
}

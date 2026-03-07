<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Synonyms extends BaseConfig
{
    public array $list = [
        // Jenis peraturan
        'peraturan' => ['regulasi', 'aturan', 'ketentuan', 'norma', 'kaidah'],
        'undang-undang' => ['uu', 'undang undang', 'law', 'statute'],
        'peraturan pemerintah' => ['pp', 'perpem', 'government regulation'],
        'peraturan presiden' => ['perpres', 'pres', 'presidential regulation'],
        'peraturan menteri' => ['permen', 'permenteri', 'ministerial regulation'],
        'keputusan' => ['kepmen', 'kep', 'sk', 'surat keputusan', 'decree'],
        'instruksi' => ['inpres', 'instruksi presiden', 'instruction'],
        'surat edaran' => ['se', 'surat', 'circular letter'],
        
        // Domain/bidang
        'pajak' => ['tax', 'pungutan', 'retribusi', 'fiscal', 'tarif'],
        'lingkungan' => ['environment', 'ekologi', 'alam', 'environmental'],
        'pendidikan' => ['education', 'sekolah', 'universitas', 'akademik', 'pembelajaran'],
        'kesehatan' => ['health', 'medis', 'rumah sakit', 'medical', 'healthcare'],
        'ekonomi' => ['economy', 'bisnis', 'perdagangan', 'commercial', 'usaha'],
        'hukum' => ['law', 'legal', 'yuridis', 'judicial', 'peradilan'],
        'sosial' => ['social', 'masyarakat', 'komunitas', 'kemasyarakatan'],
        'keuangan' => ['finance', 'financial', 'monetary', 'anggaran', 'budget'],
        'investasi' => ['investment', 'penanaman modal', 'modal', 'capital'],
        'tenaga kerja' => ['employment', 'pekerja', 'buruh', 'labor', 'karyawan'],
        'pertanian' => ['agriculture', 'farming', 'agrikultur', 'tani'],
        'industri' => ['industry', 'manufacturing', 'pabrik', 'produksi'],
        'transportasi' => ['transport', 'angkutan', 'lalu lintas', 'traffic'],
        'telekomunikasi' => ['telecommunication', 'komunikasi', 'internet', 'digital'],
        
        // Topik sosial khusus
        'anak' => ['bocah', 'balita', 'remaja', 'juvenile', 'minor'],
        'jalanan' => ['gelandangan', 'tunawisma', 'homeless', 'pengamen', 'pengemis'],
        'terlantar' => ['yatim', 'piatu', 'yatim piatu', 'tidak terurus', 'terabaikan'],
        'pembinaan' => ['bimbingan', 'pelatihan', 'pendampingan', 'coaching', 'mentoring'],
        'rehabilitasi' => ['pemulihan', 'penyembuhan', 'restorasi', 'recovery'],
        'bantuan' => ['santunan', 'tunjangan', 'subsidi', 'dukungan', 'support'],
        'kesejahteraan' => ['kemakmuran', 'prosperity', 'well-being', 'welfare'],
        'pemberdayaan' => ['empowerment', 'penguatan', 'capacity building'],
        
        // Kata kerja/aksi
        'pembentukan' => ['formasi', 'creation', 'establishment', 'pendirian'],
        'pelaksanaan' => ['implementation', 'execution', 'penerapan'],
        'pengawasan' => ['supervision', 'monitoring', 'control', 'surveillance'],
        'pengendalian' => ['control', 'regulation', 'management'],
        'perlindungan' => ['protection', 'safeguard', 'security'],
        'penanganan' => ['penanggulangan', 'penyelesaian', 'treatment', 'handling'],
        'menanggapi' => ['merespon', 'menangani', 'menghadapi', 'menyikapi'],
        
        // Istilah umum
        'standar' => ['standard', 'criteria', 'benchmark', 'kriteria'],
        'prosedur' => ['procedure', 'process', 'tata cara', 'mekanisme'],
        'sistem' => ['system', 'mechanism', 'framework', 'struktur'],
        'kebijakan' => ['policy', 'strategy', 'strategi'],
        'program' => ['programme', 'scheme', 'rencana'],
        'layanan' => ['service', 'pelayanan', 'fasilitas'],
        'khusus' => ['spesial', 'tertentu', 'spesifik', 'particular']
    ];
}
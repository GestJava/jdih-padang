<?php

/**
 * Metadata Helper - Versi JSON Column
 * Sesuai Permenkumham No. 8 Tahun 2019
 * 
 * Menggunakan kolom metadata_json untuk menyimpan metadata khusus
 */

if (!function_exists('get_metadata_by_kategori')) {
    function get_metadata_by_kategori($peraturan, $kategori)
    {
        $metadata_json = $peraturan['metadata_json'] ?? null;

        if (!$metadata_json) {
            $result = get_metadata_fallback($peraturan, $kategori);
        } else {
            $metadata = json_decode($metadata_json, true);
            if (!$metadata) {
                $result = get_metadata_fallback($peraturan, $kategori);
            } else {
                switch ($kategori) {
                    case 'Produk Hukum':
                        $result = get_metadata_produk_hukum($peraturan);
                        break;
                    case 'Monografi Hukum':
                        $result = get_metadata_monografi_hukum_json($metadata, $peraturan);
                        break;
                    case 'Artikel Hukum':
                        $result = get_metadata_artikel_hukum_json($metadata, $peraturan);
                        break;
                    case 'Yurisprudensi':
                        $result = get_metadata_yurisprudensi_json($metadata, $peraturan);
                        break;
                    case 'Perjanjian Internasional':
                        $result = get_metadata_perjanjian_internasional_json($metadata, $peraturan);
                        break;
                    case 'Putusan Mahkamah Konstitusi':
                        $result = get_metadata_putusan_mk_json($metadata, $peraturan);
                        break;
                    default:
                        $result = get_metadata_fallback($peraturan, $kategori);
                        break;
                }
            }
        }

        // Inject New Metadata Fields
        $result['lokasi'] = 'Bagian Hukum Kota Padang';
        $is_english = (strpos($kategori, 'Regulation') !== false || strpos($kategori, 'Terjemahan') !== false);
        $result['bahasa'] = $is_english ? 'English' : 'Indonesia';
        $result['singkatan'] = get_singkatan_jenis($kategori);

        return $result;
    }
}

if (!function_exists('get_singkatan_jenis')) {
    function get_singkatan_jenis($kategori)
    {
        $mapping = [
            'Peraturan Daerah' => 'Perda',
            'Peraturan Walikota' => 'Perwako',
            'Keputusan Walikota' => 'Kepwako',
            'Ranperda' => 'Ranperda',
            'Surat Edaran Walikota' => 'SE Walikota',
            'Instruksi Walikota' => 'Inwako',
            'Regulation Of The Municipality' => 'RotM',
            'Terjemahan Peraturan Daerah' => 'Transl. Perda'
        ];
        return $mapping[$kategori] ?? '-';
    }
}

if (!function_exists('get_metadata_produk_hukum')) {
    function get_metadata_produk_hukum($peraturan)
    {
        return [
            'Judul Peraturan' => $peraturan['judul'] ?? '-',
            'Nomor' => $peraturan['nomor'] ?? '-',
            'Tahun' => $peraturan['tahun'] ?? '-',
            'Tanggal Penetapan' => format_tanggal($peraturan['tgl_penetapan']),
            'Tanggal Pengundangan' => format_tanggal($peraturan['tgl_pengundangan']),
            'Tempat Penetapan' => $peraturan['tempat_penetapan'] ?? '-',
            'Penandatangan' => $peraturan['penandatangan'] ?? '-',
            'Instansi' => $peraturan['nama_instansi'] ?? '-',
            'Sumber' => $peraturan['sumber'] ?? '-',
            'Status' => $peraturan['nama_status'] ?? '-',
            'Abstrak' => $peraturan['abstrak_teks'] ?? '-',
            'Catatan' => $peraturan['catatan_teks'] ?? '-',
            'Jenis Dokumen' => $peraturan['jenis_peraturan'] ?? '-',
            'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
        ];
    }
}

if (!function_exists('get_metadata_monografi_hukum_json')) {
    function get_metadata_monografi_hukum_json($metadata, $peraturan)
    {
        return [
            'Judul Monografi' => $peraturan['judul'] ?? '-',
            'Penulis/Peneliti' => $metadata['penulis'] ?? $peraturan['penandatangan'] ?? '-',
            'Penerbit' => $metadata['penerbit'] ?? $peraturan['sumber'] ?? '-',
            'ISBN/ISSN' => $metadata['isbn_issn'] ?? $peraturan['nomor'] ?? '-',
            'Jumlah Halaman' => $metadata['halaman'] ?? '-',
            'Tempat Terbit' => $metadata['tempat_terbit'] ?? $peraturan['tempat_penetapan'] ?? '-',
            'Tahun Terbit' => $peraturan['tahun'] ?? '-',
            'Abstrak' => $peraturan['abstrak_teks'] ?? '-',
            'Catatan' => $peraturan['catatan_teks'] ?? '-',
            'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
        ];
    }
}

if (!function_exists('get_metadata_artikel_hukum_json')) {
    function get_metadata_artikel_hukum_json($metadata, $peraturan)
    {
        return [
            'Judul Artikel' => $peraturan['judul'] ?? '-',
            'Penulis' => $metadata['penulis'] ?? $peraturan['penandatangan'] ?? '-',
            'Tanggal Publikasi' => format_tanggal($metadata['tanggal_publikasi'] ?? $peraturan['tgl_penetapan']),
            'Media Publikasi' => $metadata['media_publikasi'] ?? $peraturan['sumber'] ?? '-',
            'Kategori Artikel' => $metadata['kategori_artikel'] ?? $peraturan['jenis_peraturan'] ?? '-',
            'Tahun' => $peraturan['tahun'] ?? '-',
            'Abstrak' => $peraturan['abstrak_teks'] ?? '-',
            'Catatan' => $peraturan['catatan_teks'] ?? '-',
            'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
        ];
    }
}

if (!function_exists('get_metadata_yurisprudensi_json')) {
    function get_metadata_yurisprudensi_json($metadata, $peraturan)
    {
        return [
            'Judul Putusan' => $peraturan['judul'] ?? '-',
            'Nomor Perkara' => $metadata['nomor_perkara'] ?? $peraturan['nomor'] ?? '-',
            'Tanggal Putusan' => format_tanggal($metadata['tanggal_putusan'] ?? $peraturan['tgl_penetapan']),
            'Pengadilan' => $metadata['pengadilan'] ?? $peraturan['tempat_penetapan'] ?? '-',
            'Hakim Ketua' => $metadata['hakim_ketua'] ?? $peraturan['penandatangan'] ?? '-',
            'Jenis Perkara' => $metadata['jenis_perkara'] ?? $peraturan['jenis_peraturan'] ?? '-',
            'Pokok Perkara' => $metadata['pokok_perkara'] ?? $peraturan['abstrak_teks'] ?? '-',
            'Amar Putusan' => $metadata['amar_putusan'] ?? $peraturan['catatan_teks'] ?? '-',
            'Status Putusan' => $metadata['status_putusan'] ?? '-',
            'Tahun' => $peraturan['tahun'] ?? '-',
            'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
        ];
    }
}

if (!function_exists('get_metadata_perjanjian_internasional_json')) {
    function get_metadata_perjanjian_internasional_json($metadata, $peraturan)
    {
        return [
            'Judul Perjanjian' => $peraturan['judul'] ?? '-',
            'Nomor Perjanjian' => $metadata['nomor_perjanjian'] ?? $peraturan['nomor'] ?? '-',
            'Tanggal Penandatanganan' => format_tanggal($metadata['tanggal_penandatanganan'] ?? $peraturan['tgl_penetapan']),
            'Tempat Penandatanganan' => $metadata['tempat_penandatanganan'] ?? $peraturan['tempat_penetapan'] ?? '-',
            'Pihak-pihak' => $metadata['pihak_pihak'] ?? '-',
            'Status Ratifikasi' => $metadata['status_ratifikasi'] ?? '-',
            'Lembaran Negara' => $metadata['lembaran_negara'] ?? '-',
            'Jenis Perjanjian' => $metadata['jenis_perjanjian'] ?? '-',
            'Penandatangan' => $peraturan['penandatangan'] ?? '-',
            'Tahun' => $peraturan['tahun'] ?? '-',
            'Abstrak' => $peraturan['abstrak_teks'] ?? '-',
            'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
        ];
    }
}

if (!function_exists('get_metadata_putusan_mk_json')) {
    function get_metadata_putusan_mk_json($metadata, $peraturan)
    {
        return [
            'Judul Putusan' => $peraturan['judul'] ?? '-',
            'Nomor Perkara' => $metadata['nomor_perkara'] ?? $peraturan['nomor'] ?? '-',
            'Tanggal Putusan' => format_tanggal($metadata['tanggal_putusan'] ?? $peraturan['tgl_penetapan']),
            'Pemohon' => $metadata['pemohon'] ?? '-',
            'Termohon' => $metadata['termohon'] ?? '-',
            'Jenis Pengujian' => $metadata['jenis_pengujian'] ?? '-',
            'Pokok Perkara' => $metadata['pokok_perkara'] ?? $peraturan['abstrak_teks'] ?? '-',
            'Amar Putusan' => $metadata['amar_putusan'] ?? $peraturan['catatan_teks'] ?? '-',
            'Status Putusan' => $metadata['status_putusan'] ?? '-',
            'Tahun' => $peraturan['tahun'] ?? '-',
            'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
        ];
    }
}

if (!function_exists('get_metadata_fallback')) {
    function get_metadata_fallback($peraturan, $kategori)
    {
        // Fallback ke mapping dari field existing jika tidak ada JSON
        switch ($kategori) {
            case 'Monografi Hukum':
                return [
                    'Judul Monografi' => $peraturan['judul'] ?? '-',
                    'Penulis/Peneliti' => $peraturan['penandatangan'] ?? '-',
                    'Penerbit' => $peraturan['sumber'] ?? '-',
                    'ISBN/ISSN' => $peraturan['nomor'] ?? '-',
                    'Tempat Terbit' => $peraturan['tempat_penetapan'] ?? '-',
                    'Tahun Terbit' => $peraturan['tahun'] ?? '-',
                    'Abstrak' => $peraturan['abstrak_teks'] ?? '-',
                    'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
                ];
            case 'Artikel Hukum':
                return [
                    'Judul Artikel' => $peraturan['judul'] ?? '-',
                    'Penulis' => $peraturan['penandatangan'] ?? '-',
                    'Tanggal Publikasi' => format_tanggal($peraturan['tgl_penetapan']),
                    'Media Publikasi' => $peraturan['sumber'] ?? '-',
                    'Kategori Artikel' => $peraturan['jenis_peraturan'] ?? '-',
                    'Tahun' => $peraturan['tahun'] ?? '-',
                    'Abstrak' => $peraturan['abstrak_teks'] ?? '-',
                    'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
                ];
            case 'Yurisprudensi':
                return [
                    'Judul Putusan' => $peraturan['judul'] ?? '-',
                    'Nomor Perkara' => $peraturan['nomor'] ?? '-',
                    'Tanggal Putusan' => format_tanggal($peraturan['tgl_penetapan']),
                    'Pengadilan' => $peraturan['tempat_penetapan'] ?? '-',
                    'Hakim Ketua' => $peraturan['penandatangan'] ?? '-',
                    'Jenis Perkara' => $peraturan['jenis_peraturan'] ?? '-',
                    'Pokok Perkara' => $peraturan['abstrak_teks'] ?? '-',
                    'Amar Putusan' => $peraturan['catatan_teks'] ?? '-',
                    'Tahun' => $peraturan['tahun'] ?? '-',
                    'File Dokumen' => $peraturan['file_dokumen'] ?? '-'
                ];
            default:
                return get_metadata_produk_hukum($peraturan);
        }
    }
}

if (!function_exists('format_tanggal')) {
    function format_tanggal($tanggal)
    {
        if (empty($tanggal) || $tanggal == '0000-00-00') {
            return '-';
        }

        $bulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $parts = explode('-', $tanggal);
        if (count($parts) == 3) {
            $tahun = $parts[0];
            $bulan_num = $parts[1];
            $hari = $parts[2];

            return $hari . ' ' . ($bulan[$bulan_num] ?? $bulan_num) . ' ' . $tahun;
        }

        return $tanggal;
    }
}

if (!function_exists('should_show_metadata_section')) {
    function should_show_metadata_section($peraturan)
    {
        // Tampilkan section jika ada data metadata JSON
        if (!empty($peraturan['metadata_json'])) {
            return true;
        }

        // Tampilkan section jika kategori sesuai
        $kategori = $peraturan['jenis_peraturan'] ?? '';
        if (in_array($kategori, ['Monografi Hukum', 'Artikel Hukum', 'Yurisprudensi', 'Perjanjian Internasional', 'Putusan Mahkamah Konstitusi'])) {
            return true;
        }

        return false;
    }
}

if (!function_exists('get_json_metadata_value')) {
    function get_json_metadata_value($metadata_json, $key, $default = '')
    {
        if (empty($metadata_json)) {
            return $default;
        }

        $metadata = json_decode($metadata_json, true);
        if (!$metadata || !is_array($metadata)) {
            return $default;
        }

        return $metadata[$key] ?? $default;
    }
}

if (!function_exists('set_json_metadata_value')) {
    function set_json_metadata_value($metadata_json, $key, $value)
    {
        $metadata = [];

        if (!empty($metadata_json)) {
            $metadata = json_decode($metadata_json, true);
            if (!is_array($metadata)) {
                $metadata = [];
            }
        }

        $metadata[$key] = $value;
        return json_encode($metadata, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Get metadata labels berdasarkan kategori dokumen
 */
function get_metadata_labels_by_kategori($kategori)
{
    $labels = [
        // Produk Hukum - Peraturan Terjemahan (English)
        'Terjemahan Peraturan Daerah' => [
            'tipe_dokumen' => 'Document Type',
            'jenis_dokumen' => 'Document Category',
            'nomor_dokumen' => 'Document Number',
            'tahun_dokumen' => 'Year',
            'tanggal_penetapan' => 'Date of Enactment',
            'tanggal_pengundangan' => 'Date of Promulgation',
            'tempat_penetapan' => 'Place of Enactment',
            'penandatangan' => 'Signatory',
            'teu' => 'Main Entry Title (T.E.U)',
            'bidang_hukum' => 'Legal Field',
            'sumber' => 'Source',
            'pemrakarsa' => 'Initiator',
            'subjek' => 'Subject',
            'status' => 'Status',
            'lokasi' => 'Location',
            'bahasa' => 'Language',
            'singkatan' => 'Type Abbreviation'
        ],
        'Regulation Of The Municipality' => [
            'tipe_dokumen' => 'Document Type',
            'jenis_dokumen' => 'Document Category',
            'nomor_dokumen' => 'Document Number',
            'tahun_dokumen' => 'Year',
            'tanggal_penetapan' => 'Date of Enactment',
            'tanggal_pengundangan' => 'Date of Promulgation',
            'tempat_penetapan' => 'Place of Enactment',
            'penandatangan' => 'Signatory',
            'teu' => 'Main Entry Title (T.E.U)',
            'bidang_hukum' => 'Legal Field',
            'sumber' => 'Source',
            'pemrakarsa' => 'Initiator',
            'subjek' => 'Subject',
            'status' => 'Status',
            'lokasi' => 'Location',
            'bahasa' => 'Language',
            'singkatan' => 'Type Abbreviation'
        ],
        // Produk Hukum - Peraturan (sesuai database)
        'Peraturan Daerah' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penetapan',
            'tanggal_pengundangan' => 'Tanggal Pengundangan',
            'tempat_penetapan' => 'Tempat Penetapan',
            'penandatangan' => 'Penandatangan',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Peraturan Walikota' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penetapan',
            'tanggal_pengundangan' => 'Tanggal Pengundangan',
            'tempat_penetapan' => 'Tempat Penetapan',
            'penandatangan' => 'Penandatangan',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Keputusan Walikota' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penetapan',
            'tanggal_pengundangan' => 'Tanggal Pengundangan',
            'tempat_penetapan' => 'Tempat Penetapan',
            'penandatangan' => 'Penandatangan',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Ranperda' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penyusunan',
            'tanggal_pengundangan' => 'Tanggal Pengajuan',
            'tempat_penetapan' => 'Tempat Penyusunan',
            'penandatangan' => 'Penyusun',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Jenis Lain (Instruksi, Peraturan Bersama, Staatsblad)' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penetapan',
            'tanggal_pengundangan' => 'Tanggal Pengundangan',
            'tempat_penetapan' => 'Tempat Penetapan',
            'penandatangan' => 'Penandatangan',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        // Produk Hukum - Non-Peraturan
        'Surat Edaran Walikota' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Surat',
            'tanggal_pengundangan' => 'Tanggal Edaran',
            'tempat_penetapan' => 'Tempat Surat',
            'penandatangan' => 'Penandatangan',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Nota Kesepahaman/Kesepakatan' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penandatanganan',
            'tanggal_pengundangan' => 'Tanggal Berlaku',
            'tempat_penetapan' => 'Tempat Penandatanganan',
            'penandatangan' => 'Pihak-pihak',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Dokumen Penugasan (SK Tim, Surat Tugas)' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Penugasan',
            'nomor_dokumen' => 'Nomor SK/Surat',
            'tahun_dokumen' => 'Tahun Penugasan',
            'tanggal_penetapan' => 'Tanggal Penugasan',
            'tanggal_pengundangan' => 'Tanggal Berlaku',
            'tempat_penetapan' => 'Tempat Penugasan',
            'penandatangan' => 'Penandatangan',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Propemperda' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Penyusunan',
            'tanggal_pengundangan' => 'Tanggal Pengesahan',
            'tempat_penetapan' => 'Tempat Penyusunan',
            'penandatangan' => 'Penyusun',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Publikasi Daerah (Lembaran/Berita)' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Publikasi',
            'tanggal_pengundangan' => 'Tanggal Terbit',
            'tempat_penetapan' => 'Tempat Publikasi',
            'penandatangan' => 'Penanggung Jawab',
            'teu' => 'Tajuk Entri Utama (T.E.U)',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        // Monografi Hukum
        'Naskah Akademik & Kajian' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Naskah',
            'nomor_dokumen' => 'Nomor Naskah',
            'tahun_dokumen' => 'Tahun Penyusunan',
            'tanggal_penetapan' => 'Tanggal Penyusunan',
            'tanggal_pengundangan' => 'Tanggal Selesai',
            'tempat_penetapan' => 'Tempat Penyusunan',
            'penandatangan' => 'Penyusun/Peneliti',
            'teu' => 'Institusi',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Karya Ilmiah & Buku Hukum' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Karya',
            'nomor_dokumen' => 'Nomor Katalog',
            'tahun_dokumen' => 'Tahun Terbit',
            'tanggal_penetapan' => 'Tanggal Terbit',
            'tanggal_pengundangan' => 'Tanggal Publikasi',
            'tempat_penetapan' => 'Tempat Terbit',
            'penandatangan' => 'Penulis',
            'teu' => 'Penerbit',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Institusi',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        // Artikel Hukum
        'Opini dan Gagasan Hukum' => [
            'tipe_dokumen' => 'Tipe Artikel',
            'jenis_dokumen' => 'Jenis Artikel',
            'nomor_dokumen' => 'Nomor Artikel',
            'tahun_dokumen' => 'Tahun Publikasi',
            'tanggal_penetapan' => 'Tanggal Publikasi',
            'tanggal_pengundangan' => 'Tanggal Terbit',
            'tempat_penetapan' => 'Media Publikasi',
            'penandatangan' => 'Penulis',
            'teu' => 'Jurnal/Majalah',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Institusi',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Analisis dan Evaluasi Regulasi Daerah' => [
            'tipe_dokumen' => 'Tipe Analisis',
            'jenis_dokumen' => 'Jenis Analisis',
            'nomor_dokumen' => 'Nomor Analisis',
            'tahun_dokumen' => 'Tahun Analisis',
            'tanggal_penetapan' => 'Tanggal Analisis',
            'tanggal_pengundangan' => 'Tanggal Selesai',
            'tempat_penetapan' => 'Tempat Analisis',
            'penandatangan' => 'Analis',
            'teu' => 'Institusi',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemrakarsa',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Edukasi dan Literasi Hukum untuk Publik' => [
            'tipe_dokumen' => 'Tipe Artikel',
            'jenis_dokumen' => 'Jenis Artikel',
            'nomor_dokumen' => 'Nomor Artikel',
            'tahun_dokumen' => 'Tahun Publikasi',
            'tanggal_penetapan' => 'Tanggal Publikasi',
            'tanggal_pengundangan' => 'Tanggal Terbit',
            'tempat_penetapan' => 'Media Publikasi',
            'penandatangan' => 'Penulis',
            'teu' => 'Jurnal/Majalah',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Institusi',
            'subjek' => 'Subjek',
            'status' => 'Status',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        // Putusan/Yurisprudensi
        'Putusan Mahkamah Agung (MA)' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Putusan',
            'tanggal_pengundangan' => 'Tanggal Registrasi',
            'tempat_penetapan' => 'Pengadilan',
            'penandatangan' => 'Hakim Ketua',
            'teu' => 'Majelis Hakim',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemohon',
            'subjek' => 'Pokok Perkara',
            'status' => 'Status Putusan',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ],
        'Putusan Mahkamah Konstitusi (MK)' => [
            'tipe_dokumen' => 'Tipe Dokumen',
            'jenis_dokumen' => 'Jenis Dokumen',
            'nomor_dokumen' => 'Nomor Dokumen',
            'tahun_dokumen' => 'Tahun Dokumen',
            'tanggal_penetapan' => 'Tanggal Putusan',
            'tanggal_pengundangan' => 'Tanggal Registrasi',
            'tempat_penetapan' => 'Pengadilan',
            'penandatangan' => 'Hakim Ketua',
            'teu' => 'Majelis Hakim',
            'bidang_hukum' => 'Bidang Hukum',
            'sumber' => 'Sumber',
            'pemrakarsa' => 'Pemohon',
            'subjek' => 'Pokok Perkara',
            'status' => 'Status Putusan',
            'lokasi' => 'Lokasi',
            'bahasa' => 'Bahasa',
            'singkatan' => 'Singkatan Jenis'
        ]
    ];

    // Default labels untuk kategori yang tidak terdaftar
    $default_labels = [
        'tipe_dokumen' => 'Tipe Dokumen',
        'jenis_dokumen' => 'Jenis Dokumen',
        'nomor_dokumen' => 'Nomor Dokumen',
        'tahun_dokumen' => 'Tahun Dokumen',
        'tanggal_penetapan' => 'Tanggal Penetapan',
        'tanggal_pengundangan' => 'Tanggal Pengundangan',
        'tempat_penetapan' => 'Tempat Penetapan',
        'penandatangan' => 'Penandatangan',
        'teu' => 'Tajuk Entri Utama (T.E.U)',
        'bidang_hukum' => 'Bidang Hukum',
        'sumber' => 'Sumber',
        'pemrakarsa' => 'Pemrakarsa',
        'subjek' => 'Subjek',
        'status' => 'Status',
        'lokasi' => 'Lokasi',
        'bahasa' => 'Bahasa',
        'singkatan' => 'Singkatan Jenis'
    ];

    return $labels[$kategori] ?? $default_labels;
}

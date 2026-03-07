/**
* Membatasi panjang teks dengan menambahkan ellipsis
* @param string $text Teks yang akan dibatasi
* @param int $maxLength Panjang maksimal karakter
* @param string $ellipsis Karakter ellipsis (default: ...)
* @return string Teks yang sudah dibatasi
*/
function truncate_text($text, $maxLength = 20, $ellipsis = '...')
{
if (strlen($text) <= $maxLength) {
    return $text;
    }

    return substr($text, 0, $maxLength - strlen($ellipsis)) . $ellipsis;
    }

    /**
    * Membatasi nama user untuk tampilan UI
    * @param string $nama Nama user
    * @param int $maxLength Panjang maksimal (default: 15 untuk button, 25 untuk dropdown)
    * @return string Nama yang sudah dibatasi
    */
    function truncate_user_name($nama, $maxLength=15)
    {
    if (empty($nama)) {
    return 'Admin' ;
    }

    return truncate_text($nama, $maxLength);
    }

    /**
    * Menampilkan nama user dengan tooltip jika terpotong
    * @param string $nama Nama user asli
    * @param int $maxLength Panjang maksimal
    * @param string $tooltipClass Class CSS untuk tooltip
    * @return string HTML dengan tooltip jika diperlukan
    */
    function display_user_name($nama, $maxLength=15, $tooltipClass='user-name-tooltip' )
    {
    if (empty($nama)) {
    return 'Admin' ;
    }

    $truncated=truncate_text($nama, $maxLength);

    // Jika nama terpotong, tambahkan tooltip
    if (strlen($nama)> $maxLength) {
    return '<span class="' . $tooltipClass . '" title="' . esc($nama) . '">' . esc($truncated) . '</span>';
    }

    return esc($truncated);
    }
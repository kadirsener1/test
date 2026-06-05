<?php
header('Content-Type: application/json');

// M3U listenizin tam URL'si (buraya kendi listenizin adresini yazın)
$playlistUrl = 'https://raw.githubusercontent.com/kadirsener1/avva/refs/heads/main/playlist.m3u'; // M3U listenizin adresi

// Kanal adını URL parametresinden al (örn: proxy.php?kanal=TABİİ SPOR)
$channelName = $_GET['kanal'] ?? '';

if (empty($channelName)) {
    die(json_encode(['hata' => 'Kanal adı belirtilmedi. Örnek: proxy.php?kanal=TABİİ SPOR']));
}

// M3U listesini indir
$m3uContent = @file_get_contents($playlistUrl);
if ($m3uContent === false) {
    die(json_encode(['hata' => 'M3U listesi indirilemedi. URL\'yi kontrol edin.']));
}

// M3U satırlarını analiz et
$lines = explode("\n", $m3uContent);
$streamUrl = null;
$currentName = '';

foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '#EXTINF:') === 0) {
        // Virgülden sonrasını kanal adı olarak al
        $commaPos = strrpos($line, ',');
        if ($commaPos !== false) {
            $currentName = trim(substr($line, $commaPos + 1));
        } else {
            $currentName = '';
        }
    } elseif (!empty($currentName) && !empty($line) && $line[0] !== '#') {
        // Bu satır yayın URL'sidir
        if ($currentName === $channelName) {
            $streamUrl = $line;
            break;
        }
        $currentName = '';
    }
}

// Sonucu JSON olarak döndür
if ($streamUrl) {
    echo json_encode(['url' => $streamUrl]);
} else {
    echo json_encode(['hata' => "Kanal bulunamadı: $channelName"]);
}
?>

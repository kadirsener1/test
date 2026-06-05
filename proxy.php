<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// M3U listenizin adresi (sabittir)
$playlistUrl = 'https://raw.githubusercontent.com/kadirsener1/avva/refs/heads/main/playlist.m3u';

// Kanal adını URL'den al (örnek: proxy.php?kanal=TABİİ SPOR)
$channelName = $_GET['kanal'] ?? 'beIN Sports 2-R';

if (empty($channelName)) {
    echo json_encode(['error' => 'Kanal adı belirtilmedi. Örnek: ?kanal=TABİİ SPOR']);
    exit;
}

// 1. M3U listesini cURL ile indir (daha güvenli)
$ch = curl_init($playlistUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$m3uContent = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($m3uContent)) {
    echo json_encode(['error' => "M3U listesi indirilemedi. HTTP kodu: $httpCode"]);
    exit;
}

// 2. M3U satırlarını analiz et
$lines = explode("\n", $m3uContent);
$streamUrl = null;

for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);
    
    // #EXTINF satırı içinde kanal adını ara (virgülden sonrası veya herhangi bir yerde)
    if (strpos($line, '#EXTINF:') === 0) {
        // Virgülden sonrasını al
        $commaPos = strrpos($line, ',');
        if ($commaPos !== false) {
            $nameInList = trim(substr($line, $commaPos + 1));
        } else {
            $nameInList = '';
        }
        
        // Kanat adı eşleşiyor mu? (birebir)
        if ($nameInList === $channelName) {
            // Bir sonraki satır yayın URL'sidir
            $nextLine = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : '';
            if (!empty($nextLine) && $nextLine[0] !== '#') {
                $streamUrl = $nextLine;
                break;
            }
        }
    }
}

if ($streamUrl) {
    echo json_encode(['url' => $streamUrl]);
} else {
    echo json_encode(['error' => "Kanal bulunamadı: $channelName"]);
}
?>

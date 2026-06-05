// proxy.php
<?php
header('Content-Type: application/json');

$playlistUrl = 'https://ornek-site.com/kanallar.m3u'; // M3U listenizin adresi
$channelName = $_GET['kanal'] ?? '';               // Kanal adını URL'den al

if (empty($channelName)) {
    die(json_encode(['hata' => 'Kanal adı belirtilmedi.']));
}

// 1. M3U listesini indir
$m3uContent = @file_get_contents($playlistUrl);
if ($m3uContent === false) {
    die(json_encode(['hata' => 'M3U listesi indirilemedi.']));
}

// 2. M3U içeriğini satır satır analiz et
$lines = explode("\n", $m3uContent);
$streamUrl = null;
$currentName = '';

foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '#EXTINF:') === 0) {
        // Kanal ismini ayıkla
        preg_match('/,(.*)$/', $line, $matches);
        $currentName = trim($matches[1] ?? '');
    } elseif (!empty($currentName) && !empty($line) && $line[0] !== '#') {
        // Bu satır, bir önceki #EXTINF satırındaki kanalın URL'idir.
        if ($currentName === $channelName) {
            $streamUrl = $line;
            break;
        }
        $currentName = '';
    }
}

// 3. Bulunan URL'i JSON formatında döndür
if ($streamUrl) {
    echo json_encode(['url' => $streamUrl]);
} else {
    echo json_encode(['hata' => "Kanal bulunamadı: $channelName"]);
}
?>

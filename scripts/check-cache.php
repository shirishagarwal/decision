<?php
// Check what's in the cached files

$cacheDir = __DIR__ . '/../../data/external_decisions';

echo "📂 Checking cache directory: $cacheDir\n\n";

if (!is_dir($cacheDir)) {
    echo "❌ Cache directory doesn't exist!\n";
    echo "Creating it...\n";
    mkdir($cacheDir, 0755, true);
    exit;
}

$files = scandir($cacheDir);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $filepath = $cacheDir . '/' . $file;
    $size = filesize($filepath);
    $age = time() - filemtime($filepath);
    $ageHours = round($age / 3600, 1);
    
    echo "📄 $file\n";
    echo "   Size: $size bytes\n";
    echo "   Age: $ageHours hours\n";
    
    $content = file_get_contents($filepath);
    $data = json_decode($content, true);
    
    if ($data) {
        echo "   Records: " . count($data) . "\n";
        if (count($data) > 0) {
            echo "   Sample: " . print_r($data[0], true) . "\n";
        }
    } else {
        echo "   ⚠️  Invalid JSON or empty\n";
        echo "   Content: " . substr($content, 0, 200) . "\n";
    }
    
    echo "\n";
}

echo "\n🗑️  To clear cache and re-scrape:\n";
echo "   rm -rf $cacheDir/*.json\n";
echo "   php " . __DIR__ . "/DataIngestionPipeline.php\n";
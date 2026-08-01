<?php

$folders = [
    __DIR__ . '/bootstrap/cache',
    __DIR__ . '/storage/framework/cache/data',
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/storage/framework/sessions',
];

$deleted = [];

foreach ($folders as $folder) {
    if (!is_dir($folder)) continue;

    $files = glob($folder . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $deleted[] = $file;
        }
    }
}

echo "✔ Archivos de caché eliminados: <br>";
echo "<ul>";
foreach ($deleted as $d) {
    echo "<li>" . basename($d) . "</li>";
}
echo "</ul>";

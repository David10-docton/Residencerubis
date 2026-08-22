<?php
$file = 'admin/index.php';
$lines = file($file, FILE_IGNORE_NEW_LINES);

$startIdx = null;
$endIdx = null;
for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], "elseif ($tab === 'requests')") !== false) {
        $startIdx = $i;
    }
    if ($startIdx !== null && $startIdx < $i && strpos($lines[$i], "elseif ($tab === 'blog')") !== false) {
        $endIdx = $i;
        break;
    }
}

if ($startIdx === null || $endIdx === null) {
    echo "Section non trouvée\n";
    exit(1);
}

echo "Section: lignes " . ($startIdx+1) . " a " . ($endIdx+1) . "\n";

// Read the new section from a separate file
$newHtml = file_get_contents('new_requests_section.php');
$before = array_slice($lines, 0, $startIdx);
$after = array_slice($lines, $endIdx);
$newContent = array_merge($before, explode("\n", $newHtml), $after);

file_put_contents($file, implode("\n", $newContent));
echo "HTML remplace: " . count($newContent) . " lignes\n";

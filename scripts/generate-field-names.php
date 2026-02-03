<?php

/**
 * Helper script to generate field names for fillable PDF creation
 * 
 * Usage: php scripts/generate-field-names.php
 * 
 * This will output all field names you need to create in your fillable PDF
 */

$pages = 10; // Number of pages (8 rows per page)
$rowsPerPage = 8;

echo "=== PDF Form Field Names ===\n\n";

echo "HEADER FIELDS (same on all pages):\n";
echo "- title\n";
echo "- date\n";
echo "- venue\n\n";

echo "ATTENDEE ROW FIELDS:\n";
echo "Format: row{number}_{field}\n\n";

$rowNumber = 1;
for ($page = 1; $page <= $pages; $page++) {
    echo "PAGE {$page} (Rows " . ($rowNumber) . " to " . ($rowNumber + $rowsPerPage - 1) . "):\n";
    
    for ($row = 0; $row < $rowsPerPage; $row++) {
        $currentRow = $rowNumber + $row;
        echo "  Row {$currentRow}:\n";
        echo "    - row{$currentRow}_no\n";
        echo "    - row{$currentRow}_name\n";
        echo "    - row{$currentRow}_signature\n";
        echo "    - row{$currentRow}_mobile\n";
        echo "    - row{$currentRow}_email\n";
        echo "    - row{$currentRow}_prc\n";
        echo "    - row{$currentRow}_expiry\n";
    }
    
    $rowNumber += $rowsPerPage;
    echo "\n";
}

echo "=== TOTAL FIELDS ===\n";
$totalFields = 3 + ($pages * $rowsPerPage * 7); // 3 header + (pages * rows * 7 fields per row)
echo "Header fields: 3\n";
echo "Row fields per page: " . ($rowsPerPage * 7) . "\n";
echo "Total pages: {$pages}\n";
echo "Total row fields: " . ($pages * $rowsPerPage * 7) . "\n";
echo "TOTAL FIELDS: {$totalFields}\n\n";

echo "=== COPY-PASTE FIELD LIST ===\n";
echo "Use this list when creating your fillable PDF:\n\n";

// Header
echo "title\ndate\nvenue\n";

// All row fields
$rowNumber = 1;
for ($page = 1; $page <= $pages; $page++) {
    for ($row = 0; $row < $rowsPerPage; $row++) {
        $currentRow = $rowNumber + $row;
        echo "row{$currentRow}_no\n";
        echo "row{$currentRow}_name\n";
        echo "row{$currentRow}_signature\n";
        echo "row{$currentRow}_mobile\n";
        echo "row{$currentRow}_email\n";
        echo "row{$currentRow}_prc\n";
        echo "row{$currentRow}_expiry\n";
    }
    $rowNumber += $rowsPerPage;
}

echo "\n=== DONE ===\n";

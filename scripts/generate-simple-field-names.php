<?php

/**
 * Generate simple field names based on data fields and row numbers
 * 
 * Usage: php scripts/generate-simple-field-names.php
 */

$pages = 10; // Number of pages
$rowsPerPage = 8;

echo "=== SIMPLE FIELD NAMING ===\n\n";
echo "Pattern: {datafield}_{rownumber}\n\n";

echo "HEADER FIELDS (same on all pages):\n";
echo "- title\n";
echo "- date\n";
echo "- venue\n\n";

echo "ROW FIELDS:\n";
echo "For each row, create 7 fields:\n";
echo "  - no_{number}       (row number)\n";
echo "  - name_{number}     (full name)\n";
echo "  - signature_{number} (signature image)\n";
echo "  - mobile_{number}   (mobile phone)\n";
echo "  - email_{number}    (email address)\n";
echo "  - prc_{number}      (PRC license)\n";
echo "  - expiry_{number}   (PRC expiry date)\n\n";

echo "=== FIELD LIST BY PAGE ===\n\n";

$rowNumber = 1;
for ($page = 1; $page <= $pages; $page++) {
    echo "PAGE {$page} (Rows {$rowNumber} to " . ($rowNumber + $rowsPerPage - 1) . "):\n";
    
    for ($row = 0; $row < $rowsPerPage; $row++) {
        $currentRow = $rowNumber + $row;
        echo "  Row {$currentRow}:\n";
        echo "    no_{$currentRow}\n";
        echo "    name_{$currentRow}\n";
        echo "    signature_{$currentRow}\n";
        echo "    mobile_{$currentRow}\n";
        echo "    email_{$currentRow}\n";
        echo "    prc_{$currentRow}\n";
        echo "    expiry_{$currentRow}\n";
    }
    
    $rowNumber += $rowsPerPage;
    echo "\n";
}

echo "=== COPY-PASTE LIST FOR PDF CREATION ===\n\n";
echo "Header fields:\n";
echo "title\ndate\nvenue\n\n";

echo "Row fields (for all " . ($pages * $rowsPerPage) . " rows):\n";
$rowNumber = 1;
for ($page = 1; $page <= $pages; $page++) {
    for ($row = 0; $row < $rowsPerPage; $row++) {
        $currentRow = $rowNumber + $row;
        echo "no_{$currentRow}\n";
        echo "name_{$currentRow}\n";
        echo "signature_{$currentRow}\n";
        echo "mobile_{$currentRow}\n";
        echo "email_{$currentRow}\n";
        echo "prc_{$currentRow}\n";
        echo "expiry_{$currentRow}\n";
    }
    $rowNumber += $rowsPerPage;
}

echo "\n=== DONE ===\n";
echo "Total fields: " . (3 + ($pages * $rowsPerPage * 7)) . "\n";

<?php

try {
    $db = new PDO('sqlite:../database.db');
    
    echo "--- SQLite Database Inspection ---\n";
    
    // Get all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables found: " . implode(', ', $tables) . "\n\n";
    
    foreach ($tables as $table) {
        echo "Structure of table [$table]:\n";
        $columns = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo " - {$col['name']} ({$col['type']})\n";
        }
        
        echo "\nSample data (1 row):\n";
        $sample = $db->query("SELECT * FROM $table LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        print_r($sample);
        echo "-----------------------------------\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

<?php
// ==========================
// Delete tables from 3 days back
// ==========================
function deleteOldTables($mysqli, $days_back = 3) {
    $target_date = date('Ymd', strtotime("-$days_back days"));
    $search_pattern = "spotify_charts_{$target_date}_";
    
    writeLog("Looking for tables to delete from $days_back days ago (date: $target_date)", 'INFO');
    
    // Find all tables matching the pattern
    $find_sql = "SELECT table_name FROM information_schema.tables 
                 WHERE table_schema = DATABASE() 
                 AND table_name LIKE '{$search_pattern}%' 
                 ORDER BY table_name";
    
    $result = $mysqli->query($find_sql);
    
    if (!$result) {
        writeLog("Error finding tables: " . $mysqli->error, 'ERROR');
        return false;
    }
    
    if ($result->num_rows == 0) {
        writeLog("No tables found matching pattern: {$search_pattern}%", 'INFO');
        return true;
    }
    
    $tables_to_delete = [];
    while ($row = $result->fetch_assoc()) {
        $tables_to_delete[] = $row['table_name'];
    }
    
    writeLog("Found " . count($tables_to_delete) . " table(s) to delete:", 'INFO');
    foreach ($tables_to_delete as $table) {
        writeLog("- $table", 'INFO');
    }
    
    // Delete each table
    $deleted_count = 0;
    $failed_count = 0;
    
    foreach ($tables_to_delete as $table) {
        $drop_sql = "DROP TABLE IF EXISTS `$table`";
        
        if ($mysqli->query($drop_sql)) {
            writeLog("✓ Successfully deleted table: $table", 'INFO');
            $deleted_count++;
        } else {
            writeLog("✗ Failed to delete table $table: " . $mysqli->error, 'ERROR');
            $failed_count++;
        }
    }
    
    writeLog("Cleanup summary: $deleted_count deleted, $failed_count failed", 'INFO');
    
    return $failed_count == 0;
}

// ==========================
// Alternative: Delete tables older than X days
// ==========================
function deleteTablesOlderThan($mysqli, $days_to_keep = 7) {
    writeLog("Looking for spotify_charts tables older than $days_to_keep days", 'INFO');
    
    // Find all spotify_charts tables
    $find_sql = "SELECT table_name FROM information_schema.tables 
                 WHERE table_schema = DATABASE() 
                 AND table_name LIKE 'spotify_charts_%' 
                 ORDER BY table_name";
    
    $result = $mysqli->query($find_sql);
    
    if (!$result) {
        writeLog("Error finding tables: " . $mysqli->error, 'ERROR');
        return false;
    }
    
    if ($result->num_rows == 0) {
        writeLog("No spotify_charts tables found", 'INFO');
        return true;
    }
    
    $cutoff_date = date('Ymd', strtotime("-$days_to_keep days"));
    $tables_to_delete = [];
    
    while ($row = $result->fetch_assoc()) {
        $table_name = $row['table_name'];
        
        // Extract date from table name (assuming format: spotify_charts_YYYYMMDD_HHMMSS)
        if (preg_match('/spotify_charts_(\d{8})_/', $table_name, $matches)) {
            $table_date = $matches[1];
            
            if ($table_date < $cutoff_date) {
                $tables_to_delete[] = $table_name;
            }
        }
    }
    
    if (empty($tables_to_delete)) {
        writeLog("No old tables found to delete (keeping tables from $cutoff_date onwards)", 'INFO');
        return true;
    }
    
    writeLog("Found " . count($tables_to_delete) . " old table(s) to delete:", 'INFO');
    foreach ($tables_to_delete as $table) {
        writeLog("- $table", 'INFO');
    }
    
    // Delete each table
    $deleted_count = 0;
    $failed_count = 0;
    
    foreach ($tables_to_delete as $table) {
        $drop_sql = "DROP TABLE IF EXISTS `$table`";
        
        if ($mysqli->query($drop_sql)) {
            writeLog("✓ Successfully deleted old table: $table", 'INFO');
            $deleted_count++;
        } else {
            writeLog("✗ Failed to delete table $table: " . $mysqli->error, 'ERROR');
            $failed_count++;
        }
    }
    
    writeLog("Cleanup summary: $deleted_count deleted, $failed_count failed", 'INFO');
    
    return $failed_count == 0;
}

// ==========================
// Usage Examples
// ==========================

// Example 1: Delete tables from exactly 3 days ago
// deleteOldTables($mysqli, 3);

// Example 2: Delete tables from exactly 7 days ago  
// deleteOldTables($mysqli, 7);

// Example 3: Delete all tables older than 7 days (keep only last 7 days)
// deleteTablesOlderThan($mysqli, 7);

// Example 4: Delete all tables older than 3 days (keep only last 3 days)
// deleteTablesOlderThan($mysqli, 3);

?>
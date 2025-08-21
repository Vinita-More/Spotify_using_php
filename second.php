<?php
include './config.php';
$start_time = microtime(true);

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Dates
$yesterday = 'spotify_charts_20250820_130735';
$today = 'spotify_charts_20250821_092319';

// ===============================
// 1. Fetch yesterday's data
// ===============================
$old_data = [];
$sql_old = "SELECT id, showId, countryCode, category, chart_rank FROM `$yesterday`";
$result_old = $conn->query($sql_old);

if ($result_old) {
    while ($row = $result_old->fetch_assoc()) {
        $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
        $old_data[$key] = (int)$row['chart_rank'];
    }
}

echo "📊 Loaded " . count($old_data) . " records from yesterday\n";

// ===============================
// 2. Fetch today's data & compare
// ===============================
$sql_today = "SELECT id, showId, countryCode, category, chart_rank FROM `$today`";
$result_today = $conn->query($sql_today);

$existing_keys_today = [];
$dropped_out_count = 0;
$processed_count = 0;
$new_entries = 0;
$movements_calculated = 0;

// Prepare update statement ONCE (faster than inside loop)
$update_sql = "UPDATE `$today` SET movement = ? WHERE id = ?";
$stmt = $conn->prepare($update_sql);

if ($stmt && $result_today) {
    while ($row = $result_today->fetch_assoc()) {
        $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
        
        $today_rank = (int)$row['chart_rank'];
        $yesterday_rank = isset($old_data[$key]) ? $old_data[$key] : null;
        
        // Calculate movement - be explicit about NULL handling
        if ($yesterday_rank !== null) {
            $movement = $yesterday_rank - $today_rank;
            $movements_calculated++;
            
            // Debug output for first few calculations
            if ($movements_calculated <= 5) {
                echo "🔍 Debug: Key=$key, Yesterday=$yesterday_rank, Today=$today_rank, Movement=$movement\n";
            }
        } else {
            $movement = null;
            $new_entries++;
        }
        
        // Determine effect (for your reference, not stored)
        if ($yesterday_rank === null) {
            $effect = "new";
        } elseif ($today_rank < $yesterday_rank) {
            $effect = "up";
        } elseif ($today_rank > $yesterday_rank) {
            $effect = "down";
        } else {
            $effect = "unchanged";
        }
        
        // Update DB - Handle NULL explicitly
        if ($movement === null) {
            // For NULL values, we need to use a different approach
            $stmt->bind_param("si", $null_movement, $row['id']);
            $null_movement = null;
        } else {
            $stmt->bind_param("ii", $movement, $row['id']);
        }
        
        if (!$stmt->execute()) {
            echo "❌ Error updating record ID {$row['id']}: " . $stmt->error . "\n";
        }
        
        $existing_keys_today[$key] = true;
        $processed_count++;
    }
}

// ===============================
// 3. Dropped out items
// ===============================
foreach ($old_data as $key => $yesterday_rank) {
    if (!isset($existing_keys_today[$key])) {
        $dropped_out_count++;
        echo "📉 Dropped out: $key (was rank $yesterday_rank)\n";
    }
}

// ===============================
// 4. Verification Query (Optional)
// ===============================
echo "\n🔍 Sample verification - checking first 5 records:\n";
$verify_sql = "SELECT id, showId, countryCode, category, chart_rank, movement FROM `$today` LIMIT 5";
$verify_result = $conn->query($verify_sql);
if ($verify_result) {
    while ($row = $verify_result->fetch_assoc()) {
        $movement_display = $row['movement'] === null ? 'NULL' : $row['movement'];
        echo "ID: {$row['id']}, Rank: {$row['chart_rank']}, Movement: $movement_display\n";
    }
}

// ===============================
// 5. Finish up
// ===============================
if ($stmt) $stmt->close();
$conn->close();

$end_time = microtime(true);
$elapsed = $end_time - $start_time;

echo "\n✅ Rank comparison & diff update complete!\n";
echo "⏱ Time taken: " . round($elapsed, 2) . "s\n";
echo "📊 Processed: $processed_count records\n";
echo "📈 New entries: $new_entries\n";
echo "🔢 Movements calculated: $movements_calculated\n";
echo "📉 Dropped out: $dropped_out_count\n";
?>
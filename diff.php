<?php
$start_time = microtime(true);

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "podcasts";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$yesterday = date('Y-m-d', strtotime('-1 day'));
$today = date('Y-m-d');

// Fetch yesterday's data
$old_data = [];
$sql_old = "SELECT * FROM podcasts WHERE DATE(created_at) = '$yesterday'";
$result_old = $conn->query($sql_old);

if ($result_old) {
    while ($row = $result_old->fetch_assoc()) {
        // Create unique key based on available columns
        $key = ($row['category'] === 'top_episodes')
            ? $row['countryCode'] . "|" . $row['category'] . "|" . $row['id'] . "|" . $row['episodeId']
            : $row['countryCode'] . "|" . $row['category'] . "|" . $row['id'];
        $old_data[$key] = (int)$row['chart_rank'];
    }
}

// Fetch today's data
$sql_today = "SELECT * FROM podcasts WHERE DATE(created_at) = '$today'";
$result_today = $conn->query($sql_today);

$existing_keys_today = [];
$max_rank_today = 0;

if ($result_today) {
    while ($row = $result_today->fetch_assoc()) {
        // Create unique key
        $key = ($row['category'] === 'top_episodes')
            ? $row['countryCode'] . "|" . $row['category'] . "|" . $row['id'] . "|" . $row['episodeId']
            : $row['countryCode'] . "|" . $row['category'] . "|" . $row['id'];
        
        $today_rank = (int)$row['chart_rank'];
        if ($today_rank > $max_rank_today) $max_rank_today = $today_rank;
        
        $yesterday_rank = $old_data[$key] ?? null;
        
        // Determine movement
        if ($yesterday_rank === null) {
            $movement = "NEW";
        } else {
            $diff = $yesterday_rank - $today_rank;
            if ($diff > 0) $movement = "+$diff";
            elseif ($diff < 0) $movement = (string)$diff;
            else $movement = "0";
        }
        
        // Update the diff column in the database
        $update_sql = "UPDATE podcasts SET diff = ? WHERE id = ? AND DATE(created_at) = ?";
        $stmt = $conn->prepare($update_sql);
        
        if ($stmt) {
            $stmt->bind_param("sis", $movement, $row['id'], $today);
            $stmt->execute();
            $stmt->close();
        }
        
        $existing_keys_today[$key] = true;
    }
}

// Handle dropped out items (items that were in yesterday's data but not in today's)
$dropped_out_count = 0;
foreach ($old_data as $key => $yesterday_rank) {
    if (!isset($existing_keys_today[$key])) {
        $dropped_out_count++;
        // Note: Since dropped items don't exist in today's data, 
        // we can't update them in the current table structure
        // You might want to log these or handle them differently
        echo "Dropped out: $key (was rank $yesterday_rank)\n";
    }
}

$conn->close();
$end_time = microtime(true);
$elapsed = $end_time - $start_time;
echo "✅ Rank comparison and update done! Time taken: " . round($elapsed, 2) . " seconds\n";
echo "📊 Processed records from today's data\n";
if ($dropped_out_count > 0) {
    echo "📉 Found $dropped_out_count items that dropped out of rankings\n";
}

//include "./config.php"; -->

// // Connect
// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
// if ($conn->connect_error) {
//     die("❌ Connection failed: " . $conn->connect_error);
// }

// Drop old table if exists
// $drop_sql = "DROP TABLE IF EXISTS todays_rank";
// if ($conn->query($drop_sql) === TRUE) {
//     echo "✅ Old todays_rank table removed.<br>";
// } else {
//     echo "⚠️ Error dropping table: " . $conn->error . "<br>";
// }

// Create new table
// $create_sql = "CREATE TABLE todays_rank (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     showId VARCHAR(255),
//     showName VARCHAR(255),
//     countryName VARCHAR(255),
//     countryCode VARCHAR(50),
//     category VARCHAR(255),
//     episodeId VARCHAR(255) DEFAULT NULL,
//     today_rank INT DEFAULT NULL,
//     yesterday_rank INT DEFAULT NULL,
//     rank_diff INT,
//     movement VARCHAR(50) DEFAULT NULL
// )";
// if ($conn->query($create_sql) === TRUE) {
//     echo "✅ New todays_rank table created.<br>";
// } 
// else {
//     die("❌ Error creating table: " . $conn->error);
// }

// Insert rank diffs
// $insert_sql = 
// "
// INSERT INTO todays_rank ( showId, showName,countryName, countryCode,category, episodeId, today_rank, yesterday_rank, rank_diff)
// SELECT 
    
//     t.showId,
//     t.showName,
//     t.countryName,
//     t.countryCode,
//     t.category,
//     t.episodeId,
//     t.chart_rank AS today_rank,
//     y.chart_rank AS yesterday_rank,
//     CASE
//         WHEN y.showId IS NULL THEN 'NEW'
//         WHEN (y.chart_rank - t.chart_rank) > 0 THEN CONCAT('+', (y.chart_rank - t.chart_rank))
//         WHEN (y.chart_rank - t.chart_rank) < 0 THEN (y.chart_rank - t.chart_rank)
//         ELSE '0'
//     END AS movement
// FROM `14-08-with-top-episodes` t
// LEFT JOIN `13-08-with-top-episodes` y
//     ON t.countryCode = y.countryCode
//    AND t.category = y.category
//    AND t.showId = y.showId
//    AND ( (t.category = 'top_episodes' AND t.episodeId = y.episodeId)
//          OR (t.category <> 'top_episodes') )"
//          ;

// if ($conn->query($insert_sql) === TRUE) {
//     echo "✅ Data inserted into todays_rank.<br>";
// } else {
//     echo "❌ Error inserting data: " . $conn->error;
// }

// $update = "
// UPDATE todays_rank
// SET movement = CASE
//     WHEN rank_diff > 0 THEN 'Up'
//     WHEN rank_diff < 0 THEN 'Down'
//     WHEN rank_diff = 0 THEN 'Same'
//     ELSE 'New'
// END";
// if (!$conn->query($update)) {
//     die("Movement update failed: " . $conn->error);
// }
// echo "✅ Movement updated successfully.<br>";
// $conn->close();
// ?>

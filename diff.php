<?php

    include './config.php';

    $start_time = microtime(true);

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    // Dates
    $yesterday = 'spotify_20250818_070140';
    $today     = 'spotify_20250819_054128';

    // ===============================
    // 1. Fetch yesterday’s data
    // ===============================
    $old_data = [];
    $sql_old = "SELECT id, showId,countryCode, category, episodeId, chart_rank 
                FROM `$yesterday`";
    $result_old = $conn->query($sql_old);

    if ($result_old) {
        while ($row = $result_old->fetch_assoc()) {
            $key = ($row['category'] === 'top_episodes')
                ? $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'] . "|" . $row['episodeId']
                : $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
            $old_data[$key] = (int)$row['chart_rank'];
        }
    }

    // ===============================
    // 2. Fetch today’s data & compare
    // ===============================
    $sql_today = "SELECT id, showId, countryCode, category, episodeId, chart_rank 
                FROM `$today`";
    $result_today = $conn->query($sql_today);

    $existing_keys_today = [];
    $dropped_out_count = 0;

    // Prepare update statement ONCE (faster than inside loop)
    $update_sql = "UPDATE `$today` SET diff = ? , movement = ?  WHERE id = ? ";
    $stmt = $conn->prepare($update_sql);

    if ($stmt && $result_today) {
        while ($row = $result_today->fetch_assoc()) {
            $key = ($row['category'] === 'top_episodes')
                ? $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'] . "|" . $row['episodeId']
                : $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];

            $today_rank = (int)$row['chart_rank'];
            $yesterday_rank = $old_data[$key] ?? null;
            $diff = $yesterday_rank !== null ? $yesterday_rank - $today_rank : null;


            if ($yesterday_rank === null) {
                $movement = "new"; // not present yesterday
            } elseif ($today_rank < $yesterday_rank) {
                $movement = "up"; // rank improved
            } elseif ($today_rank > $yesterday_rank) {
                $movement = "down"; // rank dropped
            } else {
                $movement = "unchanged"; // rank same
            }

            // Update DB
            if ($stmt) {
                $stmt->bind_param("isi", $diff,$movement, $row['id']);
                $stmt->execute();
            }

            $existing_keys_today[$key] = true;
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
    // 4. Finish up
    // ===============================
    if ($stmt) $stmt->close();
    $conn->close();

    $end_time = microtime(true);
    $elapsed = $end_time - $start_time;

    echo "✅ Rank comparison & diff update complete!\n";
    echo "⏱ Time taken: " . round($elapsed, 2) . "s\n";
    echo "📊 Processed today's records. Dropped out: $dropped_out_count\n";

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

<?php

    include '../config.php';

    $start_time = microtime(true);
    // File for dropped out entries
    $timestamp = date("Ymd_His");
    $filename = "dropped_$timestamp.txt";
    $file = fopen($filename, "w");

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    // Dates
    $yesterday = 'spotify_charts_20250821_105411';
    $today     = 'spotify_charts_20250822_051029';

    // ===============================
    // 1. Fetch yesterday’s data
    // ===============================
    $old_data = [];
    $sql_old = "SELECT id, showId,countryCode, category, chart_rank 
                FROM `$yesterday`";
    $result_old = $conn->query($sql_old);

    if ($result_old) {
        while ($row = $result_old->fetch_assoc()) {
            $key =  $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
            $old_data[$key] = (int)$row['chart_rank'];
        }
    }

    // ===============================
    // 2. Fetch today’s data & compare
    // ===============================
    $sql_today = "SELECT id, showId, countryCode, category, chart_rank 
                FROM `$today`";
    $result_today = $conn->query($sql_today);

    $existing_keys_today = [];
    $dropped_out_count = 0;

    // Prepare update statement ONCE (faster than inside loop)
    $update_sql = "UPDATE `$today` SET  y_rank=?, movement = ?, cal_move = ?  WHERE id = ? ";
    $stmt = $conn->prepare($update_sql);

    if ($stmt && $result_today) {
        while ($row = $result_today->fetch_assoc()) {
            $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];

            $today_rank = (int)$row['chart_rank'];
            $yesterday_rank = $old_data[$key] ?? null;
            $movement = $yesterday_rank !== null ? $yesterday_rank - $today_rank : null;
            $y_rank = $yesterday_rank !== null ? $yesterday_rank : null;

            if ($y_rank === null) {
                $cal_move = "new"; // not present yesterday
            } elseif ($today_rank < $y_rank) {
                $cal_move = "up"; // rank improved
            } elseif ($today_rank > $y_rank) {
                $cal_move = "down"; // rank dropped
            } else {
                $cal_move = "unchanged"; // rank same
            }

            // Update DB
            if ($stmt) {
                $stmt->bind_param("iisi",$y_rank,$movement, $cal_move,$row['id']);
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
            $line = "📉 Dropped out: $key (was rank $yesterday_rank)\n";
            echo $line;
            fwrite($file, $line);
        }
        
    }

    fclose($file);

    // ===============================
    // 4. Finish up
    // ===============================
    if ($stmt) $stmt->close();
    $conn->close();

    $end_time = microtime(true);
    $elapsed = $end_time - $start_time;

    echo "✅ Rank comparison & movement update complete!\n";
    echo "⏱ Time taken: " . round($elapsed, 2) . "s\n";
    echo "📊 Processed today's records. Dropped out: $dropped_out_count\n";
    echo "📝 Dropped shows saved in file: $filename\n";
?>
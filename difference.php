<?php
include "./config.php";
$start_time = microtime(true);
echo "start time ". $start_time . " in microtime \n";
// Connect
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Drop old table if exists
$drop_sql = "DROP TABLE IF EXISTS todays_rank";
if ($conn->query($drop_sql) === TRUE) {
    echo "✅ Old todays_rank table removed.\n";
} else {
    echo "⚠️ Error dropping table: " . $conn->error . "\n";
}

// Create new table
// Create new table
$create_sql = "CREATE TABLE todays_rank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    showId VARCHAR(255),
    showName VARCHAR(255),
    countryCode VARCHAR(50),
    countryName VARCHAR(255),
    category VARCHAR(255),
    episodeId VARCHAR(255) DEFAULT NULL,
    today_rank INT DEFAULT NULL,
    yesterday_rank INT DEFAULT NULL,
    rank_diff INT DEFAULT NULL,
    movement VARCHAR(50) DEFAULT NULL
)";

if ($conn->query($create_sql) === TRUE) {
    echo "✅ New todays_rank table created.\n";
} else {
    die("❌ Error creating table: " . $conn->error);
}

// First, insert current day's data with movement calculation
$insert_current_sql = "
INSERT INTO todays_rank (
    showId, showName, countryCode, countryName, category, 
    episodeId, today_rank, yesterday_rank, rank_diff, movement
)
SELECT 
    t.showId,
    t.showName,
    t.countryCode,
    t.countryName,
    t.category,
    t.episodeId,
    t.chart_rank AS today_rank,
    y.chart_rank AS yesterday_rank,
    CASE 
        WHEN y.chart_rank IS NULL THEN NULL
        ELSE (y.chart_rank - t.chart_rank)
    END AS rank_diff,
    CASE
        WHEN y.chart_rank IS NULL THEN 'NEW'
        WHEN (y.chart_rank - t.chart_rank) > 0 THEN 'UP'
        WHEN (y.chart_rank - t.chart_rank) < 0 THEN 'DOWN'
        ELSE 'UNCHANGED'
    END AS movement
FROM `14-08-with-top-episodes` t
LEFT JOIN `13-08-with-top-episodes` y ON (
    t.countryCode = y.countryCode 
    AND t.category = y.category 
    AND t.showId = y.showId 
    AND (
        (t.category = 'top_episodes' AND t.episodeId = y.episodeId) 
        OR (t.category != 'top_episodes')
    )
)";

if ($conn->query($insert_current_sql) === TRUE) {
    echo "✅ Current day data inserted into todays_rank.\n";
} else {
    echo "❌ Error inserting current data: " . $conn->error . "\n";
}

// Find maximum rank from today's data for dropped out items
$max_rank_result = $conn->query("SELECT MAX(today_rank) as max_rank FROM todays_rank");
$max_rank_row = $max_rank_result->fetch_assoc();
$max_rank_today = $max_rank_row['max_rank'];

// Now insert dropped out items (items in yesterday but not in today)
$insert_dropped_sql = "
INSERT INTO todays_rank (
    showId, showName, countryCode, countryName, category, 
    episodeId, today_rank, yesterday_rank, rank_diff, movement
)
SELECT 
    y.showId,
    y.showName,
    y.countryCode,
    y.countryName,
    y.category,
    y.episodeId,
    ($max_rank_today + ROW_NUMBER() OVER (ORDER BY y.chart_rank)) AS today_rank,
    y.chart_rank AS yesterday_rank,
    NULL as rank_diff,
    'DOWN_OUT' as movement
FROM `13-08-with-top-episodes` y
LEFT JOIN `14-08-with-top-episodes` t ON (
    y.countryCode = t.countryCode 
    AND y.category = t.category 
    AND y.showId = t.showId 
    AND (
        (y.category = 'top_episodes' AND y.episodeId = t.episodeId) 
        OR (y.category != 'top_episodes')
    )
)
WHERE t.showId IS NULL";

if ($conn->query($insert_dropped_sql) === TRUE) {
    echo "✅ Dropped out items inserted into todays_rank.\n";
} else {
    echo "❌ Error inserting dropped out data: " . $conn->error . "\n";
}

// Display summary
$summary_sql = "
SELECT 
    movement,
    COUNT(*) as count
FROM todays_rank 
GROUP BY movement 
ORDER BY movement";

$end_time = microtime(true);
$execution_time = $end_time - $start_time;

echo "\n⏱️ Script executed in " . round($execution_time, 3) . " seconds.";


$result = $conn->query($summary_sql);
echo "\n📊 Movement Summary:\n";
while($row = $result->fetch_assoc()) {
    echo "- {$row['movement']}: {$row['count']} items\n";
}

$conn->close();
echo "\n✅ Process completed successfully!";
?>
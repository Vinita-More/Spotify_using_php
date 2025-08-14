<?php

include './config.php';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

$Three = [ 
    "ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", 
    "id", "ie", "it", "jp", "nz", "no", "ph", "es", "nl"
];
$Seventeen = [
    "au", "us", "gb", "br", "de", "mx", "se"
];
$CATEGORIES_3 = ["top", "top_episodes", "trending"];
$CATEGORIES_20 = [
    "top", "trending", "top_episodes", "arts", "business", "comedy", "education", "fiction", "history", 
    "health%252520%2526%252520fitness", "leisure", "music", "news", "religion%252520%2526%252520spirituality", 
    "science", "society%252520%2526%252520culture", "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"
];

$countries = array_unique(array_merge($Three, $Seventeen));

// CSV File setup
$csvFile = fopen("rank_comparison.csv", "w");
fputcsv($csvFile, ["Country", "Category", "showName", "showID", "EpisodeID", "YesterdayRank", "TodayRank", "Movement", "Status"]);

foreach ($countries as $country) {
    if (in_array($country, $Seventeen)) {
        $categories = $CATEGORIES_20;
    } else {
        $categories = $CATEGORIES_3;
    }

    foreach ($categories as $category) {
        echo "Processing $country - $category\n";
        
       
        // Fetch yesterday's ranks
        $yesterdayData = [];
        $result = $mysqli->query("SELECT showId, chart_rank, showName, episodeId FROM `13-08-with-top-episodes` WHERE countryCode='$country' AND category='$category'");
        while ($row = $result->fetch_assoc()) {
           $key = ($category === "top_episodes") ? $row['episodeId'] : $row['showId'];
            $yesterdayData[$key] = [
                'rank' => $row['chart_rank'],
                'showName' => $row['showName'],
                'showId' => $row['showId'],
                'episodeId' => $row['episodeId'] ?? null
            ];
        }

        // Fetch today's ranks
        $todayData = [];
        $result = $mysqli->query("SELECT showId, chart_rank, showName, episodeId FROM `14-08-with-top-episodes` WHERE countryCode='$country' AND category='$category'");
        while ($row = $result->fetch_assoc()) {
            $key = ($category === "top_episodes") ? $row['episodeId'] : $row['showId'];
            $todayData[$key] = [
                'rank' => $row['chart_rank'],
                'showName' => $row['showName'],
                'showId' => $row['showId'],
                'episodeId' => $row['episodeId'] ?? null
            ];
        }

        // Compare
        foreach ($todayData as $key => $todayInfo) {
            $todayRank = $todayInfo['rank'];
            $showName = $todayInfo['showName'];
            $showId = $todayInfo['showId'];
            $episodeId = $todayInfo['episodeId'] ?? null;

            if (isset($yesterdayData[$key])) {
                $yesterdayRank = $yesterdayData[$key]['rank'];
                $movement = $yesterdayRank - $todayRank;

                if ($movement > 0) {
                    $status = "Up";
                } elseif ($movement < 0) {
                    $status = "Down";
                } else {
                    $status = "Same";
                }

                fputcsv($csvFile, [$country, $category, $showName, $showId, $episodeId, $yesterdayRank, $todayRank, $movement, $status]);
            } else {
                // New podcast
                fputcsv($csvFile, [$country, $category, $showName, $showId, $episodeId, "-", $todayRank, "-", "New"]);
            }
        }

        // Check for dropped podcasts
        foreach ($yesterdayData as $key => $yesterdayInfo) {
            if (!isset($todayData[$key])) {
                fputcsv($csvFile, [$country, $category, $yesterdayInfo['showName'], $yesterdayInfo['showId'], $yesterdayInfo['episodeId'] ?? null, $yesterdayInfo['rank'], "-", "-", "Dropped"]);
            }
        }
    }
}

fclose($csvFile);
echo "Comparison completed. CSV file saved as rank_comparison.csv\n";

$mysqli->close();
?>

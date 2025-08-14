<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "your_database_name";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// 1️⃣ Check if "today" table exists
$sql_check = "SELECT table_name, CREATE_TIME 
              FROM information_schema.tables 
              WHERE table_schema = '$dbname' 
              AND table_name = 'today'";

$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $create_time = $row['CREATE_TIME'];
    $timestamp = date("Ymd_His", strtotime($create_time));

    // 2️⃣ Rename existing "today" table
    $old_table_name = "changed_spotify_charts_today_" . $timestamp;
    $rename_sql = "RENAME TABLE today TO $old_table_name";
    if ($conn->query($rename_sql) === TRUE) {
        echo "✅ Renamed old today table to $old_table_name\n";
    } else {
        die("❌ Error renaming table: " . $conn->error);
    }

    // 3️⃣ Create a fresh empty "today" table with the same structure
    $create_sql = "CREATE TABLE today LIKE $old_table_name";
    if ($conn->query($create_sql) === TRUE) {
        echo "✅ Created fresh today table\n";
    } else {
        die("❌ Error creating new today table: " . $conn->error);
    }
} else {
    echo "ℹ️ No existing today table found. Creating new one...\n";
    // Create a brand new today table (define structure here)
    $conn->query("CREATE TABLE today (
        episodeId BIGINT,
        showId BIGINT,
        category VARCHAR(255),
        country VARCHAR(10),
        diff INT DEFAULT 0,
        movement VARCHAR(50)
    )");
}

// 4️⃣ Load new data into today table (replace with your actual insert logic)
$conn->query("INSERT INTO today (episodeId, showId, category, country)
              VALUES (111, 999, 'Music', 'US'),
                     (112, 888, 'News', 'GB')");

// 5️⃣ Compute diff + movement from yesterday’s data
if (isset($old_table_name)) {
    $sql_update = "
        UPDATE today t
        JOIN $old_table_name y 
          ON t.showId = y.showId 
         AND t.category = y.category
         AND t.country = y.country
         AND t.episodeId = y.episodeId
        SET t.diff = (y.rank - t.rank),
            t.movement = CASE
                WHEN y.rank > t.rank THEN 'Up'
                WHEN y.rank < t.rank THEN 'Down'
                ELSE 'Same'
            END
    ";
    $conn->query($sql_update);
    echo "✅ Diff & movement computed from old table\n";
}

$conn->close();
?>

<!-- ?php
# to create new db whenever running
// include './config.php';

// // DB connection
// $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
// if ($mysqli->connect_errno) {
//     die("Failed to connect to MySQL: " . $mysqli->connect_error);
// }

// // Your arrays
// $Three = [ "ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", "id", "ie", "it", "jp", "nz", "no", "ph", "es", "nl" ];
// $Seventeen = [ "au", "us", "gb", "br", "de", "mx", "se" ];

// $CATEGORIES_3 = ["top", "top_episodes", "trending"];
// $CATEGORIES_20 = [
//     "top", "trending", "top_episodes", "arts", "business", "comedy", "education", "fiction", "history",
//     "health%252520%2526%252520fitness", "leisure", "music", "news", "religion%252520%2526%252520spirituality",
//     "science", "society%252520%2526%252520culture", "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"
// ];

// $COUNTRY_NAMES = [
//     "ar" => "Argentina", "at" => "Austria", "au" => "Australia", "br" => "Brazil", "ca" => "Canada", "cl" => "Chile",
//     "co" => "Colombia", "de" => "Germany", "dk" => "Denmark", "es" => "Spain", "fi" => "Finland", "fr" => "France",
//     "gb" => "United Kingdom", "id" => "Indonesia", "ie" => "Ireland", "in" => "India", "it" => "Italy", "jp" => "Japan",
//     "mx" => "Mexico", "nl" => "Netherlands", "no" => "Norway", "nz" => "New Zealand", "ph" => "Philippines",
//     "se" => "Sweden", "us" => "United States"
// ];

// // Merge countries
// $countries = array_unique(array_merge($Three, $Seventeen));

// // Timestamp for table name
// $timestamp = date("Ymd_His");
// $newTable = "spotify_charts_$timestamp";

// $createSQL = "CREATE TABLE `$newTable` (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     showId VARCHAR(255),
//     showName TEXT,
//     showPublisher TEXT,
//     showImageUrl TEXT,
//     showDescription TEXT,
//     countryCode VARCHAR(10),
//     countryName VARCHAR(255),
//     categoryName VARCHAR(255),
//     episodeId VARCHAR(255) DEFAULT NULL,
//     episodeName TEXT DEFAULT NULL,
//     chart_rank INT,
//     movement VARCHAR(50),
//     diff INT DEFAULT NULL
// )";

// if (!$mysqli->query($createSQL)) {
//     die("[ERROR] Failed to create table: " . $mysqli->error);
// }

// // Prepare insert statement
// $stmt = $mysqli->prepare("INSERT INTO `$newTable` (
//     showId, showName, showPublisher, showImageUrl, showDescription,  countryCode,
//     countryName, categoryName, episodeId, episodeName ,chart_rank, movement
// ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


// if (!$stmt) {
//     die("[ERROR] Prepare failed: " . $mysqli->error);
// }


// function fetchWithRetry($url, $retries = 3) {
//     $attempt = 0;
//     while ($attempt < $retries) {
//         $json = file_get_contents($url);
//         if ($json !== false) {
//             return json_decode($json, true);
//         }
//         $attempt++;
//         sleep(1);
//     }
//     return null;
// }

// foreach ($countries as $country) {
//     $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : $CATEGORIES_3;

//     foreach ($categories as $category) {
//         echo "[INFO] Fetching " . strtoupper($country) . " - $category\n";
//         $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";

//         $data = fetchWithRetry($url);
//         if ($data === null) {
//             echo "[ERROR] Failed to fetch data: $country - $category\n";
//             continue;
//         }

//         $items = $data['items'] ?? [];
//         $rank = 1;

//         foreach ($items as $item) {
//             $showUri = $item['showUri'] ?? '';  
//             $showId = $showUri ? explode(":", $showUri)[count(explode(":", $showUri)) - 1] : '';
//             $showName = $item['showName'] ?? '';
//             $showPublisher = $item['showPublisher'] ?? '';
//             $showImageUrl = $item['showImageUrl'] ?? '';
//             $showDescription = $item['showDescription'] ?? ''; 
//             $countryName = $COUNTRY_NAMES[$country] ?? '';
//             $categoryName = ucwords(str_replace('-', ' ', urldecode($category)));
//             $episodeUri = $item['episodeUri'] ?? null;
//             $episodeId = $episodeUri ? explode(":", $episodeUri)[count(explode(":", $episodeUri)) - 1] : null;
//             $episodeName = $item['episodeName'] ?? '';
//             $movement = $item['chartRankMove'];
            
//             $stmt->bind_param(
//                 "ssssssssssis",
//                 $showId,
//                 $showName,
//                 $showPublisher,
//                 $showImageUrl,
//                 $showDescription,
//                 $country,
//                 $countryName,
//                 $categoryName,
//                 $episodeId,
//                 $episodeName,
//                 $rank,
//                 $movement
//             );

//             if (!$stmt->execute()) {
//                 echo "[ERROR] Insert failed: " . $stmt->error . "\n";
//             }

//             $rank++;
//         }

//         echo "[OK] Inserted " . count($items) . " rows for $country - $category\n";
//         sleep(1);
//     }
// }

// $stmt->close();
// $mysqli->close();
// echo "[DONE] Data loaded into $newTable\n";
?> 
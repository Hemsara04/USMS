<?php
/**
 * USMS TIC Demo Data Seeder
 * Populates sports, athletes, facilities, equipment, requests, fixtures, squads, and bookings.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../models/User.php';

$pdo = Database::connect();
$userModel = new User();

echo "<h2>Populating USMS TIC Demo Data...</h2>";

// 1. SEED SPORTS
$sports = [
    'Football',
    'Basketball',
    'Cricket',
    'Badminton',
    'Track & Field',
    'Swimming',
    'Rugby',
    'Volleyball',
    'Tennis',
    'Table Tennis'
];

foreach ($sports as $s) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sports WHERE sport_name = ?");
    $stmt->execute([$s]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO sports (sport_name) VALUES (?)")->execute([$s]);
        echo "<p style='color:green;'>+ Sport added: <strong>$s</strong></p>";
    }
}

// 2. SEED FACILITIES
$facilities = [
    ['Main Gymnasium', 'Hall', 500, '06:00 - 21:00'],
    ['Colombo University Grounds', 'Ground', 2000, '06:00 - 18:30'],
    ['Indoor Sports Arena', 'Hall', 350, '07:00 - 22:00'],
    ['University Tennis Courts', 'Ground', 150, '06:30 - 19:00'],
    ['Swimming Pool Complex', 'Hall', 300, '06:00 - 20:00']
];

foreach ($facilities as $f) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE facility_name = ?");
    $stmt->execute([$f[0]]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO facilities (facility_name, type, capacity, available_time_windows) VALUES (?, ?, ?, ?)")->execute($f);
        echo "<p style='color:green;'>+ Facility added: <strong>{$f[0]}</strong></p>";
    }
}

// 3. SEED SEASONS
$stmt = $pdo->query("SELECT COUNT(*) FROM seasons WHERE season_year = 2026");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO seasons (season_year, academic_year, status) VALUES (2026, 3, 'Active')");
    echo "<p style='color:green;'>+ Season 2026 created</p>";
}

// 4. SEED SAMPLE ATHLETES (and corresponding user accounts)
$sampleAthletes = [
    ['Kasun Perera',      'athlete_kasun',   'kasun@uoc.ac.lk',   'SC/2022/1042', 3, 'Faculty of Science',      178.5, 72.0, '+94771234567'],
    ['Sarah Jenkins',     'athlete_sarah',   'sarah@uoc.ac.lk',   'AR/2021/8821', 4, 'Faculty of Arts',         169.0, 58.5, '+94779876543'],
    ['Marcus Thorne',     'athlete_marcus',  'marcus@uoc.ac.lk',  'MG/2023/4420', 2, 'Faculty of Management',   185.0, 84.0, '+94715551234'],
    ['Elena Rodriguez',   'athlete_elena',   'elena@uoc.ac.lk',   'MD/2022/1928', 3, 'Faculty of Medicine',     165.0, 55.0, '+94762223344'],
    ['Tom Aris',          'athlete_tom',     'tom@uoc.ac.lk',     'TC/2023/3391', 2, 'Faculty of Technology',   180.0, 76.5, '+94784445566'],
    ['Dinuka Fernando',   'athlete_dinuka',  'dinuka@uoc.ac.lk',  'SC/2021/2049', 4, 'Faculty of Science',      175.0, 68.0, '+94701112233'],
    ['Amali Wickramasinghe','athlete_amali', 'amali@uoc.ac.lk',   'AR/2023/5021', 2, 'Faculty of Arts',         162.0, 52.0, '+94718889900']
];

foreach ($sampleAthletes as $ath) {
    $fullName  = $ath[0];
    $username  = $ath[1];
    $email     = $ath[2];
    $indexNo   = $ath[3];
    $acadYear  = $ath[4];
    $faculty   = $ath[5];
    $height    = $ath[6];
    $weight    = $ath[7];
    $emContact = $ath[8];

    // Create user if not exists
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        $userModel->create($username, $email, 'password123', 'Athlete');
        $userId = $pdo->lastInsertId();
    }

    // Create athlete record
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Athletic WHERE index_number = ?");
    $stmt->execute([$indexNo]);
    if ($stmt->fetchColumn() == 0) {
        $stmtAth = $pdo->prepare("
            INSERT INTO Athletic (user_id, index_number, full_name, academic_year, faculty, height, weight, emergency_Contact)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtAth->execute([$userId, $indexNo, $fullName, $acadYear, $faculty, $height, $weight, $emContact]);
        echo "<p style='color:green;'>+ Athlete profile created: <strong>$fullName</strong> ($indexNo)</p>";
    }
}

// 5. SEED EQUIPMENT INVENTORY
$equipmentItems = [
    ['Football',      'Elite GPS Trackers (Set of 10)',   14, 'Good'],
    ['Football',      'Nike Strike Training Footballs',   45, 'New'],
    ['Football',      'Agility Training Hurdles Set',     22, 'Good'],
    ['Basketball',    'Spalding TF-1000 Basketballs',     30, 'New'],
    ['Basketball',    'Pro-Grade Rebounder Net',          6,  'Good'],
    ['Cricket',       'SS Ton Reserve Cricket Bats',      18, 'Good'],
    ['Cricket',       'Kookaburra Turf Match Balls',      60, 'New'],
    ['Track & Field', 'High-Jump Pit Measurement Kit',    4,  'Good'],
    ['Track & Field', 'Starting Blocks (Olympic Standard)', 8, 'New'],
    ['Badminton',     'Yonex Nanoflare Rackets',          24, 'Good'],
    ['Badminton',     'Yonex Aerosensa Shuttlecock Tubes', 80, 'New'],
    ['Swimming',      'Digital Stopwatch & Pace Clocks',  12, 'Good'],
    ['Tennis',        'Wilson Pro Staff Tennis Rackets',  15, 'Good'],
    ['Tennis',        'Wilson Championship Ball Tubes',   50, 'New']
];

foreach ($equipmentItems as $eq) {
    $sportName = $eq[0];
    $itemName  = $eq[1];
    $qty       = $eq[2];
    $cond      = $eq[3];

    $sportId = $pdo->query("SELECT sport_id FROM sports WHERE sport_name = '$sportName'")->fetchColumn();
    if ($sportId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE item_name = ?");
        $stmt->execute([$itemName]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO equipment (sport_id, item_name, quantity_in_stock, condition_status) VALUES (?, ?, ?, ?)")->execute([$sportId, $itemName, $qty, $cond]);
            echo "<p style='color:green;'>+ Equipment item created: <strong>$itemName</strong> ($qty units)</p>";
        }
    }
}

// 6. SEED EQUIPMENT REQUESTS & CHECKOUTS
$ath1 = $pdo->query("SELECT athlete_id FROM Athletic LIMIT 1 OFFSET 0")->fetchColumn();
$ath2 = $pdo->query("SELECT athlete_id FROM Athletic LIMIT 1 OFFSET 1")->fetchColumn();
$ath3 = $pdo->query("SELECT athlete_id FROM Athletic LIMIT 1 OFFSET 2")->fetchColumn();
$ath4 = $pdo->query("SELECT athlete_id FROM Athletic LIMIT 1 OFFSET 3")->fetchColumn();

$eq1 = $pdo->query("SELECT equipment_id FROM equipment LIMIT 1 OFFSET 0")->fetchColumn();
$eq2 = $pdo->query("SELECT equipment_id FROM equipment LIMIT 1 OFFSET 1")->fetchColumn();
$eq3 = $pdo->query("SELECT equipment_id FROM equipment LIMIT 1 OFFSET 3")->fetchColumn();
$eq4 = $pdo->query("SELECT equipment_id FROM equipment LIMIT 1 OFFSET 5")->fetchColumn();
$eq5 = $pdo->query("SELECT equipment_id FROM equipment LIMIT 1 OFFSET 7")->fetchColumn();

if ($pdo->query("SELECT COUNT(*) FROM equipment_issue")->fetchColumn() == 0) {
    // Pending Requests
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Pending')")->execute([$eq1, $ath1, date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))]);
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Pending')")->execute([$eq2, $ath2, date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('+6 days'))]);
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Pending')")->execute([$eq3, $ath3, date('Y-m-d', strtotime('-2 days')), date('Y-m-d', strtotime('+5 days'))]);
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Pending')")->execute([$eq4, $ath4, date('Y-m-d', strtotime('-2 days')), date('Y-m-d', strtotime('+4 days'))]);

    // Active Checkouts (Issued)
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Issued')")->execute([$eq5, $ath1, date('Y-m-d', strtotime('-3 days')), date('Y-m-d', strtotime('+4 days'))]);
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Issued')")->execute([$eq1, $ath2, date('Y-m-d', strtotime('-4 days')), date('Y-m-d', strtotime('+3 days'))]);
    $pdo->prepare("INSERT INTO equipment_issue (equipment_id, athletic_id, issue_date, expected_return_date, status) VALUES (?, ?, ?, ?, 'Issued')")->execute([$eq2, $ath3, date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('+2 days'))]);

    echo "<p style='color:green;'>+ Seeded 4 Pending Requests and 3 Active Checkouts</p>";
}

// 7. SEED FIXTURES & SQUAD APPROVALS
$footId = $pdo->query("SELECT sport_id FROM sports WHERE sport_name = 'Football'")->fetchColumn();
$baskId = $pdo->query("SELECT sport_id FROM sports WHERE sport_name = 'Basketball'")->fetchColumn();
$cricId = $pdo->query("SELECT sport_id FROM sports WHERE sport_name = 'Cricket'")->fetchColumn();

$groundId = $pdo->query("SELECT facility_id FROM facilities WHERE facility_name LIKE '%Grounds%'")->fetchColumn();
$gymId    = $pdo->query("SELECT facility_id FROM facilities WHERE facility_name LIKE '%Gymnasium%'")->fetchColumn();

if ($pdo->query("SELECT COUNT(*) FROM fixtures")->fetchColumn() == 0) {
    $stmtFix = $pdo->prepare("INSERT INTO fixtures (sport_id, facility_id, opponent, fixture_type, fixture_date, fixture_time) VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmtFix->execute([$footId, $groundId, 'University of Peradeniya', 'Inter-University', date('Y-m-d', strtotime('+4 days')), '15:30:00']);
    $fix1 = $pdo->lastInsertId();

    $stmtFix->execute([$baskId, $gymId, 'University of Moratuwa', 'Inter-University', date('Y-m-d', strtotime('+7 days')), '17:00:00']);
    $fix2 = $pdo->lastInsertId();

    $stmtFix->execute([$cricId, $groundId, 'University of Kelaniya', 'Inter-University', date('Y-m-d', strtotime('+10 days')), '09:30:00']);
    $fix3 = $pdo->lastInsertId();

    // Squad members
    $allAthletes = $pdo->query("SELECT athlete_id FROM Athletic LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allAthletes as $aid) {
        $pdo->prepare("INSERT INTO match_squads (fixture_id, athlete_id, tic_approval_status) VALUES (?, ?, 'Pending')")->execute([$fix1, $aid]);
        $pdo->prepare("INSERT INTO match_squads (fixture_id, athlete_id, tic_approval_status) VALUES (?, ?, 'Approved')")->execute([$fix2, $aid]);
    }
    echo "<p style='color:green;'>+ Seeded 3 Upcoming Match Fixtures and Squads</p>";
}

// 8. SEED FACILITY BOOKINGS
if ($pdo->query("SELECT COUNT(*) FROM facility_bookings")->fetchColumn() == 0) {
    $stmtBook = $pdo->prepare("INSERT INTO facility_bookings (facility_id, sport_id, booking_date, start_time, duration_minutes, approval_status) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Pending
    $stmtBook->execute([$gymId, $baskId, date('Y-m-d', strtotime('+2 days')), '16:00:00', 90, 'Pending']);
    $stmtBook->execute([$groundId, $footId, date('Y-m-d', strtotime('+3 days')), '07:00:00', 120, 'Pending']);
    
    // Approved
    $stmtBook->execute([$groundId, $cricId, date('Y-m-d', strtotime('+1 day')), '14:00:00', 180, 'Approved']);
    $stmtBook->execute([$gymId, $baskId, date('Y-m-d', strtotime('+5 days')), '18:00:00', 90, 'Approved']);
    
    echo "<p style='color:green;'>+ Seeded Facility Bookings (2 Pending, 2 Approved)</p>";
}

echo "<hr><h3 style='color:green;'>✅ TIC Demo Data Seeded Successfully!</h3>";
?>

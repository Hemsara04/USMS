<?php
/**
 * Database Migration and Comprehensive Demo Data Seeding Script
 * Updates USMS database schema and seeds realistic, synchronized demo data.
 */

require_once __DIR__ . '/config/db.php';

try {
    $db = Database::connect();
    echo "Connected to database successfully.\n";

    // 1. Schema Updates
    echo "Ensuring schema integrity...\n";

    // Check & update user table
    $userCols = $db->query("SHOW COLUMNS FROM `user` LIKE 'phone_number'")->fetch();
    if (!$userCols) {
        $db->exec("ALTER TABLE `user` ADD COLUMN `phone_number` VARCHAR(20) NOT NULL DEFAULT '+94 11 258 1835' AFTER `email`");
        echo "Added phone_number to user table.\n";
    }

    // Check & update Athletic table
    $db->exec("ALTER TABLE `Athletic` MODIFY COLUMN `height` FLOAT(5,2) NULL DEFAULT 0.00");
    $db->exec("ALTER TABLE `Athletic` MODIFY COLUMN `weight` FLOAT(5,2) NULL DEFAULT 0.00");
    $db->exec("ALTER TABLE `Athletic` MODIFY COLUMN `emergency_Contact` VARCHAR(20) NULL DEFAULT ''");
    
    // Check & update Athletic table for Medical attributes
    $bgCol = $db->query("SHOW COLUMNS FROM `Athletic` LIKE 'blood_group'")->fetch();
    if (!$bgCol) {
        $db->exec("ALTER TABLE `Athletic` ADD COLUMN `blood_group` VARCHAR(10) NULL DEFAULT 'O+'");
        $db->exec("ALTER TABLE `Athletic` ADD COLUMN `medical_status` ENUM('Fit to Compete', 'Under Review', 'Temporarily Unfit') NOT NULL DEFAULT 'Fit to Compete'");
        $db->exec("ALTER TABLE `Athletic` ADD COLUMN `allergies` VARCHAR(255) NULL DEFAULT 'None'");
        $db->exec("ALTER TABLE `Athletic` ADD COLUMN `medical_notes` TEXT NULL");
        $db->exec("ALTER TABLE `Athletic` ADD COLUMN `last_examined_date` DATE NULL");
        echo "Added medical columns to Athletic table.\n";
    }

    // Check & update injuries table
    $injNotes = $db->query("SHOW COLUMNS FROM `injuries` LIKE 'notes'")->fetch();
    if (!$injNotes) {
        $db->exec("ALTER TABLE `injuries` ADD COLUMN `notes` TEXT NULL");
        $db->exec("ALTER TABLE `injuries` ADD COLUMN `reported_by` VARCHAR(100) NULL DEFAULT 'Medical Officer'");
        echo "Added notes and reported_by to injuries table.\n";
    }

    // Check & update medical_clearances table
    $clrNotes = $db->query("SHOW COLUMNS FROM `medical_clearances` LIKE 'notes'")->fetch();
    if (!$clrNotes) {
        $db->exec("ALTER TABLE `medical_clearances` ADD COLUMN `cleared_date` DATE NULL");
        $db->exec("ALTER TABLE `medical_clearances` ADD COLUMN `notes` TEXT NULL");
        $db->exec("ALTER TABLE `medical_clearances` ADD COLUMN `certifying_doctor` VARCHAR(100) NULL DEFAULT 'Dr. A. Fernando'");
        echo "Added cleared_date, notes, and certifying_doctor to medical_clearances.\n";
    }
    echo "Updated Athletic table column defaults.\n";

    // Check & update announcements table
    $annCols = $db->query("SHOW COLUMNS FROM `announcements` LIKE 'target_audience'")->fetch();
    if (!$annCols) {
        $db->exec("ALTER TABLE `announcements` ADD COLUMN `target_audience` ENUM('All Users','Student Athletes','Coaches & Staff','Medical Staff','TIC') NOT NULL DEFAULT 'All Users'");
        $db->exec("ALTER TABLE `announcements` ADD COLUMN `priority` ENUM('Normal','Important','Urgent') NOT NULL DEFAULT 'Normal'");
        $db->exec("ALTER TABLE `announcements` ADD COLUMN `status` ENUM('Active','Archived') NOT NULL DEFAULT 'Active'");
        $db->exec("ALTER TABLE `announcements` ADD COLUMN `updated_at` DATETIME NULL");
        echo "Added target_audience, priority, status to announcements table.\n";
    }

    // Check & update fixtures table
    $fixtureCols = $db->query("SHOW COLUMNS FROM `fixtures` LIKE 'home_team'")->fetch();
    if (!$fixtureCols) {
        $db->exec("ALTER TABLE `fixtures` ADD COLUMN `home_team` VARCHAR(100) NOT NULL DEFAULT 'University of Colombo' AFTER `fixture_type`");
        echo "Added home_team to fixtures table.\n";
    }
    $db->exec("ALTER TABLE `fixtures` MODIFY COLUMN `fixture_type` ENUM('Inter-University','Inter-Faculty','Internal') NOT NULL DEFAULT 'Inter-University'");
    echo "Updated fixtures table fixture_type enum.\n";

    // Check & update equipment_issue table
    $eqIssueCols = $db->query("SHOW COLUMNS FROM `equipment_issue` LIKE 'recipient_type'")->fetch();
    if (!$eqIssueCols) {
        $db->exec("ALTER TABLE `equipment_issue` ADD COLUMN `recipient_type` ENUM('Athlete','Coach') NOT NULL DEFAULT 'Athlete' AFTER `athletic_id`");
        $db->exec("ALTER TABLE `equipment_issue` ADD COLUMN `recipient_name` VARCHAR(150) NULL AFTER `recipient_type`");
        echo "Added recipient_type and recipient_name to equipment_issue.\n";
    }
    $qtyCol = $db->query("SHOW COLUMNS FROM `equipment_issue` LIKE 'quantity'")->fetch();
    if (!$qtyCol) {
        $db->exec("ALTER TABLE `equipment_issue` ADD COLUMN `quantity` INT(11) NOT NULL DEFAULT 1 AFTER `equipment_id`");
        echo "Added quantity to equipment_issue.\n";
    }
    $db->exec("ALTER TABLE `equipment_issue` MODIFY COLUMN `athletic_id` INT(11) NULL");
    $db->exec("ALTER TABLE `equipment_issue` MODIFY COLUMN `status` ENUM('Pending','Issued','Returned','Rejected') NOT NULL DEFAULT 'Pending'");
    echo "Updated equipment_issue table.\n";

    // Check & update equipment condition_status enum
    $db->exec("ALTER TABLE `equipment` MODIFY COLUMN `condition_status` ENUM('New','Good','Needs Repair') NOT NULL DEFAULT 'Good'");

    // Update sport_application schema
    try {
        $db->exec("ALTER TABLE `sport_application` MODIFY COLUMN `application_id` INT(11) NOT NULL AUTO_INCREMENT");
    } catch (Exception $e) {}
    try {
        $db->exec("ALTER TABLE `sport_application` MODIFY COLUMN `status` ENUM('Pending','Shortlisted','Approved','Rejected') NOT NULL DEFAULT 'Pending'");
    } catch (Exception $e) {}
    try {
        $db->exec("ALTER TABLE `sport_application` MODIFY COLUMN `rejection_reason` TEXT DEFAULT NULL");
    } catch (Exception $e) {}
    $appStatementCol = $db->query("SHOW COLUMNS FROM `sport_application` LIKE 'statement_of_interest'")->fetch();
    if (!$appStatementCol) {
        $db->exec("ALTER TABLE `sport_application` ADD COLUMN `statement_of_interest` TEXT DEFAULT NULL");
    }
    $appDateCol = $db->query("SHOW COLUMNS FROM `sport_application` LIKE 'applied_date'")->fetch();
    if (!$appDateCol) {
        $db->exec("ALTER TABLE `sport_application` ADD COLUMN `applied_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    // Update training_sessions schema
    try {
        $db->exec("ALTER TABLE `training_sessions` MODIFY COLUMN `session_id` INT(11) NOT NULL AUTO_INCREMENT");
    } catch (Exception $e) {}
    try {
        $db->exec("ALTER TABLE `training_sessions` MODIFY COLUMN `facility_id` INT(11) NULL DEFAULT NULL");
    } catch (Exception $e) {}
    $tsFocusCol = $db->query("SHOW COLUMNS FROM `training_sessions` LIKE 'focus_area'")->fetch();
    if (!$tsFocusCol) {
        $db->exec("ALTER TABLE `training_sessions` ADD COLUMN `focus_area` VARCHAR(255) NULL DEFAULT NULL AFTER `session_type`");
    }
    $tsNotesCol = $db->query("SHOW COLUMNS FROM `training_sessions` LIKE 'notes'")->fetch();
    if (!$tsNotesCol) {
        $db->exec("ALTER TABLE `training_sessions` ADD COLUMN `notes` TEXT NULL DEFAULT NULL AFTER `focus_area`");
    }

    // Update training_attendance schema
    try {
        $db->exec("ALTER TABLE `training_attendance` MODIFY COLUMN `attendance_id` INT(11) NOT NULL AUTO_INCREMENT");
    } catch (Exception $e) {}
    try {
        $db->exec("ALTER TABLE `training_attendance` MODIFY COLUMN `status` ENUM('Present','Absent','Excused') NOT NULL DEFAULT 'Present'");
    } catch (Exception $e) {}

    // Update match_results schema
    try {
        $db->exec("ALTER TABLE `match_results` MODIFY COLUMN `result_id` INT(11) NOT NULL AUTO_INCREMENT");
    } catch (Exception $e) {}
    try {
        $db->exec("ALTER TABLE `match_results` MODIFY COLUMN `scorers` TEXT NULL DEFAULT NULL");
    } catch (Exception $e) {}
    $mrReportCol = $db->query("SHOW COLUMNS FROM `match_results` LIKE 'match_report'")->fetch();
    if (!$mrReportCol) {
        $db->exec("ALTER TABLE `match_results` ADD COLUMN `match_report` TEXT NULL DEFAULT NULL AFTER `scorers`");
    }
    try {
        $db->exec("ALTER TABLE `match_results` MODIFY COLUMN `player_ratings` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL");
    } catch (Exception $e) {}

    echo "Schema updates complete.\n\n";

    // 2. Synchronize and Seed Users
    echo "Synchronizing Users and Athletes...\n";

    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    $secAnsHash   = password_hash('football', PASSWORD_BCRYPT);

    // List of standard users
    $usersToSeed = [
        // Staff / Admin
        ['username' => 'tic_admin',       'email' => 'tic@uoc.ac.lk',          'phone' => '+94 11 258 1835', 'role' => 'TIC',             'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'saman_kusum',     'email' => 'saman@uoc.ac.lk',        'phone' => '+94 11 258 1837', 'role' => 'TIC',             'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'pres_admin',      'email' => 'president@uoc.ac.lk',    'phone' => '+94 11 258 1836', 'role' => 'President',       'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'med_officer',     'email' => 'medical@uoc.ac.lk',      'phone' => '+94 11 258 5500', 'role' => 'Medical_Officer', 'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'coach_silva',     'email' => 'coach.silva@uoc.ac.lk',  'phone' => '+94 77 112 2334', 'role' => 'Coach',           'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'coach_sunil',     'email' => 'sunil.coach@uoc.ac.lk',  'phone' => '+94 77 441 2389', 'role' => 'Coach',           'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'coach_anura',     'email' => 'anura.coach@uoc.ac.lk',  'phone' => '+94 71 882 3490', 'role' => 'Coach',           'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],
        ['username' => 'coach_pradeep',   'email' => 'pradeep.coach@uoc.ac.lk','phone' => '+94 76 993 4501', 'role' => 'Coach',           'sec_q' => 'What is your favorite sport?', 'sec_a' => $secAnsHash],

        // Student Athletes across all 10 faculties
        // Faculty of Science
        ['username' => 'athlete_kasun',   'email' => 'kasun@uoc.ac.lk',        'phone' => '+94 77 123 4567', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'athlete_dinuka',  'email' => 'dinuka@uoc.ac.lk',       'phone' => '+94 70 111 2233', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'hasith_eranga1',  'email' => 'ranga.hasitha04@gmail.com','phone' => '+94 75 901 2345','role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Arts
        ['username' => 'athlete_sarah',   'email' => 'sarah@uoc.ac.lk',        'phone' => '+94 77 987 6543', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'athlete_amali',   'email' => 'amali@uoc.ac.lk',        'phone' => '+94 71 888 9900', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'dilshan_pathirana','email' => 'dilshan@uoc.ac.lk',      'phone' => '+94 77 443 3221', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Management & Finance
        ['username' => 'athlete_marcus',  'email' => 'marcus@uoc.ac.lk',       'phone' => '+94 71 555 1234', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'tamasha_sevindi', 'email' => 'tamasha@uoc.ac.lk',      'phone' => '+94 77 678 9012', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'ruwan_weerasinghe','email' => 'ruwan@uoc.ac.lk',       'phone' => '+94 76 332 2110', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Medicine
        ['username' => 'athlete_elena',   'email' => 'elena@uoc.ac.lk',        'phone' => '+94 76 222 3344', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'test_user',       'email' => 'test@uoc.ac.lk',         'phone' => '+94 78 789 0123', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Technology
        ['username' => 'athlete_tom',     'email' => 'tom@uoc.ac.lk',          'phone' => '+94 78 444 5566', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'new_user',        'email' => 'newuser@uoc.ac.lk',      'phone' => '+94 70 890 1234', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // UCSC (School of Computing)
        ['username' => 'athlete_user',    'email' => 'athlete@uoc.ac.lk',      'phone' => '+94 77 345 6789', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'hasith_eranga',   'email' => 'eranga.hasitha04@gmail.com','phone' => '+94 76 567 8901','role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'kusal_mendis',    'email' => 'kusal@uoc.ac.lk',        'phone' => '+94 71 223 3445', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Law
        ['username' => 'kamal_gunaratne', 'email' => 'kamal@uoc.ac.lk',        'phone' => '+94 71 456 7890', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],
        ['username' => 'sanath_jayasuriya','email' => 'sanath@uoc.ac.lk',      'phone' => '+94 77 556 6778', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Education
        ['username' => 'suresh_silva',    'email' => 'suresh@uoc.ac.lk',       'phone' => '+94 78 112 2334', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Nursing
        ['username' => 'kaveesha_w',      'email' => 'kaveesha@uoc.ac.lk',     'phone' => '+94 77 889 9001', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Faculty of Indigenous Medicine
        ['username' => 'sanduni_j',       'email' => 'sanduni@uoc.ac.lk',      'phone' => '+94 76 998 8776', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash],

        // Recently Registered Athletes
        ['username' => 'yumal_perera',    'email' => 'yumal@uoc.ac.lk',        'phone' => '+94 77 123 4567', 'role' => 'Athlete', 'sec_q' => 'What is your primary sport?', 'sec_a' => $secAnsHash]
    ];

    $stmtFindUser = $db->prepare("SELECT user_id FROM `user` WHERE `username` = :u OR `email` = :e LIMIT 1");
    $stmtInsertUser = $db->prepare("
        INSERT INTO `user` (`username`, `email`, `phone_number`, `password_hash`, `role`, `security_question`, `security_answere_hash`)
        VALUES (:username, :email, :phone, :password_hash, :role, :sec_q, :sec_a)
    ");
    $stmtUpdateUser = $db->prepare("
        UPDATE `user` SET `email` = :email, `phone_number` = :phone, `password_hash` = :password_hash, `role` = :role
        WHERE `user_id` = :id
    ");

    $userMap = []; // username => user_id

    foreach ($usersToSeed as $u) {
        $stmtFindUser->execute([':u' => $u['username'], ':e' => $u['email']]);
        $existing = $stmtFindUser->fetch();
        if ($existing) {
            $userId = (int)$existing['user_id'];
            $stmtUpdateUser->execute([
                ':email'         => $u['email'],
                ':phone'         => $u['phone'],
                ':password_hash' => $passwordHash,
                ':role'          => $u['role'],
                ':id'            => $userId
            ]);
        } else {
            $stmtInsertUser->execute([
                ':username'      => $u['username'],
                ':email'         => $u['email'],
                ':phone'         => $u['phone'],
                ':password_hash' => $passwordHash,
                ':role'          => $u['role'],
                ':sec_q'         => $u['sec_q'],
                ':sec_a'         => $u['sec_a']
            ]);
            $userId = (int)$db->lastInsertId();
        }
        $userMap[$u['username']] = $userId;
    }
    echo "Users synchronized successfully. Total seeded/updated: " . count($userMap) . "\n";

    // Synchronize Athletic Table
    $athletesToSeed = [
        // Science
        ['username' => 'athlete_kasun',    'index' => 'SC/2022/1042', 'name' => 'Kasun Perera',         'year' => 3, 'faculty' => 'Faculty of Science',            'h' => 178.5, 'w' => 72.0, 'em' => '+94771234567'],
        ['username' => 'athlete_dinuka',   'index' => 'SC/2021/2049', 'name' => 'Dinuka Fernando',      'year' => 4, 'faculty' => 'Faculty of Science',            'h' => 175.0, 'w' => 68.0, 'em' => '+94701112233'],
        ['username' => 'hasith_eranga1',   'index' => 'SC/2023/3305', 'name' => 'Malith Perera',        'year' => 2, 'faculty' => 'Faculty of Science',            'h' => 172.0, 'w' => 66.5, 'em' => '+94759012345'],

        // Arts
        ['username' => 'athlete_sarah',    'index' => 'AR/2021/8821', 'name' => 'Sarah Jenkins',        'year' => 4, 'faculty' => 'Faculty of Arts',               'h' => 169.0, 'w' => 58.5, 'em' => '+94779876543'],
        ['username' => 'athlete_amali',    'index' => 'AR/2023/5021', 'name' => 'Amali Wickramasinghe', 'year' => 2, 'faculty' => 'Faculty of Arts',               'h' => 162.0, 'w' => 52.0, 'em' => '+94718889900'],
        ['username' => 'dilshan_pathirana','index' => 'AR/2022/6630', 'name' => 'Dilshan Pathirana',    'year' => 3, 'faculty' => 'Faculty of Arts',               'h' => 180.0, 'w' => 74.0, 'em' => '+94774433221'],

        // Management & Finance
        ['username' => 'athlete_marcus',   'index' => 'MG/2023/4420', 'name' => 'Marcus Thorne',        'year' => 2, 'faculty' => 'Faculty of Management & Finance','h' => 185.0, 'w' => 84.0, 'em' => '+94715551234'],
        ['username' => 'tamasha_sevindi',  'index' => 'MG/2022/2204', 'name' => 'Tamasha Sevindi',      'year' => 3, 'faculty' => 'Faculty of Management & Finance','h' => 166.0, 'w' => 56.0, 'em' => '+94776789012'],
        ['username' => 'ruwan_weerasinghe','index' => 'MG/2021/7701', 'name' => 'Ruwan Weerasinghe',    'year' => 4, 'faculty' => 'Faculty of Management & Finance','h' => 177.0, 'w' => 71.0, 'em' => '+94763322110'],

        // Medicine
        ['username' => 'athlete_elena',    'index' => 'MD/2022/1928', 'name' => 'Elena Rodriguez',      'year' => 3, 'faculty' => 'Faculty of Medicine',           'h' => 165.0, 'w' => 55.0, 'em' => '+94762223344'],
        ['username' => 'test_user',        'index' => 'MD/2023/4019', 'name' => 'Chamara Bandara',      'year' => 2, 'faculty' => 'Faculty of Medicine',           'h' => 173.0, 'w' => 69.0, 'em' => '+94787890123'],

        // Technology
        ['username' => 'athlete_tom',      'index' => 'TC/2023/3391', 'name' => 'Tom Aris',             'year' => 2, 'faculty' => 'Faculty of Technology',         'h' => 180.0, 'w' => 76.5, 'em' => '+94784445566'],
        ['username' => 'new_user',         'index' => 'TC/2022/1908', 'name' => 'Nadeesha Dilhani',     'year' => 3, 'faculty' => 'Faculty of Technology',         'h' => 163.0, 'w' => 54.0, 'em' => '+94708901234'],

        // UCSC
        ['username' => 'athlete_user',     'index' => 'CS/2022/9012', 'name' => 'Thilina Madushanka',   'year' => 3, 'faculty' => 'UCSC',                          'h' => 176.0, 'w' => 70.0, 'em' => '+94773456789'],
        ['username' => 'hasith_eranga',    'index' => 'CS/2023/1102', 'name' => 'Hasitha Eranga',       'year' => 2, 'faculty' => 'UCSC',                          'h' => 174.0, 'w' => 67.0, 'em' => '+94765678901'],
        ['username' => 'kusal_mendis',     'index' => 'CS/2021/8044', 'name' => 'Kusal Mendis',         'year' => 4, 'faculty' => 'UCSC',                          'h' => 178.0, 'w' => 73.0, 'em' => '+94712233445'],

        // Law
        ['username' => 'kamal_gunaratne',  'index' => 'LW/2021/3015', 'name' => 'Kamal Gunaratne',      'year' => 4, 'faculty' => 'Faculty of Law',                'h' => 181.0, 'w' => 78.0, 'em' => '+94714567890'],
        ['username' => 'sanath_jayasuriya','index' => 'LW/2023/2004', 'name' => 'Sanath Jayasuriya Jr', 'year' => 2, 'faculty' => 'Faculty of Law',                'h' => 179.0, 'w' => 75.0, 'em' => '+94775566778'],

        // Education
        ['username' => 'suresh_silva',     'index' => 'ED/2022/4410', 'name' => 'Suresh Silva',         'year' => 3, 'faculty' => 'Faculty of Education',          'h' => 170.0, 'w' => 65.0, 'em' => '+94781122334'],

        // Nursing
        ['username' => 'kaveesha_w',       'index' => 'NU/2023/1020', 'name' => 'Kaveesha Wijesinghe',  'year' => 2, 'faculty' => 'Faculty of Nursing',            'h' => 164.0, 'w' => 53.0, 'em' => '+94778899001'],

        // Indigenous Medicine
        ['username' => 'sanduni_j',        'index' => 'IM/2022/5510', 'name' => 'Sanduni Jayawardena',  'year' => 3, 'faculty' => 'Faculty of Indigenous Medicine','h' => 160.0, 'w' => 50.0, 'em' => '+94769988776']
    ];

    $stmtCheckAth = $db->prepare("SELECT athlete_id FROM `Athletic` WHERE `user_id` = :uid OR `index_number` = :idx");
    $stmtUpdateAth = $db->prepare("
        UPDATE `Athletic` SET 
            `user_id` = :uid,
            `index_number` = :idx,
            `full_name` = :name,
            `academic_year` = :year,
            `faculty` = :fac,
            `height` = :h,
            `weight` = :w,
            `emergency_Contact` = :em
        WHERE `athlete_id` = :aid
    ");
    $stmtInsertAth = $db->prepare("
        INSERT INTO `Athletic` (`user_id`, `index_number`, `full_name`, `academic_year`, `faculty`, `height`, `weight`, `emergency_Contact`)
        VALUES (:uid, :idx, :name, :year, :fac, :h, :w, :em)
    ");

    $athleteMap = []; // index_number => athlete_id

    foreach ($athletesToSeed as $a) {
        $uid = $userMap[$a['username']];
        $stmtCheckAth->execute([':uid' => $uid, ':idx' => $a['index']]);
        $existing = $stmtCheckAth->fetch();

        if ($existing) {
            $aid = (int)$existing['athlete_id'];
            $stmtUpdateAth->execute([
                ':uid'  => $uid,
                ':idx'  => $a['index'],
                ':name' => $a['name'],
                ':year' => $a['year'],
                ':fac'  => $a['faculty'],
                ':h'    => $a['h'],
                ':w'    => $a['w'],
                ':em'   => $a['em'],
                ':aid'  => $aid
            ]);
        } else {
            $stmtInsertAth->execute([
                ':uid'  => $uid,
                ':idx'  => $a['index'],
                ':name' => $a['name'],
                ':year' => $a['year'],
                ':fac'  => $a['faculty'],
                ':h'    => $a['h'],
                ':w'    => $a['w'],
                ':em'   => $a['em']
            ]);
            $aid = (int)$db->lastInsertId();
        }
        $athleteMap[$a['index']] = $aid;
    }
    echo "Athletes synchronized. Total in Athletic table: " . count($athleteMap) . "\n\n";

    // 3. Equipment Inventory Seeding
    echo "Seeding Equipment Inventory...\n";

    // Ensure all sports exist
    $sportsList = [
        1 => 'Football',
        2 => 'Basketball',
        3 => 'Cricket',
        4 => 'Badminton',
        5 => 'Track & Field',
        6 => 'Swimming',
        7 => 'Rugby',
        8 => 'Volleyball',
        9 => 'Tennis',
        10 => 'Table Tennis'
    ];
    $stmtSport = $db->prepare("INSERT IGNORE INTO `sports` (`sport_id`, `sport_name`) VALUES (:id, :name)");
    foreach ($sportsList as $sid => $sname) {
        $stmtSport->execute([':id' => $sid, ':name' => $sname]);
    }

    $equipmentList = [
        // Football
        ['sport_id' => 1, 'name' => 'Elite GPS Trackers (Set of 10)',     'qty' => 10, 'cond' => 'Good'],
        ['sport_id' => 1, 'name' => 'Nike Strike Training Footballs',     'qty' => 35, 'cond' => 'New'],
        ['sport_id' => 1, 'name' => 'Agility Training Hurdles Set',       'qty' => 18, 'cond' => 'Good'],
        ['sport_id' => 1, 'name' => 'Corner Flags & Spring Poles (Set 4)','qty' => 4,  'cond' => 'Needs Repair'], // Low stock & Needs Repair

        // Basketball
        ['sport_id' => 2, 'name' => 'Spalding TF-1000 Basketballs',       'qty' => 28, 'cond' => 'New'],
        ['sport_id' => 2, 'name' => 'Pro-Grade Rebounder Net',            'qty' => 4,  'cond' => 'Good'], // Low stock
        ['sport_id' => 2, 'name' => 'Heavy Duty Basketball Rim & Net',    'qty' => 3,  'cond' => 'Needs Repair'], // Low stock & Needs Repair

        // Cricket
        ['sport_id' => 3, 'name' => 'SS Ton Reserve Cricket Bats',        'qty' => 14, 'cond' => 'Good'],
        ['sport_id' => 3, 'name' => 'Kookaburra Turf Match Balls',        'qty' => 55, 'cond' => 'New'],
        ['sport_id' => 3, 'name' => 'Spring-Loaded Cricket Stumps Set',   'qty' => 5,  'cond' => 'Good'], // Low stock

        // Badminton
        ['sport_id' => 4, 'name' => 'Yonex Nanoflare Rackets',            'qty' => 22, 'cond' => 'Good'],
        ['sport_id' => 4, 'name' => 'Yonex Aerosensa Shuttlecock Tubes',  'qty' => 75, 'cond' => 'New'],
        ['sport_id' => 4, 'name' => 'Badminton Tournament Net',           'qty' => 2,  'cond' => 'Needs Repair'], // Low stock & Needs Repair

        // Track & Field
        ['sport_id' => 5, 'name' => 'Starting Blocks (Olympic Standard)', 'qty' => 8,  'cond' => 'Good'],
        ['sport_id' => 5, 'name' => 'High-Jump Pit Measurement Kit',      'qty' => 6,  'cond' => 'Good'],
        ['sport_id' => 5, 'name' => 'Aluminum Relay Batons (Set of 8)',   'qty' => 12, 'cond' => 'New'],

        // Swimming
        ['sport_id' => 6, 'name' => 'Digital Stopwatch & Pace Clocks',    'qty' => 10, 'cond' => 'Good'],
        ['sport_id' => 6, 'name' => 'Speedo Training Kickboards',         'qty' => 25, 'cond' => 'Good'],

        // Tennis
        ['sport_id' => 9, 'name' => 'Wilson Pro Staff Tennis Rackets',    'qty' => 14, 'cond' => 'Good'],
        ['sport_id' => 9, 'name' => 'Wilson Championship Ball Tubes',     'qty' => 45, 'cond' => 'New'],

        // Table Tennis
        ['sport_id' => 10,'name' => 'Stiga Pro Carbon TT Bats',            'qty' => 16, 'cond' => 'Good'],
        ['sport_id' => 10,'name' => 'Nittaku 3-Star TT Balls (Pack 72)',  'qty' => 25, 'cond' => 'New'],

        // Volleyball
        ['sport_id' => 8, 'name' => 'Mikasa V200W Official Volleyballs',  'qty' => 20, 'cond' => 'New'],
        ['sport_id' => 8, 'name' => 'Volleyball Antenna & Net Straps',    'qty' => 3,  'cond' => 'Needs Repair'] // Low stock & Needs Repair
    ];

    $stmtCheckEq = $db->prepare("SELECT equipment_id FROM `equipment` WHERE `sport_id` = :sid AND `item_name` = :name");
    $stmtUpdateEq = $db->prepare("UPDATE `equipment` SET `quantity_in_stock` = :qty, `condition_status` = :cond WHERE `equipment_id` = :id");
    $stmtInsertEq = $db->prepare("INSERT INTO `equipment` (`sport_id`, `item_name`, `quantity_in_stock`, `condition_status`) VALUES (:sid, :name, :qty, :cond)");

    $equipmentMap = []; // name => equipment_id
    foreach ($equipmentList as $eq) {
        $stmtCheckEq->execute([':sid' => $eq['sport_id'], ':name' => $eq['name']]);
        $existing = $stmtCheckEq->fetch();
        if ($existing) {
            $eid = (int)$existing['equipment_id'];
            $stmtUpdateEq->execute([':qty' => $eq['qty'], ':cond' => $eq['cond'], ':id' => $eid]);
        } else {
            $stmtInsertEq->execute([':sid' => $eq['sport_id'], ':name' => $eq['name'], ':qty' => $eq['qty'], ':cond' => $eq['cond']]);
            $eid = (int)$db->lastInsertId();
        }
        $equipmentMap[$eq['name']] = $eid;
    }
    echo "Equipment items updated/seeded: " . count($equipmentMap) . "\n\n";

    // 4. Equipment Issues Seeding (Pending, Issued, Returned)
    echo "Seeding Equipment Requests & Active Checkouts...\n";

    // Clean up older issues to start with fresh, consistent test state
    $db->exec("DELETE FROM `equipment_issue`");

    $issuesToSeed = [
        // Pending Requests (for views/tic_equipment_requests.php)
        [
            'eq' => 'Kookaburra Turf Match Balls',
            'qty' => 6,
            'rec_type' => 'Athlete',
            'idx' => 'AR/2021/8821', // Sarah Jenkins
            'coach' => null,
            'issue_date' => '2026-09-18',
            'exp_date' => '2026-09-24',
            'act_date' => null,
            'status' => 'Pending'
        ],
        [
            'eq' => 'Yonex Nanoflare Rackets',
            'qty' => 2,
            'rec_type' => 'Athlete',
            'idx' => 'CS/2022/9012', // Thilina Madushanka
            'coach' => null,
            'issue_date' => '2026-09-19',
            'exp_date' => '2026-09-26',
            'act_date' => null,
            'status' => 'Pending'
        ],
        [
            'eq' => 'Nike Strike Training Footballs',
            'qty' => 4,
            'rec_type' => 'Athlete',
            'idx' => 'SC/2022/1042', // Kasun Perera
            'coach' => null,
            'issue_date' => '2026-09-19',
            'exp_date' => '2026-09-25',
            'act_date' => null,
            'status' => 'Pending'
        ],
        [
            'eq' => 'Spalding TF-1000 Basketballs',
            'qty' => 3,
            'rec_type' => 'Coach',
            'idx' => null,
            'coach' => 'coach_pradeep',
            'issue_date' => '2026-09-20',
            'exp_date' => '2026-09-27',
            'act_date' => null,
            'status' => 'Pending'
        ],
        [
            'eq' => 'Wilson Pro Staff Tennis Rackets',
            'qty' => 2,
            'rec_type' => 'Athlete',
            'idx' => 'MD/2022/1928', // Elena Rodriguez
            'coach' => null,
            'issue_date' => '2026-09-20',
            'exp_date' => '2026-09-25',
            'act_date' => null,
            'status' => 'Pending'
        ],
        [
            'eq' => 'Digital Stopwatch & Pace Clocks',
            'qty' => 2,
            'rec_type' => 'Athlete',
            'idx' => 'TC/2023/3391', // Tom Aris
            'coach' => null,
            'issue_date' => '2026-09-20',
            'exp_date' => '2026-09-28',
            'act_date' => null,
            'status' => 'Pending'
        ],

        // Active Check-outs (status = 'Issued' for views/tic_checkout_log.php)
        [
            'eq' => 'Elite GPS Trackers (Set of 10)',
            'qty' => 2,
            'rec_type' => 'Coach',
            'idx' => null,
            'coach' => 'coach_sunil',
            'issue_date' => '2026-09-15',
            'exp_date' => '2026-09-23',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'Agility Training Hurdles Set',
            'qty' => 1,
            'rec_type' => 'Athlete',
            'idx' => 'SC/2021/2049', // Dinuka Fernando
            'coach' => null,
            'issue_date' => '2026-09-16',
            'exp_date' => '2026-09-24',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'Spalding TF-1000 Basketballs',
            'qty' => 4,
            'rec_type' => 'Athlete',
            'idx' => 'MG/2023/4420', // Marcus Thorne
            'coach' => null,
            'issue_date' => '2026-09-17',
            'exp_date' => '2026-09-24',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'SS Ton Reserve Cricket Bats',
            'qty' => 2,
            'rec_type' => 'Coach',
            'idx' => null,
            'coach' => 'coach_anura',
            'issue_date' => '2026-09-18',
            'exp_date' => '2026-09-25',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'Starting Blocks (Olympic Standard)',
            'qty' => 2,
            'rec_type' => 'Athlete',
            'idx' => 'AR/2023/5021', // Amali Wickramasinghe
            'coach' => null,
            'issue_date' => '2026-09-18',
            'exp_date' => '2026-09-26',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'Yonex Nanoflare Rackets',
            'qty' => 3,
            'rec_type' => 'Athlete',
            'idx' => 'LW/2021/3015', // Kamal Gunaratne
            'coach' => null,
            'issue_date' => '2026-09-19',
            'exp_date' => '2026-09-25',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'Stiga Pro Carbon TT Bats',
            'qty' => 4,
            'rec_type' => 'Athlete',
            'idx' => 'MG/2022/2204', // Tamasha Sevindi
            'coach' => null,
            'issue_date' => '2026-09-19',
            'exp_date' => '2026-09-26',
            'act_date' => null,
            'status' => 'Issued'
        ],
        [
            'eq' => 'Pro-Grade Rebounder Net',
            'qty' => 1,
            'rec_type' => 'Athlete',
            'idx' => 'CS/2023/1102', // Hasitha Eranga
            'coach' => null,
            'issue_date' => '2026-09-20',
            'exp_date' => '2026-09-27',
            'act_date' => null,
            'status' => 'Issued'
        ],

        // Returned Records (History)
        [
            'eq' => 'Wilson Championship Ball Tubes',
            'qty' => 2,
            'rec_type' => 'Coach',
            'idx' => null,
            'coach' => 'coach_pradeep',
            'issue_date' => '2026-09-10',
            'exp_date' => '2026-09-15',
            'act_date' => '2026-09-14',
            'status' => 'Returned'
        ],
        [
            'eq' => 'Nike Strike Training Footballs',
            'qty' => 5,
            'rec_type' => 'Athlete',
            'idx' => 'SC/2022/1042',
            'coach' => null,
            'issue_date' => '2026-09-11',
            'exp_date' => '2026-09-16',
            'act_date' => '2026-09-15',
            'status' => 'Returned'
        ]
    ];

    $stmtInsertIssue = $db->prepare("
        INSERT INTO `equipment_issue` (
            `equipment_id`, `quantity`, `athletic_id`, `recipient_type`, `recipient_name`, `issue_date`, `expected_return_date`, `actual_return_date`, `status`
        ) VALUES (
            :eq_id, :qty, :ath_id, :rec_type, :rec_name, :issue_date, :exp_date, :act_date, :status
        )
    ");

    foreach ($issuesToSeed as $iss) {
        $eid = $equipmentMap[$iss['eq']];
        $athId = !empty($iss['idx']) ? $athleteMap[$iss['idx']] : null;
        $stmtInsertIssue->execute([
            ':eq_id'      => $eid,
            ':qty'        => $iss['qty'],
            ':ath_id'     => $athId,
            ':rec_type'   => $iss['rec_type'],
            ':rec_name'   => $iss['coach'],
            ':issue_date' => $iss['issue_date'],
            ':exp_date'   => $iss['exp_date'],
            ':act_date'   => $iss['act_date'],
            ':status'     => $iss['status']
        ]);
    }
    echo "Equipment issues seeded successfully. Total: " . count($issuesToSeed) . "\n\n";

    // 5. Fixtures & Tournaments Seeding
    echo "Seeding Match Fixtures & Squad Nominations...\n";

    // Clean fixtures & squads
    $db->exec("DELETE FROM `match_squads`");
    $db->exec("DELETE FROM `fixtures`");

    $fixturesToSeed = [
        // Inter-University (UOC Combined vs External Universities)
        [
            'sport_id'     => 1, // Football
            'facility_id'  => 2, // Colombo University Grounds
            'opponent'     => 'University of Peradeniya',
            'type'         => 'Inter-University',
            'home'         => 'University of Colombo',
            'date'         => '2026-09-25',
            'time'         => '15:30:00',
            'squads'       => [
                ['idx' => 'SC/2022/1042', 'status' => 'Pending'],  // Kasun Perera (Science)
                ['idx' => 'AR/2021/8821', 'status' => 'Pending'],  // Sarah Jenkins (Arts)
                ['idx' => 'MG/2023/4420', 'status' => 'Pending'],  // Marcus Thorne (Management)
                ['idx' => 'MD/2022/1928', 'status' => 'Pending'],  // Elena Rodriguez (Medicine)
                ['idx' => 'TC/2023/3391', 'status' => 'Pending'],  // Tom Aris (Technology)
                ['idx' => 'CS/2022/9012', 'status' => 'Approved'], // Thilina Madushanka (UCSC)
                ['idx' => 'LW/2021/3015', 'status' => 'Approved']  // Kamal Gunaratne (Law)
            ]
        ],
        [
            'sport_id'     => 2, // Basketball
            'facility_id'  => 1, // Main Gymnasium
            'opponent'     => 'University of Moratuwa',
            'type'         => 'Inter-University',
            'home'         => 'University of Colombo',
            'date'         => '2026-09-28',
            'time'         => '17:00:00',
            'squads'       => [
                ['idx' => 'MG/2023/4420', 'status' => 'Approved'],
                ['idx' => 'TC/2023/3391', 'status' => 'Approved'],
                ['idx' => 'CS/2023/1102', 'status' => 'Pending'],
                ['idx' => 'SC/2021/2049', 'status' => 'Pending']
            ]
        ],
        [
            'sport_id'     => 3, // Cricket
            'facility_id'  => 2, // Colombo University Grounds
            'opponent'     => 'University of Kelaniya',
            'type'         => 'Inter-University',
            'home'         => 'University of Colombo',
            'date'         => '2026-10-02',
            'time'         => '09:30:00',
            'squads'       => [
                ['idx' => 'CS/2021/8044', 'status' => 'Pending'], // Kusal Mendis (UCSC)
                ['idx' => 'AR/2022/6630', 'status' => 'Pending'], // Dilshan Pathirana (Arts)
                ['idx' => 'MG/2021/7701', 'status' => 'Pending']  // Ruwan Weerasinghe (Management)
            ]
        ],
        [
            'sport_id'     => 4, // Badminton
            'facility_id'  => 3, // Indoor Sports Arena
            'opponent'     => 'Univ. of Sri Jayewardenepura',
            'type'         => 'Inter-University',
            'home'         => 'University of Colombo',
            'date'         => '2026-10-05',
            'time'         => '14:00:00',
            'squads'       => [
                ['idx' => 'CS/2022/9012', 'status' => 'Pending'],
                ['idx' => 'LW/2021/3015', 'status' => 'Pending']
            ]
        ],
        [
            'sport_id'     => 5, // Track & Field
            'facility_id'  => 2, // Colombo University Grounds
            'opponent'     => 'University of Ruhuna',
            'type'         => 'Inter-University',
            'home'         => 'University of Colombo',
            'date'         => '2026-10-08',
            'time'         => '08:00:00',
            'squads'       => [
                ['idx' => 'AR/2023/5021', 'status' => 'Approved'],
                ['idx' => 'TC/2023/3391', 'status' => 'Approved'],
                ['idx' => 'SC/2022/1042', 'status' => 'Approved']
            ]
        ],

        // Inter-Faculty Championship (Internal Faculty Competitions)
        [
            'sport_id'     => 1, // Football
            'facility_id'  => 2, // Colombo University Grounds
            'opponent'     => 'Faculty of Arts',
            'type'         => 'Inter-Faculty',
            'home'         => 'Faculty of Science',
            'date'         => '2026-09-24',
            'time'         => '16:00:00',
            'squads'       => [
                ['idx' => 'SC/2022/1042', 'status' => 'Pending'], // Kasun Perera (Science)
                ['idx' => 'SC/2021/2049', 'status' => 'Pending'], // Dinuka Fernando (Science)
                ['idx' => 'SC/2023/3305', 'status' => 'Pending']  // Malith Perera (Science)
            ]
        ],
        [
            'sport_id'     => 3, // Cricket
            'facility_id'  => 2, // Colombo University Grounds
            'opponent'     => 'Faculty of Management & Finance',
            'type'         => 'Inter-Faculty',
            'home'         => 'UCSC',
            'date'         => '2026-09-27',
            'time'         => '14:30:00',
            'squads'       => [
                ['idx' => 'CS/2022/9012', 'status' => 'Pending'], // Thilina Madushanka (UCSC)
                ['idx' => 'CS/2023/1102', 'status' => 'Pending'], // Hasitha Eranga (UCSC)
                ['idx' => 'CS/2021/8044', 'status' => 'Pending']  // Kusal Mendis (UCSC)
            ]
        ],
        [
            'sport_id'     => 2, // Basketball
            'facility_id'  => 1, // Main Gymnasium
            'opponent'     => 'Faculty of Technology',
            'type'         => 'Inter-Faculty',
            'home'         => 'Faculty of Medicine',
            'date'         => '2026-09-29',
            'time'         => '16:30:00',
            'squads'       => [
                ['idx' => 'MD/2022/1928', 'status' => 'Pending'], // Elena Rodriguez (Medicine)
                ['idx' => 'MD/2023/4019', 'status' => 'Pending']  // Chamara Bandara (Medicine)
            ]
        ],
        [
            'sport_id'     => 4, // Badminton
            'facility_id'  => 3, // Indoor Sports Arena
            'opponent'     => 'Faculty of Science',
            'type'         => 'Inter-Faculty',
            'home'         => 'Faculty of Law',
            'date'         => '2026-10-01',
            'time'         => '15:00:00',
            'squads'       => [
                ['idx' => 'LW/2021/3015', 'status' => 'Pending'], // Kamal Gunaratne (Law)
                ['idx' => 'LW/2023/2004', 'status' => 'Pending']  // Sanath Jayasuriya Jr (Law)
            ]
        ],
        [
            'sport_id'     => 10, // Table Tennis
            'facility_id'  => 3, // Indoor Sports Arena
            'opponent'     => 'UCSC',
            'type'         => 'Inter-Faculty',
            'home'         => 'Faculty of Technology',
            'date'         => '2026-10-03',
            'time'         => '16:00:00',
            'squads'       => [
                ['idx' => 'TC/2023/3391', 'status' => 'Pending'], // Tom Aris (Tech)
                ['idx' => 'TC/2022/1908', 'status' => 'Pending']  // Nadeesha Dilhani (Tech)
            ]
        ],

        // Internal Match (Trial / Selection)
        [
            'sport_id'     => 1, // Football
            'facility_id'  => 2, // Colombo University Grounds
            'opponent'     => 'UOC Varsity B (Development Squad)',
            'type'         => 'Internal',
            'home'         => 'UOC Varsity A',
            'date'         => '2026-09-26',
            'time'         => '07:30:00',
            'squads'       => [
                ['idx' => 'SC/2022/1042', 'status' => 'Approved'],
                ['idx' => 'AR/2021/8821', 'status' => 'Approved']
            ]
        ]
    ];

    $stmtInsertFix = $db->prepare("
        INSERT INTO `fixtures` (`sport_id`, `facility_id`, `opponent`, `fixture_type`, `home_team`, `fixture_date`, `fixture_time`)
        VALUES (:sport_id, :facility_id, :opponent, :type, :home, :date, :time)
    ");
    $stmtInsertSquad = $db->prepare("
        INSERT INTO `match_squads` (`fixture_id`, `athlete_id`, `tic_approval_status`)
        VALUES (:fix_id, :ath_id, :status)
    ");

    $totalSquadCount = 0;
    foreach ($fixturesToSeed as $fix) {
        $stmtInsertFix->execute([
            ':sport_id'     => $fix['sport_id'],
            ':facility_id'  => $fix['facility_id'],
            ':opponent'     => $fix['opponent'],
            ':type'         => $fix['type'],
            ':home'         => $fix['home'],
            ':date'         => $fix['date'],
            ':time'         => $fix['time']
        ]);
        $fixId = (int)$db->lastInsertId();

        foreach ($fix['squads'] as $sq) {
            $athId = $athleteMap[$sq['idx']];
            $stmtInsertSquad->execute([
                ':fix_id' => $fixId,
                ':ath_id' => $athId,
                ':status' => $sq['status']
            ]);
            $totalSquadCount++;
        }
    }
    echo "Fixtures seeded: " . count($fixturesToSeed) . " | Total squad player nominations: " . $totalSquadCount . "\n\n";

    // 6. Facility Bookings Seeding
    echo "Seeding Facility Bookings...\n";
    $db->exec("DELETE FROM `facility_bookings`");

    $bookingsToSeed = [
        ['fac' => 2, 'sport' => 1, 'date' => '2026-09-22', 'time' => '15:30:00', 'dur' => 120, 'status' => 'Pending'],  // Grounds - Football
        ['fac' => 1, 'sport' => 2, 'date' => '2026-09-23', 'time' => '17:00:00', 'dur' => 90,  'status' => 'Pending'],  // Gym - Basketball
        ['fac' => 3, 'sport' => 4, 'date' => '2026-09-24', 'time' => '08:30:00', 'dur' => 120, 'status' => 'Pending'],  // Arena - Badminton
        ['fac' => 5, 'sport' => 6, 'date' => '2026-09-25', 'time' => '07:00:00', 'dur' => 90,  'status' => 'Pending'],  // Pool - Swimming
        ['fac' => 2, 'sport' => 3, 'date' => '2026-09-26', 'time' => '14:00:00', 'dur' => 180, 'status' => 'Approved'], // Grounds - Cricket
        ['fac' => 4, 'sport' => 9, 'date' => '2026-09-27', 'time' => '16:30:00', 'dur' => 90,  'status' => 'Approved']  // Courts - Tennis
    ];

    $stmtInsertBooking = $db->prepare("
        INSERT INTO `facility_bookings` (`facility_id`, `sport_id`, `booking_date`, `start_time`, `duration_minutes`, `approval_status`)
        VALUES (:fac, :sport, :date, :time, :dur, :status)
    ");
    foreach ($bookingsToSeed as $b) {
        $stmtInsertBooking->execute([
            ':fac'    => $b['fac'],
            ':sport'  => $b['sport'],
            ':date'   => $b['date'],
            ':time'   => $b['time'],
            ':dur'    => $b['dur'],
            ':status' => $b['status']
        ]);
    }
    echo "Facility bookings seeded: " . count($bookingsToSeed) . "\n\n";

    // 7. Announcements and Notifications
    echo "Seeding Announcements & Notifications...\n";
    $db->exec("DELETE FROM `announcements`");
    $db->exec("DELETE FROM `notfications`");

    $ticUserId = $userMap['tic_admin'];
    $presUserId = $userMap['pres_admin'];

    $announcements = [
        [
            'pub' => $ticUserId,
            'title' => 'Inter-Faculty Championship 2026 Schedule Announced',
            'content' => 'The annual Inter-Faculty Championship matches will commence on September 24th, 2026. Faculty teams in Football, Cricket, Basketball, and Badminton must finalize their squads with the TIC office before match day.',
            'date' => '2026-09-18 10:00:00'
        ],
        [
            'pub' => $presUserId,
            'title' => 'Varsity Sports Equipment Restocked for Season 2026',
            'content' => 'The Amalgamated Club has procured new official match gear including Yonex shuttlecocks, Kookaburra cricket turf balls, and Nike match footballs. Coaches and athletes may submit equipment checkout requests online.',
            'date' => '2026-09-19 14:30:00'
        ],
        [
            'pub' => $ticUserId,
            'title' => 'Facility Booking Guidelines for Colombo University Grounds & Gym',
            'content' => 'All ground and gymnasium allocations for varsity and faculty practices must be submitted at least 48 hours in advance through the USMS portal for TIC review and approval.',
            'date' => '2026-09-20 09:15:00'
        ]
    ];

    $stmtInsertAnn = $db->prepare("
        INSERT INTO `announcements` (`published_by_user_id`, `title`, `content`, `created_at`)
        VALUES (:pub, :title, :content, :date)
    ");
    foreach ($announcements as $ann) {
        $stmtInsertAnn->execute([
            ':pub'   => $ann['pub'],
            ':title' => $ann['title'],
            ':content' => $ann['content'],
            ':date'  => $ann['date']
        ]);
    }

    $notifications = [
        [
            'user' => $ticUserId,
            'message' => 'New equipment request pending: Kookaburra Turf Match Balls requested by Sarah Jenkins.',
            'is_read' => 0,
            'date' => '2026-09-19 11:20:00'
        ],
        [
            'user' => $ticUserId,
            'message' => 'New squad nominated for Inter-Faculty Football: Faculty of Science vs Faculty of Arts.',
            'is_read' => 0,
            'date' => '2026-09-20 08:45:00'
        ],
        [
            'user' => $ticUserId,
            'message' => 'New ground reservation request pending for Colombo University Grounds (Football training).',
            'is_read' => 0,
            'date' => '2026-09-20 10:30:00'
        ]
    ];

    $stmtInsertNotif = $db->prepare("
        INSERT INTO `notfications` (`user_id`, `message`, `is_read`, `created_at`)
        VALUES (:uid, :msg, :read, :date)
    ");
    foreach ($notifications as $n) {
        $stmtInsertNotif->execute([
            ':uid'  => $n['user'],
            ':msg'  => $n['message'],
            ':read' => $n['is_read'],
            ':date' => $n['date']
        ]);
    }
    echo "Announcements and Notifications seeded.\n\n";

    // 8. Training Sessions & Attendance Seeding
    echo "Seeding Training Sessions & Attendance Records...\n";
    $db->exec("DELETE FROM `training_attendance`");
    $db->exec("DELETE FROM `training_sessions`");

    $trainingSessionsToSeed = [
        [
            'id' => 1,
            'sport' => 1, // Football
            'facility' => 2, // Grounds
            'date' => date('Y-m-d'), // Today
            'time' => '16:30:00',
            'duration' => '01:30:00',
            'type' => 'Practice',
            'focus' => 'High-Press & Set Pieces',
            'notes' => 'Match simulation with referee rehearsal before SLUG qualifier.'
        ],
        [
            'id' => 2,
            'sport' => 1, // Football
            'facility' => 1, // Gym
            'date' => date('Y-m-d', strtotime('-1 day')),
            'time' => '06:00:00',
            'duration' => '01:30:00',
            'type' => 'Conditioning',
            'focus' => 'Plyometrics & Conditioning',
            'notes' => 'Early morning core mobility, resistance band drills, and squad weigh-in.'
        ],
        [
            'id' => 3,
            'sport' => 2, // Basketball
            'facility' => 1, // Gym
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '15:30:00',
            'duration' => '02:00:00',
            'type' => 'Tactical',
            'focus' => 'Zone Defense & Fast Breaks',
            'notes' => 'Full court press transition analysis and perimeter shooting drills.'
        ],
        [
            'id' => 4,
            'sport' => 3, // Cricket
            'facility' => 2, // Grounds
            'date' => date('Y-m-d', strtotime('-4 days')),
            'time' => '16:00:00',
            'duration' => '02:00:00',
            'type' => 'Practice',
            'focus' => 'Spin Bowling & Death Overs',
            'notes' => 'Target yorker practice and middle-order acceleration under game scenarios.'
        ],
        [
            'id' => 5,
            'sport' => 1, // Football
            'facility' => 2, // Grounds
            'date' => date('Y-m-d', strtotime('-6 days')),
            'time' => '17:00:00',
            'duration' => '01:30:00',
            'type' => 'Conditioning',
            'focus' => 'Counter-Attack & Crossing',
            'notes' => 'Wing play combinations and finishing from edge-of-the-box cutbacks.'
        ]
    ];

    $stmtInsertSession = $db->prepare("
        INSERT INTO `training_sessions` (`session_id`, `sport_id`, `facility_id`, `session_date`, `start_time`, `duration_minutes`, `session_type`, `focus_area`, `notes`)
        VALUES (:id, :sport, :facility, :date, :time, :duration, :type, :focus, :notes)
    ");
    foreach ($trainingSessionsToSeed as $ts) {
        $stmtInsertSession->execute([
            ':id'       => $ts['id'],
            ':sport'    => $ts['sport'],
            ':facility' => $ts['facility'],
            ':date'     => $ts['date'],
            ':time'     => $ts['time'],
            ':duration' => $ts['duration'],
            ':type'     => $ts['type'],
            ':focus'    => $ts['focus'],
            ':notes'    => $ts['notes']
        ]);
    }

    // Seed Attendance for these sessions
    $allAthleteIds = $db->query("SELECT athlete_id FROM `Athletic` LIMIT 20")->fetchAll(PDO::FETCH_COLUMN);
    $stmtInsertAttendance = $db->prepare("
        INSERT INTO `training_attendance` (`session_id`, `athlete_id`, `status`)
        VALUES (:sess, :ath, :status)
    ");
    $statuses = ['Present', 'Present', 'Present', 'Present', 'Excused', 'Absent'];
    $attCount = 0;
    foreach ($trainingSessionsToSeed as $ts) {
        foreach ($allAthleteIds as $idx => $athId) {
            $st = $statuses[($idx + $ts['id']) % count($statuses)];
            $stmtInsertAttendance->execute([
                ':sess'   => $ts['id'],
                ':ath'    => $athId,
                ':status' => $st
            ]);
            $attCount++;
        }
    }
    echo "Training sessions seeded: " . count($trainingSessionsToSeed) . " | Attendance logs: " . $attCount . "\n\n";

    // 9. Match Results Seeding
    echo "Seeding Match Results...\n";
    $db->exec("DELETE FROM `match_results`");
    $completedFixture = $db->query("SELECT fixture_id FROM `fixtures` LIMIT 1")->fetchColumn();
    if ($completedFixture) {
        $stmtInsertResult = $db->prepare("
            INSERT INTO `match_results` (`fixture_id`, `scores`, `scorers`, `match_report`)
            VALUES (:fix, :scores, :scorers, :report)
        ");
        $stmtInsertResult->execute([
            ':fix'     => $completedFixture,
            ':scores'  => '2 - 1',
            ':scorers' => 'Kavindu Perera (34\'), Sadeera Samarawickrama (78\')',
            ':report'  => 'Dominant second-half performance with aggressive pressing secured victory in the Inter-Faculty quarter-final match.'
        ]);
        echo "Match results seeded successfully.\n\n";
    }

    echo "=== ALL DATABASE UPDATES AND DEMO DATA SEEDED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "Error during database migration: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

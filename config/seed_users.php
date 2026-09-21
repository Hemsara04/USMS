<?php
/**
 * Test Accounts Seeder for USMS
 * Run once to generate default login credentials
 */

require_once __DIR__ . '/../models/User.php';

$userModel = new User();

$defaultUsers = [
    [
        'username' => 'tic_admin',
        'email'    => 'tic@uoc.ac.lk',
        'password' => 'password123',
        'role'     => 'TIC'
    ],
    [
        'username' => 'med_officer',
        'email'    => 'medical@uoc.ac.lk',
        'password' => 'password123',
        'role'     => 'Medical_Officer'
    ],
    [
        'username' => 'athlete_user',
        'email'    => 'athlete@uoc.ac.lk',
        'password' => 'password123',
        'role'     => 'Athlete'
    ],
    [
        'username' => 'pres_admin',
        'email'    => 'president@uoc.ac.lk',
        'password' => 'password123',
        'role'     => 'President'
    ]
];

echo "<h3>USMS Default User Seeder</h3>";

foreach ($defaultUsers as $u) {
    if (!$userModel->emailExists($u['email']) && !$userModel->usernameExists($u['username'])) {
        $ok = $userModel->create($u['username'], $u['email'], $u['password'], $u['role']);
        if ($ok) {
            echo "<p style='color:green;'>✅ Created user: <strong>{$u['username']}</strong> ({$u['email']}) [Role: {$u['role']}] - Password: <code>{$u['password']}</code></p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to create user: {$u['username']}</p>";
        }
    } else {
        echo "<p style='color:blue;'>ℹ️ User already exists: <strong>{$u['username']}</strong> ({$u['email']})</p>";
    }
}

echo "<hr><p><a href='../views/login.php'>Go to Login Page &rarr;</a></p>";

<?php
$localhost = "localhost";
$username='root';
$password='';
$database='bank_app';

$connection = new mysqli($localhost, $username, $password, $database);

if ($connection->connect_error) {
    die("not connected");
} else {
    echo "connected<br>";
}

// Define your admin details
$admin = [
    'first_name'      => 'Admin',
    'last_name'       => 'User',
    'email'           => 'ogunladeadebayopeter@gmail.com',
    'password'        => password_hash('admin123', PASSWORD_DEFAULT),
    'address'         => 'Admin HQ',
    'phone_number'    => '08166223968',
    'gender'          => 'male',
    'role'            => 'admin',
    'created_at'      => date('Y-m-d H:i:s'),
    'updated_at'      => date('Y-m-d H:i:s'),
    'profile_picture' => 'admin.png'
];

// Check if admin exists by email
$checkStmt = $connection->prepare("SELECT user_id FROM bank_table WHERE email = ?");
$checkStmt->bind_param('s', $admin['email']);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "Admin already exists.<br>";
} else {
    $insertStmt = $connection->prepare(
        "INSERT INTO bank_table 
        (first_name, last_name, email, password, address, phone_number, gender, role, created_at, updated_at, profile_picture) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertStmt->bind_param(
        'sssssssssss',
        $admin['first_name'],
        $admin['last_name'],
        $admin['email'],
        $admin['password'],
        $admin['address'],
        $admin['phone_number'],
        $admin['gender'],
        $admin['role'],
        $admin['created_at'],
        $admin['updated_at'],
        $admin['profile_picture']
    );

    if ($insertStmt->execute()) {
        echo "Admin seeded successfully.<br>";
    } else {
        echo "Error seeding admin: " . $insertStmt->error . "<br>";
    }
    $insertStmt->close();
}
$checkStmt->close();
$connection->close();
?>
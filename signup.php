<?php
require 'db_connect.php';

// CORS (needed when front-end is at 127.0.0.1:5500)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://127.0.0.1:5500' || $origin === 'http://localhost:5500') {
  header("Access-Control-Allow-Origin: $origin");
  header("Access-Control-Allow-Methods: POST, OPTIONS");
  header("Access-Control-Allow-Headers: Content-Type");
}
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']); exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($fullname === '' || $email === '' || $password === '') {
  http_response_code(422);
  echo json_encode(['status'=>'error','message'=>'Please fill in all required fields.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['status'=>'error','message'=>'Invalid email address.']); exit;
}

// Prepared statements (safer)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  http_response_code(409);
  echo json_encode(['status'=>'error','message'=>'Email already registered!']); 
  $stmt->close(); $conn->close(); exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?,?,?)");
$stmt->bind_param('sss', $fullname, $email, $hash);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => " Welcome to Janjos Resort! Your account has been created successfully."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => " Something went wrong while saving your account. Please try again."
    ]);
}


$stmt->close(); $conn->close();

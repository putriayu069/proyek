<?php

$baseUrl = 'http://127.0.0.1:8000/api';

echo "=== TEST API AUTHENTICATION & CART ===\n\n";

// 1. TEST REGISTER
echo "1. Testing Register...\n";
$registerData = [
    'name' => 'Test User ' . time(),
    'email' => 'test' . time() . '@gmail.com',
    'password' => 'password123'
];

$ch = curl_init("$baseUrl/register");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

$registerResult = json_decode($response, true);
$token = $registerResult['token'] ?? null;

if (!$token) {
    echo "❌ Register gagal, tidak ada token!\n";
    exit;
}

echo "✅ Register berhasil! Token: $token\n\n";

// 2. TEST LOGIN
echo "2. Testing Login...\n";
$loginData = [
    'email' => $registerData['email'],
    'password' => $registerData['password']
];

$ch = curl_init("$baseUrl/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

$loginResult = json_decode($response, true);
$token = $loginResult['token'] ?? null;

if (!$token) {
    echo "❌ Login gagal!\n";
    exit;
}

echo "✅ Login berhasil! Token: $token\n\n";

// 3. TEST GET PROFILE
echo "3. Testing Get Profile...\n";
$ch = curl_init("$baseUrl/profile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

// 4. TEST ADD TO CART (pastikan barang_id 1 ada di database)
echo "4. Testing Add to Cart...\n";
$cartData = [
    'barang_id' => 1,
    'quantity' => 2
];

$ch = curl_init("$baseUrl/cart/add");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cartData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

// 5. TEST GET CART
echo "5. Testing Get Cart...\n";
$ch = curl_init("$baseUrl/cart");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

echo "=== TEST SELESAI ===\n";
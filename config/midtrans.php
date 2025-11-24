<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'), // Opsional, tapi disarankan
    'client_key'  => env('MIDTRANS_CLIENT_KEY'),
    'server_key'  => env('MIDTRANS_SERVER_KEY'),

    // Set true untuk Production, false untuk Sandbox (Development)
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    
    // Set true untuk mengaktifkan sanitasi Midtrans
    'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true), 
    
    // Set true untuk mengaktifkan 3D Secure
    'is_3ds'        => env('MIDTRANS_IS_3DS', true), 
];
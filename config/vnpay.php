<?php

return [
    'tmnCode' => env('VNP_TMNCODE', 'VAAJN51S'),
    'hashSecret' => env('VNP_HASHSECRET', 'UNOBMR165GLWAXUC51RO1I89FWIBH6V8'),

    'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'returnUrl' => env('VNP_RETURN_URL', 'http://localhost:8000/api/v1/payments/vnpay-return'),
    'clientSuccessUrl' => env('VNP_CLIENT_SUCCESS_URL', 'http://localhost:5173/payments/payment-success'),
    'clientFailedUrl' => env('VNP_CLIENT_FAILED_URL', 'http://localhost:5173/payments/payment-failed'),
    'version' => '2.1.0',
    'command' => 'pay',
    'currCode' => 'VND',
    'locale' => 'vn',
];

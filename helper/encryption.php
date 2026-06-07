<?php

function encrypt(String $plaintext): String
{
    $cipher = 'AES-256-CBC'; // cipher yang dipakai
    $key = 'perbankan2026'; // key untuk enkripsi dan dekripsi

    // Generate 32-byte key
    $secretKey = hash('sha256', $key, true);

    // Generate IV sesuai panjang cipher
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);

    // Enkripsi
    $encrypted = openssl_encrypt(
        $plaintext,
        $cipher,
        $secretKey,
        OPENSSL_RAW_DATA,
        $iv
    );

    // Gabungkan IV + ciphertext lalu encode base64
    return base64_encode($iv . $encrypted);
}

function decrypt(String $encryptedData): String
{
    $cipher = 'AES-256-CBC';
    $key = 'perbankan2026';

    // Generate 32-byte key
    $secretKey = hash('sha256', $key, true);

    $data = base64_decode($encryptedData);

    $ivLength = openssl_cipher_iv_length($cipher);

    // Ambil IV dan ciphertext
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);

    return openssl_decrypt(
        $ciphertext,
        $cipher,
        $secretKey,
        OPENSSL_RAW_DATA,
        $iv
    );
}

/*

// Contoh penggunaan

// Note. 026 merupakan kode bank kita + 10 digit random
$plaintext = '0261234567890';

$encrypted = encrypt($plaintext);
echo "Encrypted: " . $encrypted . PHP_EOL ."<br>";

$decrypted = decrypt($encrypted);
echo "Decrypted: " . $decrypted . PHP_EOL;

*/
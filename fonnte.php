<?php
require_once __DIR__ . "/config.php";

function normalizeWhatsapp($number) {
    $number = trim((string)$number);
    $number = preg_replace('/[^0-9+]/', '', $number);

    if (str_starts_with($number, '+62')) {
        $number = '0' . substr($number, 3);
    } elseif (str_starts_with($number, '62')) {
        $number = '0' . substr($number, 2);
    }

    return $number;
}

function sendFonnteMessage($target, $message) {
    global $FONNTE_TOKEN;

    if (!$FONNTE_TOKEN || $FONNTE_TOKEN === "ISI_TOKEN_FONNTE_DI_SINI") {
        return [
            "success" => false,
            "message" => "Token Fonnte belum diisi."
        ];
    }

    $target = normalizeWhatsapp($target);

    if ($target === '') {
        return [
            "success" => false,
            "message" => "Nomor WhatsApp kosong."
        ];
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => [
            "target" => $target,
            "message" => $message,
            "countryCode" => "62"
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: " . $FONNTE_TOKEN
        ]
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlError) {
        return [
            "success" => false,
            "message" => "Fonnte cURL error: " . $curlError
        ];
    }

    $decoded = json_decode($response, true);

    return [
        "success" => ($httpCode >= 200 && $httpCode < 300 && is_array($decoded) && !empty($decoded["status"])),
        "message" => $decoded["reason"] ?? ($decoded["message"] ?? "Respons Fonnte diterima."),
        "response" => $decoded,
        "http_code" => $httpCode
    ];
}

function sendRegistrationNotification($name, $whatsapp) {
    $message = "Halo {$name}! 👋\n\n"
             . "Nomor WhatsApp kamu berhasil diverifikasi.\n"
             . "Pendaftaran akun berhasil dan akun kamu sudah bisa digunakan untuk login.\n\n"
             . "Terima kasih.";
    return sendFonnteMessage($whatsapp, $message);
}

function sendLoginNotification($name, $whatsapp) {
    $time = date("d-m-Y H:i:s");
    $message = "Halo {$name}! 👋\n\n"
             . "Login berhasil ke Sistem Manajemen User.\n"
             . "Waktu: {$time} WIB\n\n"
             . "Jika ini bukan aktivitas kamu, segera hubungi admin.";
    return sendFonnteMessage($whatsapp, $message);
}
?>

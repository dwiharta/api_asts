VERIFIKASI WHATSAPP REGISTRASI - FONNTE
========================================

Alur registrasi sekarang:
1. User mengisi nama, email, nomor WhatsApp aktif, password.
2. Server mengecek format dan duplikasi email/nomor.
3. Server membuat OTP 6 digit dan mengirimnya melalui Fonnte.
4. User memasukkan OTP yang diterima di WhatsApp.
5. Hanya jika OTP benar dan belum kedaluwarsa, akun user dibuat.
6. Setelah akun dibuat, WhatsApp menerima pesan konfirmasi pendaftaran.

Nomor WhatsApp disebut "aktual" karena kepemilikan nomor diverifikasi dengan OTP.
Sekadar menulis nomor 08xxxxxxxxxx tidak cukup untuk membuat akun.

PENGATURAN FONNTE
-----------------
1. Buka config.php.
2. Isi:
   $FONNTE_TOKEN = "TOKEN_DEVICE_FONNTE_KAMU";
3. Pastikan PHP/XAMPP mengaktifkan cURL.
4. Device Fonnte harus terhubung dan siap mengirim WhatsApp.

JANGAN memasukkan token Fonnte ke HTML/JavaScript.
Token hanya boleh berada di file PHP server.

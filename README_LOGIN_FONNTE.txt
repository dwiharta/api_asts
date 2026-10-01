UJIAN ASTS - LOGIN USER & ADMIN

1. Extract folder ujian_asts ke C:\xampp\htdocs\
2. Pastikan Apache dan MySQL aktif.
3. Import database_auth.sql lewat phpMyAdmin.
4. Jika database lama sudah memiliki tabel accounts tanpa kolom role, jalankan:
   ALTER TABLE accounts ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';
5. Atur koneksi database di config.php bila diperlukan.
6. Isi token Fonnte di config.php jika ingin notifikasi WhatsApp.
7. Buka: http://localhost/ujian_asts/login.php

LOGIN USER
- Bisa dibuat lewat register.php.
- Semua akun hasil register otomatis mempunyai role: user.
- Setelah login diarahkan ke user_dashboard.php.

LOGIN ADMIN
- Email: admin@ujianasts.local
- Password: Admin123!
- Setelah login diarahkan ke index.php.
- Dashboard CRUD admin dilindungi role admin.

PENTING
- Jangan membuat admin dari form register.
- Ganti password admin bawaan setelah instalasi jika dipakai untuk penggunaan nyata.

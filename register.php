<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#111827">
<title>Register — User Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
:root{--ink:#111827;--muted:#6B7280;--line:#E5E7EB;--bg:#F5F7FB;--accent:#4F46E5;--accent2:#4338CA;--danger:#DC2626;--green:#059669}
*{box-sizing:border-box}body{margin:0;min-height:100vh;background:var(--bg);font-family:'Inter',system-ui,sans-serif;color:var(--ink);display:grid;place-items:center;padding:24px;-webkit-font-smoothing:antialiased}.shell{width:min(1100px,100%);min-height:720px;background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 28px 70px rgba(15,23,42,.10);display:grid;grid-template-columns:.9fr 1.1fr;overflow:hidden}.side{position:relative;background:#111827;color:#fff;padding:48px;display:flex;flex-direction:column;justify-content:space-between;overflow:hidden}.side:before{content:'';position:absolute;width:340px;height:340px;border-radius:50%;right:-160px;top:-140px;background:rgba(99,102,241,.22)}.side:after{content:'';position:absolute;width:460px;height:460px;border-radius:50%;left:-270px;bottom:-330px;background:rgba(129,140,248,.08)}.side > *{position:relative;z-index:1}.mark{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#6366F1,#4338CA);display:grid;place-items:center;font-size:13px;font-weight:800}.side h1{font-size:32px;line-height:1.12;letter-spacing:-.04em;margin:28px 0 12px}.side p{font-size:13px;line-height:1.7;color:#A7B0C0;max-width:410px}.steps{display:grid;gap:15px;margin-top:30px}.step{display:flex;gap:11px;align-items:flex-start}.step-no{width:25px;height:25px;border:1px solid rgba(255,255,255,.12);border-radius:8px;display:grid;place-items:center;color:#A5B4FC;font-size:10px;font-weight:800;flex-shrink:0}.step strong{font-size:11px;display:block}.step span{font-size:10px;color:#8E98A9;display:block;margin-top:3px;line-height:1.45}.side-foot{font-size:10px;color:#6B7280;display:flex;align-items:center;gap:8px}.dot{width:6px;height:6px;border-radius:50%;background:#34D399;box-shadow:0 0 0 4px rgba(52,211,153,.10)}.form-side{padding:50px 52px;display:flex;align-items:center}.form-wrap{width:100%;max-width:470px;margin:auto}.eyebrow{font-size:10px;text-transform:uppercase;letter-spacing:.14em;color:var(--accent);font-weight:800;margin-bottom:10px}.form-wrap h2{font-size:26px;letter-spacing:-.03em;margin:0 0 7px}.sub{font-size:12px;line-height:1.6;color:var(--muted);margin:0 0 24px}.alert{display:none;border-radius:10px;padding:11px 12px;font-size:11px;line-height:1.45;margin-bottom:14px}.alert.show{display:flex;gap:9px}.alert.error{background:#FEF2F2;color:#B91C1C}.alert.success{background:#ECFDF5;color:#047857}.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{margin-bottom:14px}.field.full{grid-column:1/-1}.field label{display:block;font-size:11px;font-weight:800;color:#374151;margin-bottom:7px}.control-wrap{position:relative}.control{width:100%;height:42px;border:1px solid #D1D5DB;border-radius:10px;background:#fff;padding:0 38px 0 12px;outline:0;font-size:12px;color:var(--ink);transition:.18s}.control::placeholder{color:#A1A1AA}.control:focus{border-color:#818CF8;box-shadow:0 0 0 4px rgba(99,102,241,.10)}.control-icon{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#9CA3AF;font-size:11px;pointer-events:none}.toggle{position:absolute;right:7px;top:50%;transform:translateY(-50%);height:29px;width:29px;border:0;background:transparent;border-radius:8px;color:#9CA3AF;cursor:pointer}.toggle:hover{background:#F3F4F6;color:var(--ink)}.helper{display:block;font-size:9px;color:#9CA3AF;margin-top:5px}.submit{width:100%;height:43px;border:0;border-radius:10px;background:var(--accent);color:#fff;font-size:12px;font-weight:800;cursor:pointer;box-shadow:0 9px 20px rgba(79,70,229,.18);transition:.18s}.submit:hover{background:var(--accent2)}.submit:disabled{opacity:.55;cursor:not-allowed;box-shadow:none}.under{text-align:center;margin-top:18px;font-size:11px;color:var(--muted)}.under a{color:var(--accent);font-weight:800;text-decoration:none}.under a:hover{text-decoration:underline}.note{display:flex;gap:8px;align-items:flex-start;margin-top:15px;font-size:10px;line-height:1.5;color:#9CA3AF}.note i{color:var(--green);margin-top:1px}.spinner{width:14px;height:14px;border-radius:50%;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;display:inline-block;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:860px){.shell{grid-template-columns:1fr;max-width:560px}.side{display:none}.form-side{padding:38px 28px;min-height:680px}}
@media(max-width:540px){.grid{grid-template-columns:1fr}.field.full{grid-column:auto}.form-side{padding:32px 20px}}
</style>
</head>
<body>
<div class="shell">
    <section class="side">
        <div>
            <div class="mark">UM</div>
            <h1>Build your access<br>in a few steps.</h1>
            <p>Buat akun untuk mengakses dashboard pengelolaan data. Nomor WhatsApp harus aktif dan akan diverifikasi dengan OTP Fonnte.</p>
            <div class="steps">
                <div class="step"><div class="step-no">01</div><div><strong>Lengkapi profil</strong><span>Nama, email, dan nomor WhatsApp aktif.</span></div></div>
                <div class="step"><div class="step-no">02</div><div><strong>Buat password</strong><span>Password disimpan dalam bentuk hash di server.</span></div></div>
                <div class="step"><div class="step-no">03</div><div><strong>Terima notifikasi</strong><span>Sistem mengirim konfirmasi melalui Fonnte.</span></div></div>
            </div>
        </div>
        <div class="side-foot"><span class="dot"></span><span>Protected account flow</span></div>
    </section>

    <section class="form-side">
        <div class="form-wrap">
            <div class="eyebrow">Create account</div>
            <h2>Set up your account</h2>
            <p class="sub">Isi data di bawah untuk membuat akun baru.</p>
            <div id="alert" class="alert"></div>
            <form id="registerForm">
                <div id="registrationFields">
                    <div class="grid">
                        <div class="field full"><label for="name">Nama lengkap</label><div class="control-wrap"><input id="name" class="control" type="text" name="name" maxlength="100" placeholder="Contoh: Budi Santoso" required><i class="control-icon fa-regular fa-user"></i></div></div>
                        <div class="field"><label for="email">Email</label><div class="control-wrap"><input id="email" class="control" type="email" name="email" maxlength="150" autocomplete="email" placeholder="nama@email.com" required><i class="control-icon fa-regular fa-envelope"></i></div></div>
                        <div class="field"><label for="whatsapp">Nomor WhatsApp aktif</label><div class="control-wrap"><input id="whatsapp" class="control" type="tel" name="whatsapp" maxlength="15" inputmode="numeric" placeholder="081234567890" required><i class="control-icon fa-brands fa-whatsapp"></i></div><span class="helper">Nomor ini harus bisa menerima WhatsApp karena akan dikirim OTP.</span></div>
                        <div class="field"><label for="password">Password</label><div class="control-wrap"><input id="password" class="control" type="password" name="password" minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter" required><button type="button" class="toggle" onclick="togglePassword('password',this)"><i class="fa-regular fa-eye"></i></button></div></div>
                        <div class="field"><label for="confirm_password">Konfirmasi password</label><div class="control-wrap"><input id="confirm_password" class="control" type="password" name="confirm_password" minlength="6" autocomplete="new-password" placeholder="Ulangi password" required><button type="button" class="toggle" onclick="togglePassword('confirm_password',this)"><i class="fa-regular fa-eye"></i></button></div></div>
                    </div>
                    <div class="note"><i class="fa-brands fa-whatsapp"></i><span>Setelah data valid, sistem mengirim OTP melalui Fonnte. Akun baru hanya dibuat setelah OTP berhasil diverifikasi.</span></div>
                    <button id="submitBtn" class="submit" type="submit" style="margin-top:18px">Kirim OTP WhatsApp</button>
                </div>

                <div id="otpSection" style="display:none">
                    <div class="field"><label for="otp">Kode OTP WhatsApp</label><div class="control-wrap"><input id="otp" class="control" type="text" name="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="6 digit OTP"><i class="control-icon fa-solid fa-shield-halved"></i></div><span id="otpHelp" class="helper"></span></div>
                    <button id="verifyBtn" class="submit" type="button">Verifikasi & Buat Akun</button>
                    <button id="resendBtn" class="submit" type="button" style="margin-top:10px;background:#374151;box-shadow:none">Kirim OTP Lagi</button>
                    <button id="backBtn" class="submit" type="button" style="margin-top:10px;background:#E5E7EB;color:#111827;box-shadow:none">Kembali & Ubah Nomor</button>
                </div>
            </form>
            <div class="under">Sudah punya akun? <a href="login.php">Sign in</a></div>
        </div>
    </section>
</div>
<script>
const form=document.getElementById('registerForm'), alertBox=document.getElementById('alert'), btn=document.getElementById('submitBtn');
const fields=document.getElementById('registrationFields'), otpSection=document.getElementById('otpSection'), verifyBtn=document.getElementById('verifyBtn'), resendBtn=document.getElementById('resendBtn'), backBtn=document.getElementById('backBtn'), otpInput=document.getElementById('otp'), otpHelp=document.getElementById('otpHelp');
function escapeHtml(value){return String(value??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));}
function showAlert(message,type='error'){alertBox.className='alert show '+type;alertBox.innerHTML=`<i class="fa-solid ${type==='error'?'fa-circle-exclamation':'fa-circle-check'}"></i><span>${escapeHtml(message)}</span>`;}
function togglePassword(id,button){const input=document.getElementById(id), icon=button.querySelector('i');input.type=input.type==='password'?'text':'password';icon.className=input.type==='password'?'fa-regular fa-eye':'fa-regular fa-eye-slash';}
async function post(data){const res=await fetch('register_action.php',{method:'POST',body:data});const text=await res.text();let json;try{json=JSON.parse(text);}catch(e){throw new Error('Server mengembalikan respons yang tidak valid.');}if(!json.success)throw new Error(json.message||'Proses registrasi gagal.');return json;}
form.addEventListener('submit',async e=>{e.preventDefault();if(document.getElementById('password').value!==document.getElementById('confirm_password').value){showAlert('Konfirmasi password tidak sama.');return}btn.disabled=true;btn.innerHTML='<span class="spinner"></span> Mengirim OTP';try{const fd=new FormData(form);fd.set('action','send_otp');const data=await post(fd);showAlert(data.message+' Cek WhatsApp kamu untuk kode 6 digit.','success');fields.style.display='none';otpSection.style.display='block';otpInput.focus();otpHelp.textContent='OTP dikirim ke '+data.whatsapp+' dan berlaku 5 menit.';}catch(err){showAlert(err.message);}finally{btn.disabled=false;btn.textContent='Kirim OTP WhatsApp';}});
verifyBtn.addEventListener('click',async()=>{const otp=otpInput.value.trim();if(!/^\d{6}$/.test(otp)){showAlert('Masukkan OTP 6 digit.');return}verifyBtn.disabled=true;verifyBtn.innerHTML='<span class="spinner"></span> Memverifikasi';try{const fd=new FormData();fd.set('action','verify_otp');fd.set('otp',otp);const data=await post(fd);showAlert(data.message+(data.notification?.sent?' Notifikasi WhatsApp konfirmasi juga terkirim.':''),'success');setTimeout(()=>location.href='login.php',1600);}catch(err){showAlert(err.message);}finally{verifyBtn.disabled=false;verifyBtn.textContent='Verifikasi & Buat Akun';}});
resendBtn.addEventListener('click',async()=>{resendBtn.disabled=true;resendBtn.textContent='Mengirim...';try{const fd=new FormData();fd.set('action','resend_otp');const data=await post(fd);showAlert(data.message,'success');otpInput.value='';}catch(err){showAlert(err.message);}finally{resendBtn.disabled=false;resendBtn.textContent='Kirim OTP Lagi';}});
backBtn.addEventListener('click',()=>{otpSection.style.display='none';fields.style.display='block';otpInput.value='';showAlert('Silakan periksa atau ubah nomor WhatsApp sebelum meminta OTP lagi.','success');});
otpInput.addEventListener('input',()=>{otpInput.value=otpInput.value.replace(/\D/g,'').slice(0,6);});
</script>
</body>
</html>

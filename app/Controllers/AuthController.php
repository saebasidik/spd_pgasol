<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // 1. Halaman Form Registrasi
    public function register()
    {
        return view('auth/register');
    }

    // 2. Proses Simpan Pendaftaran Akun
    public function registerProcess()
    {
        $username      = $this->request->getPost('username');
        $roleRequested = $this->request->getPost('role') ?? 'pekerja';

        // 🟢 PROTEKSI SINGLE ADMIN: Cek jika ada yang mencoba daftar sebagai admin
        if ($roleRequested === 'admin') {
            $existingAdminCount = $this->userModel->where('role', 'admin')->countAllResults();
            if ($existingAdminCount >= 1) {
                return redirect()->back()->withInput()->with('error', 'Akses Ditolak: Sistem dibatasi maksimal 1 Akun Admin!');
            }
        }

        // Cek apakah username sudah terdaftar
        $existingUser = $this->userModel->where('username', $username)->first();
        if ($existingUser) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan, silakan pilih username lain!');
        }

        // Simpan user baru ke database
        $this->userModel->insert([
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'username'     => $username,
            'email'        => $this->request->getPost('email'),
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'         => $roleRequested, // Default sebagai pekerja
        ]);

        return redirect()->to('/login')->with('success', 'Registrasi akun berhasil! Silakan login.');
    }

    // 3. Halaman Form Login
    public function login()
    {
        return view('auth/login');
    }

    // 4. Proses Autentikasi Login
    public function loginProcess()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('username', $username)->first();

        if ($user && password_verify($password, $user['password'])) {
            // Set session login
            session()->set([
                'user_id'      => $user['id'],
                'username'     => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role'         => $user['role'],
                'isLoggedIn'   => true,
            ]);

            return redirect()->to('/spd/cal-data')->with('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
        }

        return redirect()->back()->withInput()->with('error', 'Username atau Password salah!');
    }

    // 5. Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }

    // =========================================================================
    // 🔑 FITUR LUPA PASSWORD (FORGOT PASSWORD)
    // =========================================================================

    // 6. Halaman Form Lupa Password
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    // 7. Proses Buat & Kirim Token Reset Password
    // 7. Proses Buat & Kirim Token Reset Password berdasarkan USERNAME
    public function sendResetToken()
    {
        $username = $this->request->getPost('username');

        // Cari user murni berdasarkan username
        $user = $this->userModel->where('username', $username)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan!');
        }

        // Generate Token Unik & Kedaluwarsa 1 Jam
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->userModel->update($user['id'], [
            'reset_token'      => $token,
            'reset_expires_at' => $expiresAt
        ]);

        $resetLink = site_url('reset-password/' . $token);

        return redirect()->to('/forgot-password')->with('success_link', $resetLink);
    }
    // 8. Halaman Form Reset Password Baru
    public function resetPassword($token)
    {
        $user = $this->userModel->where('reset_token', $token)
                                ->where('reset_expires_at >=', date('Y-m-d H:i:s'))
                                ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Link reset password tidak valid atau sudah kedaluwarsa!');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    // 9. Proses Update Password Baru ke Database
    public function updatePassword()
    {
        $token          = $this->request->getPost('token');
        $passwordBaru   = $this->request->getPost('password');
        $konfirmasiPass = $this->request->getPost('confirm_password');

        if ($passwordBaru !== $konfirmasiPass) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok!');
        }

        $user = $this->userModel->where('reset_token', $token)->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid!');
        }

        // Update password & hapus token
        $this->userModel->update($user['id'], [
            'password'         => password_hash($passwordBaru, PASSWORD_BCRYPT),
            'reset_token'      => null,
            'reset_expires_at' => null
        ]);

        return redirect()->to('/login')->with('success', 'Password berhasil diperbarui! Silakan login kembali.');
    }
}
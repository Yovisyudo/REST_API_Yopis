<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected $modelName = UserModel::class;
    protected $format = 'json';

    /**
     * LOGIN & SYNC USER DARI FIREBASE
     * Endpoint: POST /api/auth/login
     */
    public function login()
    {
        // 1. Ambil data JSON dari Flutter
        // Kita pakai getJSON() karena Flutter mengirim raw body JSON
        $json = $this->request->getJSON();

        // Validasi input minimal
        if (!$json || !isset($json->email) || !isset($json->uid)) {
            return $this->failValidationError('Email dan UID wajib dikirim');
        }

        $email = $json->email;
        $firebaseUid = $json->uid;
        $name = $json->name ?? 'User Baru'; // Default jika nama kosong

        // 2. LOGIC PENTING: Cari user berdasarkan Email ATAU Firebase UID
        // Kita tidak cari by ID, karena ID diatur database.
        $existingUser = $this->model->where('email', $email)
                                    ->orWhere('firebase_uid', $firebaseUid)
                                    ->first();

        if ($existingUser) {
            // === SKENARIO A: USER SUDAH ADA ===
            
            // Cek apakah firebase_uid di database masih kosong (User lama)?
            // Jika ya, kita update supaya terhubung.
            if ($existingUser['firebase_uid'] == null || $existingUser['firebase_uid'] != $firebaseUid) {
                $this->model->update($existingUser['user_id'], [
                    'firebase_uid' => $firebaseUid
                ]);
                
                // Ambil data terbaru setelah update
                $existingUser = $this->model->find($existingUser['user_id']);
            }

            $finalUser = $existingUser;

        } else {
            // === SKENARIO B: USER BELUM ADA (INSERT BARU) ===
            
            $dataBaru = [
                // 'user_id' => JANGAN DIISI (Biarkan Auto Increment)
                'firebase_uid' => $firebaseUid, // Simpan UID di sini
                'email'        => $email,
                'name'         => $name,
                'password'     => 'LOGIN_VIA_FIREBASE', // Password dummy
                // Tambahkan field lain jika perlu
            ];

            // Insert ke database
            $this->model->insert($dataBaru);
            
            // Ambil data yang barusan dibuat
            $insertId = $this->model->getInsertID();
            $finalUser = $this->model->find($insertId);
        }

        // Hapus password dari response agar aman
        unset($finalUser['password']);

        return $this->respond([
            'status'  => 200,
            'message' => 'Login & Sync Berhasil',
            'data'    => $finalUser
        ]);
    }
    /**
     * REGISTER & SYNC USER DARI FIREBASE
     * Endpoint: POST /api/auth/register
     */
    public function register()
    {
        // 1. Ambil data JSON dari Flutter
        $json = $this->request->getJSON();

        if (!$json || !isset($json->email) || !isset($json->uid)) {
            return $this->failValidationError('Email, UID, dan Nama wajib dikirim');
        }

        $email = $json->email;
        $firebaseUid = $json->uid;
        $name = $json->name ?? 'User Baru';

        // 2. Cek apakah email sudah terdaftar
        $existingUser = $this->model->where('email', $email)->first();

        if ($existingUser) {
            // Jika sudah ada, kita update UID-nya saja (jaga-jaga jika sinkronisasi gagal sebelumnya)
            $this->model->update($existingUser['user_id'], ['firebase_uid' => $firebaseUid]);
            $finalUser = $this->model->find($existingUser['user_id']);
        } else {
            // Jika benar-benar baru, lakukan INSERT
            $dataBaru = [
                'firebase_uid' => $firebaseUid,
                'email'        => $email,
                'name'         => $name,
                'password'     => 'REGISTER_VIA_FIREBASE', // Password dummy
                'style_preference' => 'casual' // Default preference
            ];

            if (!$this->model->insert($dataBaru)) {
                return $this->fail($this->model->errors());
            }

            $insertId = $this->model->getInsertID();
            $finalUser = $this->model->find($insertId);
        }

        unset($finalUser['password']);

        return $this->respond([
            'status'  => 201, // Created
            'message' => 'User Berhasil Terdaftar di MySQL',
            'data'    => $finalUser
        ]);
    }
}
<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\UserModel; // <--- Jangan lupa load ini
use App\Libraries\WeatherAPI;
use CodeIgniter\RESTful\ResourceController;

class EventController extends ResourceController
{
    protected $modelName = 'App\Models\EventModel';
    protected $format = 'json';

    // =========================================================================
    // 1. HELPER: PENERJEMAH (Firebase UID String -> User ID Database Angka)
    // =========================================================================
// HELPER HYBRID: Bisa terima user Manual & Firebase
    private function getInternalUserId()
    {
        // 1. CEK: Apakah dia login Manual? (Dapat ID Angka langsung)
        if (isset($_SERVER['MANUAL_USER_ID'])) {
            return $_SERVER['MANUAL_USER_ID']; 
            // Langsung return karena token manual biasanya sudah menyimpan ID asli (angka)
        }

        // 2. CEK: Apakah dia login Firebase? (Dapat String UID)
        $firebaseUid = $_SERVER['FIREBASE_UID'] ?? null;
        
        if ($firebaseUid) {
            // Lakukan logika terjemahan yang tadi (Cek DB / Auto Register)
            $userModel = new UserModel();
            $existingUser = $userModel->where('firebase_uid', $firebaseUid)->first();

            if ($existingUser) {
                return $existingUser['user_id'];
            } 
            
            // Auto Register Logic
            try {
                $newId = $userModel->insert([
                    'firebase_uid'     => $firebaseUid,
                    'name'             => 'User Google Baru', 
                    'email'            => 'google_' . uniqid() . '@temp.com',
                    'password'         => password_hash('GOOGLE_PASS', PASSWORD_BCRYPT),
                    'style_preference' => 'casual'
                ]);
                return $newId;
            } catch (\Exception $e) {
                return null;
            }
        }

        // 3. Gak ada dua-duanya
        return null;
    }
    // =========================================================================
    // 2. CREATE (Tambah Event)
    // =========================================================================
    public function create()
    {
        // PANGGIL HELPER BARU
        $internalUserId = $this->getInternalUserId();

        // Cek jika gagal dapat ID
        if (!$internalUserId) {
            return $this->failUnauthorized('Gagal mengidentifikasi User. Silakan login ulang.');
        }

        // Validasi Input
        $rules = [
            'name' => 'required',
            'date' => 'required|valid_date',
            'location' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Ambil Inputan
        $location = $this->request->getVar('location');
        $dateEvent = $this->request->getVar('date');

        // --- FETCH WEATHER ---
        $weatherAPI = new WeatherAPI();
        $weather = $weatherAPI->getWeather($location, $dateEvent);

        // Susun Data
        $data = [
            'user_id' => $internalUserId, // <--- SEKARANG INI ADALAH ANGKA (INT)
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description'),
            'date' => $dateEvent,
            'location' => $location,
            'weather_temp' => $weather['temp'] ?? null,
            'weather_condition' => $weather['condition'] ?? null
        ];

        // Simpan ke Database
        $eventId = $this->model->insert($data);

        if ($eventId) {
            return $this->respondCreated([
                'success' => true,
                'event_id' => $eventId,
                'message' => 'Event berhasil dibuat',
                'weather_info' => $weather ? "Prediksi: {$weather['condition']} ({$weather['temp']}°C)" : "Cuaca n/a",
                'data' => $data
            ]);
        }

        return $this->fail('Gagal menyimpan event ke database');
    }

    // =========================================================================
    // 3. GET ALL (List Event User)
    // =========================================================================
    public function index()
    {
        // PANGGIL HELPER BARU
        $internalUserId = $this->getInternalUserId();

        if (!$internalUserId) {
            return $this->failUnauthorized('User ID tidak terbaca');
        }

        // Query menggunakan ID ANGKA
        $events = $this->model
            ->where('user_id', $internalUserId)
            ->orderBy('date', 'ASC')
            ->findAll();

        return $this->respond([
            'success' => true,
            'events' => $events
        ]);
    }
}
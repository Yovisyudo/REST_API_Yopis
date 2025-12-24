<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    // 1. PASTIKAN INI TRUE (Karena database sudah balik jadi Auto Increment Angka)
    protected $useAutoIncrement = true; 

    // 2. TAMBAHKAN 'firebase_uid' DI SINI (Wajib!)
    protected $allowedFields = ['name', 'email', 'password', 'style_preference', 'firebase_uid'];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = ''; // Kosongkan jika tidak pakai updated_at, atau isi 'updated_at'
    
    // Validasi tetap aman
    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'email' => 'required|valid_email|is_unique[users.email]',
        // Password tetap required, jadi nanti di Controller kita isi password dummy untuk user Firebase
        'password' => 'required|min_length[6]' 
    ];
}
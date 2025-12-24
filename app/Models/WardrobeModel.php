<?php

namespace App\Models;

use CodeIgniter\Model;

class WardrobeModel extends Model
{
    // Nama Tabel di Database Anda
    protected $table            = 'wardrobe_items'; 
    protected $primaryKey       = 'id';
    
    // Agar created_at dan updated_at terisi otomatis
    protected $useTimestamps    = true; 
    
    // Daftar kolom yang BOLEH diisi oleh user (Wajib didaftarkan di sini)
    protected $allowedFields    = [
        'user_id', 
        'category_id', 
        'name', 
        'image_url', 
        'color', 
        'style'
    ];
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table            = 'events';
    protected $primaryKey       = 'event_id';
    protected $useTimestamps    = true; // Pastikan Langkah 1 sudah dijalankan
    
    // Sesuai gambar database Anda:
    protected $allowedFields    = [
        'user_id', 
        'name',          // BUKAN event_name
        'description', 
        'date',          // BUKAN event_date
        'location', 
        'weather_temp',      // Tambahan
        'weather_condition'  // Tambahan
    ];
}
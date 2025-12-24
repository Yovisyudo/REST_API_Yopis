<?php

namespace App\Libraries;

class WeatherAPI
{
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5';
    
    public function __construct()
    {
        $this->apiKey = getenv('WEATHER_API_KEY');
    }
    
    public function getWeather($location, $date = null)
    {
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->get("{$this->baseUrl}/forecast", [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ],
                'verify' => false, // <--- TAMBAHKAN INI (Matikan cek SSL)
                'http_errors' => false 
            ]);
            
            // Cek jika status bukan 200 (misal 401 Unauthorized atau 404 Not Found)
            if ($response->getStatusCode() != 200) {
                // Tampilkan error ke layar Postman agar kita baca
                die('Error API: ' . $response->getBody());
            }
            
            $data = json_decode($response->getBody(), true);
            
            // ... (Kode logic tanggal/forecast Anda biarkan sama) ...
            if (isset($data['list'][0])) {
                 // ... kode mapping data ...
                 $forecast = $data['list'][0];
                 return [
                    'temp' => round($forecast['main']['temp']),
                    'condition' => strtolower($forecast['weather'][0]['main']),
                    // ... dst
                 ];
            }
            
            return null;

        } catch (\Exception $e) {
            // --- DEBUG MODE: Tampilkan error koneksi ---
            die('Koneksi Gagal: ' . $e->getMessage());
        }
    }
    
}
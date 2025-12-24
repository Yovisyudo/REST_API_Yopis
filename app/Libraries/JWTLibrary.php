<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTLibrary
{
    private $key;
    
    public function __construct()
    {
        $this->key = getenv('JWT_SECRET_KEY');
    }
    
    public function generateToken($data)
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // 24 hours
            'data' => $data
        ];
        
        return JWT::encode($payload, $this->key, 'HS256');
    }
    
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
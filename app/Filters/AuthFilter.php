<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\UserModel;
use Config\Services;

class AuthFilter implements FilterInterface
{
   // app/Filters/AuthFilter.php
public function before(RequestInterface $request, $arguments = null)
{
    $header = $request->getHeaderLine('Authorization');
    
    if (empty($header)) {
        return Services::response()->setStatusCode(401)->setJSON(['message' => 'Token missing']);
    }

    // Mengambil UID dari "Bearer <UID>" dan membersihkan spasi/newline
    $parts = explode(' ', $header);
    $firebaseUid = trim(end($parts)); 

    $userModel = new \App\Models\UserModel();
    // Cari di database
    $user = $userModel->where('firebase_uid', $firebaseUid)->first();

    if (!$user) {
        // Jika sampai di sini, berarti UID yang dikirim Flutter tidak ada di tabel users MySQL
        return Services::response()->setStatusCode(401)->setJSON([
            'message' => 'User tidak ditemukan di MySQL',
            'debug_uid' => $firebaseUid // Tambahkan ini untuk melihat UID yang diterima
        ]);
    }

    $request->user = (object) $user;
}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
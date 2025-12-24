<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\WardrobeModel;

class WardrobeController extends ResourceController
{
    use ResponseTrait;

    // Helper function untuk ambil User ID dengan aman
 private function getUserId()
{
    // Cek apakah data user sudah disuntikkan oleh Filter/Middleware
    if (isset($this->request->user)) {
        $userData = (array) $this->request->user;
        return $userData['user_id'] ?? $userData['id'] ?? null;
    }

    // fallback: Jika Anda mengirim UID Firebase langsung di Header
    $firebaseUid = $this->request->getHeaderLine('Authorization');
    if ($firebaseUid) {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('firebase_uid', $firebaseUid)->first();
        return $user['user_id'] ?? null;
    }

    return null;
}

    // 1. GET ALL (Lihat semua baju milik user)
    // 1. GET ALL (Lihat semua baju + Nama Kategori)
    public function index()
    {
        $model = new WardrobeModel();
        $userId = $this->getUserId();

        if (!$userId) return $this->failUnauthorized('Akses ditolak.');

        // --- MODIFIKASI: JOIN TABLE ---
        $data = $model->select('wardrobe_items.*, categories.name as category_name') // Ambil semua data item + nama kategori
                      ->join('categories', 'categories.category_id = wardrobe_items.category_id') // Hubungkan kedua tabel
                      ->where('wardrobe_items.user_id', $userId) // Filter punya user ini
                      ->findAll();

        return $this->respond($data);
    }

    // 2. CREATE (Tambah Baju)
    public function create()
    {
        $model = new WardrobeModel();
        $userId = $this->getUserId();

        if (!$userId) return $this->failUnauthorized('Akses ditolak. User ID tidak terbaca.');

        $name       = $this->request->getPost('name');
        $categoryId = $this->request->getPost('category_id');
        $color      = $this->request->getPost('color');
        $style      = $this->request->getPost('style');

        if (empty($name) || empty($categoryId)) {
            return $this->fail('Nama dan Category ID wajib diisi', 400);
        }

        $imageUrl = null;
        $fileGambar = $this->request->getFile('image');

        if ($fileGambar && $fileGambar->isValid() && ! $fileGambar->hasMoved()) {
            $fileName = $fileGambar->getRandomName();
            $fileGambar->move(FCPATH . 'uploads', $fileName);
            $imageUrl = base_url('uploads/' . $fileName);
        }

        $data = [
            'user_id'     => $userId,
            'name'        => $name,
            'category_id' => $categoryId,
            'color'       => $color,
            'style'       => $style,
            'image_url'   => $imageUrl,
        ];

        if ($model->insert($data)) {
            return $this->respondCreated([
                'status' => 201, 
                'message' => 'Baju berhasil ditambahkan', 
                'data' => $data
            ]);
        } else {
            return $this->fail($model->errors());
        }
    }

    // 3. SHOW (Lihat 1 Baju)
    
// 3. SHOW (Lihat 1 Baju + Nama Kategori)
    public function show($id = null)
    {
        $model = new WardrobeModel();
        $userId = $this->getUserId();

        // Sama seperti index, kita Join dulu baru cari ID-nya
        $data = $model->select('wardrobe_items.*, categories.name as category_name')
                      ->join('categories', 'categories.category_id = wardrobe_items.category_id')
                      ->where('wardrobe_items.user_id', $userId)
                      ->find($id);

        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('Item tidak ditemukan.');
        }
    }



    // 4. UPDATE (Edit Baju)
    public function update($id = null)
    {
        $model = new WardrobeModel();
        $userId = $this->getUserId();
        
        // Ambil input JSON
        $json = $this->request->getJSON();
        
        // Cek kepemilikan
        $exist = $model->where('user_id', $userId)->find($id);
        if (!$exist) {
            return $this->failNotFound('Item tidak ditemukan atau bukan milik Anda.');
        }

        $data = [
            'name'        => $json->name ?? $exist['name'],
            'category_id' => $json->category_id ?? $exist['category_id'],
            'color'       => $json->color ?? $exist['color'],
            'style'       => $json->style ?? $exist['style'],
            'image_url'   => $json->image_url ?? $exist['image_url'],
        ];

        $model->update($id, $data);

        return $this->respond([
            'status' => 200,
            'message' => 'Data berhasil diupdate',
            'data' => $data
        ]);
    }

    // 5. DELETE (Hapus Baju)
   public function delete($id = null)
{
    $model = new WardrobeModel();
    $userId = $this->getUserId();

    if (!$userId) return $this->failUnauthorized('Akses ditolak.');

    // Cari berdasarkan item_id DAN user_id untuk keamanan
    $item = $model->where([
        'item_id' => $id, 
        'user_id' => $userId
    ])->first();
    
    if ($item) {
        $model->delete($id);
        return $this->respondDeleted([
            'status' => 200,
            'message' => 'Item berhasil dihapus'
        ]);
    } else {
        return $this->failNotFound('Item tidak ditemukan atau bukan milik Anda.');
    }
}
}
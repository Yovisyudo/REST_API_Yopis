<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use Kreait\Firebase\Factory;

class FirebaseAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Authorization token not found'
                ]);
        }

        $token = $matches[1];

        try {
            $factory = (new Factory)
                ->withServiceAccount(APPPATH . 'firebase-service-account.json');

            $auth = $factory->createAuth();
            $verifiedToken = $auth->verifyIdToken($token);

            // Ambil UID Firebase
            $uid = $verifiedToken->claims()->get('sub');

            // Simpan UID ke global server
            $_SERVER['FIREBASE_UID'] = $uid;

        } catch (\Throwable $e) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid Firebase token'
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing here
    }
}

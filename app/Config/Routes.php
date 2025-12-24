<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default routes
$routes->get('/', 'Home::index');

// ===== API ROUTES =====
$routes->group('api', ['filter' => 'cors'], function($routes) {
    
    // === AUTH ROUTES (Public) ===
    // Kita buat grup 'auth' supaya URL-nya jadi: /api/auth/login
    $routes->group('auth', function($routes) {
        $routes->post('login', 'AuthController::login');    // -> /api/auth/login
        $routes->post('register', 'AuthController::register'); // -> /api/auth/register
    });
    
    // === PROTECTED ROUTES (Butuh Token) ===
    // Menggunakan filter 'firebase_auth' (Pastikan filter ini sudah dibuat!)
    $routes->group('', ['filter' => 'firebase_auth'], function($routes) {
        
        // Auth Profile
        $routes->group('auth', function($routes) {
            $routes->post('logout', 'AuthController::logout');
            $routes->get('profile', 'AuthController::profile');
        });
        
        // Wardrobe
        $routes->get('wardrobe', 'WardrobeController::index');
        $routes->post('wardrobe', 'WardrobeController::create');
        $routes->get('wardrobe/(:num)', 'WardrobeController::show/$1');
        $routes->put('wardrobe/(:num)', 'WardrobeController::update/$1');
        $routes->delete('wardrobe/(:num)', 'WardrobeController::delete/$1');
        
        // Events
        $routes->get('events', 'EventController::index');
        $routes->post('events', 'EventController::create');
        $routes->get('events/(:num)', 'EventController::show/$1');
        $routes->put('events/(:num)', 'EventController::update/$1');
        $routes->delete('events/(:num)', 'EventController::delete/$1');
        
        // Recommendations
        $routes->get('recommendations/(:num)', 'RecommendationController::getRecommendations/$1');
        $routes->post('outfits', 'RecommendationController::saveOutfit');
        $routes->get('outfits', 'RecommendationController::getOutfits');
    });
});
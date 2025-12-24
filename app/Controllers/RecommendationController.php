<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\WardrobeItemModel;
use App\Models\OutfitModel;
use App\Libraries\AIRecommendation;
use CodeIgniter\RESTful\ResourceController;

class RecommendationController extends ResourceController
{
    protected $format = 'json';
    
    public function getRecommendations($eventId)
    {
        $userId = $this->request->user['user_id'];
        
        // Get event
        $eventModel = new EventModel();
        $event = $eventModel->where('event_id', $eventId)->where('user_id', $userId)->first();
        
        if (!$event) {
            return $this->failNotFound('Event not found');
        }
        
        // Get wardrobe items with AI analysis
        $wardrobeModel = new WardrobeItemModel();
        $items = $wardrobeModel
            ->select('wardrobe_items.*, ai_analyses.weather_suitable, 
                      ai_analyses.material, ai_analyses.detected_style')
            ->join('ai_analyses', 'wardrobe_items.item_id = ai_analyses.item_id', 'left')
            ->where('wardrobe_items.user_id', $userId)
            ->findAll();
        
        // Generate recommendations
        $ai = new AIRecommendation();
        $recommendations = $ai->generateRecommendations($items, $event);
        
        return $this->respond([
            'success' => true,
            'event' => $event,
            'recommendations' => $recommendations
        ]);
    }
    
    public function saveOutfit()
    {
        $userId = $this->request->user['user_id'];
        
        $rules = [
            'event_id' => 'required|integer',
            'item_ids' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }
        
        $outfitModel = new OutfitModel();
        
        $outfitData = [
            'user_id' => $userId,
            'event_id' => $this->request->getVar('event_id'),
            'date' => date('Y-m-d'),
            'ai_generated' => true
        ];
        
        $outfitId = $outfitModel->insert($outfitData);
        
        if ($outfitId) {
            // Insert outfit items
            $itemIds = $this->request->getVar('item_ids');
            $db = \Config\Database::connect();
            
            foreach ($itemIds as $itemId) {
                $db->table('outfit_items')->insert([
                    'outfit_id' => $outfitId,
                    'item_id' => $itemId
                ]);
            }
            
            return $this->respondCreated([
                'success' => true,
                'outfit_id' => $outfitId,
                'message' => 'Outfit saved successfully'
            ]);
        }
        
        return $this->fail('Failed to save outfit');
    }
}
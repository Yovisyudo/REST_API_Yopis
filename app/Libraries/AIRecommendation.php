<?php

namespace App\Libraries;

class AIRecommendation
{
    public function generateRecommendations($items, $event)
    {
        $weatherCategory = $this->getWeatherCategory($event['weather_temp'], $event['weather_condition']);
        $eventStyle = $this->determineEventStyle($event['name'], $event['description']);
        
        // Filter items suitable for weather
        $suitableItems = array_filter($items, function($item) use ($weatherCategory) {
            return $item['weather_suitable'] && 
                   strpos($item['weather_suitable'], $weatherCategory) !== false;
        });
        
        // Filter by style
        $styleMatchItems = array_filter($suitableItems, function($item) use ($eventStyle) {
            return $item['detected_style'] && 
                   strpos($item['detected_style'], $eventStyle) !== false;
        });
        
        // Group by category
        $tops = array_filter($styleMatchItems, fn($i) => $i['category_id'] == 1);
        $bottoms = array_filter($styleMatchItems, fn($i) => $i['category_id'] == 2);
        $dresses = array_filter($styleMatchItems, fn($i) => $i['category_id'] == 3);
        $outers = array_filter($styleMatchItems, fn($i) => $i['category_id'] == 4);
        
        $recommendations = [];
        
        // Combination 1: Top + Bottom
        if (count($tops) > 0 && count($bottoms) > 0) {
            $recommendations[] = [
                'id' => 1,
                'items' => [array_values($tops)[0], array_values($bottoms)[0]],
                'score' => 95,
                'reason' => "Perfect for {$eventStyle} event in {$weatherCategory} weather",
                'weather_tip' => $this->getWeatherTip($weatherCategory)
            ];
        }
        
        // Combination 2: Dress
        if (count($dresses) > 0) {
            $outfit = [array_values($dresses)[0]];
            if ($weatherCategory == 'cool' && count($outers) > 0) {
                $outfit[] = array_values($outers)[0];
            }
            $recommendations[] = [
                'id' => 2,
                'items' => $outfit,
                'score' => 90,
                'reason' => "Elegant {$eventStyle} look",
                'weather_tip' => $this->getWeatherTip($weatherCategory)
            ];
        }
        
        // Combination 3: Top + Bottom + Outer
        if (count($tops) > 1 && count($bottoms) > 1 && count($outers) > 0) {
            $recommendations[] = [
                'id' => 3,
                'items' => [
                    array_values($tops)[1],
                    array_values($bottoms)[1],
                    array_values($outers)[0]
                ],
                'score' => 88,
                'reason' => "Layered look for {$weatherCategory} weather",
                'weather_tip' => $this->getWeatherTip($weatherCategory)
            ];
        }
        
        return $recommendations;
    }
    
    private function getWeatherCategory($temp, $condition)
    {
        if ($condition == 'rain' || $condition == 'rainy') {
            return 'rainy';
        }
        
        if ($temp > 28) return 'hot';
        if ($temp > 23) return 'warm';
        if ($temp > 18) return 'cool';
        return 'cold';
    }
    
    private function determineEventStyle($name, $description)
    {
        $text = strtolower($name . ' ' . $description);
        
        if (strpos($text, 'meeting') !== false || 
            strpos($text, 'formal') !== false || 
            strpos($text, 'business') !== false) {
            return 'formal';
        }
        
        if (strpos($text, 'date') !== false || 
            strpos($text, 'dinner') !== false || 
            strpos($text, 'kondangan') !== false) {
            return 'elegant';
        }
        
        if (strpos($text, 'sport') !== false || 
            strpos($text, 'gym') !== false || 
            strpos($text, 'jogging') !== false) {
            return 'sport';
        }
        
        return 'casual';
    }
    
    private function getWeatherTip($category)
    {
        $tips = [
            'hot' => '☀️ Stay cool! Wear light colors and breathable fabrics',
            'warm' => '🌤️ Pleasant weather, perfect for most outfits',
            'cool' => '❄️ Layer up! Bring a light jacket',
            'cold' => '🧊 Bundle up with warm layers',
            'rainy' => '☔ Bring umbrella and waterproof items'
        ];
        
        return $tips[$category] ?? 'Have a great day!';
    }
}

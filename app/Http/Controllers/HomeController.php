<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class HomeController extends Controller
{

    public function map()
    {
        $zoom = 15;
        $view = [33.5074481, 36.2003211];
        $points = $this->points(5);
        $categories = $this->categories();
        return Inertia::render('Map', compact('zoom', 'view', 'points', 'categories'));
    }

    public function getPoints(Request $request)
    {
        $points = $this->points(10);
        return json_encode(['points'=>$points]);
    }


    private function points(int $count = 50)
    {
        $points = [];
        for($i =0 ; $i <=$count;$i++){
            $lat = mt_rand(331000, 339000) / 10000;
            $long = mt_rand(361000, 369000) / 10000;
            $points[] = [
                'coords' => [$lat,$long],
                'color' => Arr::random(['black','red','gray']),
                'size' => Arr::random([24,32])
            ];
        }
        return $points;
    }

    private function categories()
    {
        return [
            'Hotels',
            'Things',
            'Museums',
            'Transit',
            'Pharmacies',
            'ATMs',
            'Museums',
            'Transit',
            'Pharmacies',
            'ATMs',
            'Museums',
            'Transit',
            'Pharmacies',
            'ATMs',
            'Museums',
            'Transit',
            'Pharmacies',
            'ATMs',
            'Museums',
            'Transit',
            'Pharmacies',
            'ATMs',
        ];
    }
}

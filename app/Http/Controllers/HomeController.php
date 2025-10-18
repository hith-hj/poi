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
        $view = [33.512767, 36.274889];
        $points = $this->points(5);
        $categories = $this->categories();
        $cities = $this->cities();
        return Inertia::render('Map', compact('zoom', 'view', 'points', 'categories','cities'));
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
            $lat = mt_rand(335000, 335199) / 10000;
            $long = mt_rand(362600, 362799) / 10000;
            $points[] = [
                'coords' => [$lat,$long],
                'color' => Arr::random(['brown','orange','yellow']),
                'size' => Arr::random([24,16])
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

    private function cities()
    {
        return [
            'damascus',
            'damascus cd',
            'aleppo',
            'hamah',
            'homs',
            'latakia',
            'tartus',
            'daraa',
            'idlib',
        ];
    }
}

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
        $city = 'damascus';
        $view = [33.513767, 36.277889];
        $points = $this->points(5);
        $categories = $this->categories();
        $cities = $this->cities();
        return Inertia::render('Map', compact('city','zoom', 'view', 'points', 'categories','cities'));
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
            'Museums',
            'Pharmacies',
            'ATMs',
            'Bars',
            'Restaurants',
            'Delivery',
            'Cafes',
            'Parks',
            'Gyms',
            'Movies',
            'Art',
            'Nightlife',
            'Libraries',
            'Shoping',
            'Elcetronics',
            'Sports',
            'Cars',
            'Groceries',
            'Car wash',
            'Car rental',
            'Parking',
            'Beauty',
        ];
    }

    private function cities()
    {
        return [
            ['name'=>'damascus','coords'=>[33.513767, 36.277889],'color'=>'red'],
            ['name'=>'damascus dc','coords'=>[33.512767, 36.274889],'color'=>'red'],
            ['name'=>'aleppo','coords'=>[36.199505, 37.162658],'color'=>'red'],
            ['name'=>'homs','coords'=>[34.730171, 36.71489],'color'=>'red'],
            ['name'=>'latakia','coords'=>[35.516782, 35.786514],'color'=>'red'],
            ['name'=>'hamah','coords'=>[35.132675, 36.754816],'color'=>'red'],
            ['name'=>'tartus','coords'=>[34.890717, 35.881522],'color'=>'red'],
            ['name'=>'daraa','coords'=>[32.626574, 36.103226],'color'=>'red'],
            ['name'=>'idlib','coords'=>[35.934373, 36.631113],'color'=>'red'],
        ];
    }
}

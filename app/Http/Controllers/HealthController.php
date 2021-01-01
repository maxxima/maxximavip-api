<?php
namespace App\Http\Controllers;
use Illuminate\Routing\Controller;

class HealthController extends Controller{

    public function health(){
        return response()->json([
            "health"=>"1.0.0.0"
        ]);
    }
}
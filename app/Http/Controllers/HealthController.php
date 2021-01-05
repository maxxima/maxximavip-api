<?php
namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class HealthController extends Controller{

    public function health(){
        return response()->json([
            "health"=>"1.0.0.2",
            "phpVersion"=>phpversion()
        ]);
    }

    public function generateSwaggerLumenDocs(){
        Artisan::call("swagger-lume:generate");
        return "Ok";
    }
}
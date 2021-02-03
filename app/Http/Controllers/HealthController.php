<?php
namespace App\Http\Controllers;
use App\Services\MaxxApiServiceInterface;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class HealthController extends Controller{

    private MaxxApiServiceInterface  $maxxApiService;

    public function __construct(MaxxApiServiceInterface  $maxxApiService)
    {
        $this->maxxApiService = $maxxApiService;
    }
    public function health(){
        return response()->json([
            "tag"=>"1.0.1.7.1"
        ]);

    }

    public function generateSwaggerLumenDocs(){
        Artisan::call("swagger-lume:generate");
        return "Ok";
    }
}
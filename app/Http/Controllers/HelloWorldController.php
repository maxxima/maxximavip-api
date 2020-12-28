<?php
namespace App\Http\Controllers;

use App\Helpers\DateHelpers;
use App\Services\MaxxApiServiceInterface;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use GuzzleHttp\Http\Client;
use Illuminate\Support\Facades\Http;

class HelloWorldController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private MaxxApiServiceInterface  $maxximizerApiService;
    public function __construct(MaxxApiServiceInterface  $maxximizerApiService)
    {
        $this->maxximizerApiService = $maxximizerApiService;
    }

    public function helloWorld(){

        /*
        $responseString = Http::post('http://ip-api.com/batch', [
"208.80.152.201"
        ]);
        $data = json_decode($responseString);
        */
        return app()->environment();
        /*
        dd(App::environment());
        $response = $this->maxximizerApiService->verifyMemberId('maxx003');
        return response()->json($response->status());*/
    }

    public function redirect(){
        return redirect('http://www.google.com');
    }

    public function helloName(Request $request,int $age){
        $name = $request->input('name');
        return "Hello world {$name}. You are {$age} years old.";
    }

    public function helloJson(Request $request){
        $data = [
            "status" => "200",
            "details" => [
                "id"=> 1,
                "email"=> "a@a.a",
                "mobile"=> "",
                "source"=>"",
                "source_id"=> "",
                "message"=> "Bad Request : Already Logged In"
            ]
        ];
        return response()->json($data);
    }

    //
}

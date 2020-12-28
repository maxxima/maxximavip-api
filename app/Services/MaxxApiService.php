<?php


namespace App\Services;


use App\Constants\EnvironmentKeys;
use Illuminate\Support\Facades\Http;

class MaxxApiService implements MaxxApiServiceInterface
{

    public function verifyMemberId(string $memberId){
        $requestBody = [
            'username'=>$memberId,
            'key'=>env(EnvironmentKeys::MAXX_API_CLIENT_API_KEY)
        ];
        $response =  Http::post('http://admin.maxx.my/v1/index.php/api/MobileJSON/VerifyMember', $requestBody);
        return $response;
    }
}

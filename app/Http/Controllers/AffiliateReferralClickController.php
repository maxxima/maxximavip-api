<?php


namespace App\Http\Controllers;


use App\Constants\HttpStatusCodes;
use App\Repositories\AffiliateReferralRepositoryInterface;
use App\Services\MaxxApiServiceInterface;
use Illuminate\Http\Request;

class AffiliateReferralClickController
{
    private AffiliateReferralRepositoryInterface $affiliateReferralRepository;

    public function __construct(AffiliateReferralRepositoryInterface $affiliateReferralRepository)
    {
        $this->affiliateReferralRepository = $affiliateReferralRepository;
    }


    public function createNewClickV1(Request $request,$sessionKey){
        $url = $request->json()->get("url");

        $allowedUrls = [
            '#home',
            '#benefits',
            '#buy-now',
            '#contacts'
        ];

        if(in_array($url,$allowedUrls)){
            $repo = $this->affiliateReferralRepository;
            $referral = $repo->getReferralBySessionKey($sessionKey);
            if($referral != null){
                $affiliateId = $referral->affiliate_id;
                $sessionKey = $referral->session_key;
                $lastClick =  $repo->getLastReferralClick($sessionKey,$url);
                $bypassThrottle = $request->query("bypassThrottle");
                if($lastClick == null || $bypassThrottle == "true"){
                    $repo->createReferralClick($affiliateId,$sessionKey,$url);
                    return Response()->json([
                        "msg"=>"success"
                    ]);
                }else{
                    return Response()->json([
                        "msg"=>"Click count exceeded for specified url"
                    ],HttpStatusCodes::TOO_MANY_REQUESTS);
                }

            }else{
                return Response()->json([
                    "msg"=>"Referral session not found"
                ],HttpStatusCodes::NOT_FOUND);
            }
        }else{
            return Response()->json([
                "msg"=>"Invalid url"
            ],HttpStatusCodes::BAD_REQUEST);
        }
    }
}

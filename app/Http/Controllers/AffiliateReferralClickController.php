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

    /**
     * @OA\POST (
     *     path="/api/v1/affiliates-clicks/sessions/{sessionKey}",
     *     summary="Create a new affiliate referral click",
     *     tags={"affiliate-referral-clicks"},
     *     description="Create a new affiliate referral click. Referral click is throttled at one url per session. Url can only be of these values: #home|#benefits|#buy-now|#contacts",
     *     operationId="",
     * @OA\RequestBody(
     *     request="application/json",
     *    required=true,
     *    description="Click information",
     *    @OA\JsonContent(
     *       required={"url"},
     *       @OA\Property(property="url", type="string", format="", example="#home"),
     *    ),
     * ),
     *     @OA\Parameter(
     *         name="sessionKey",
     *         in="path",
     *         description="Session Key",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bypassThrottle",
     *         in="query",
     *         description="Disable throttling of click per url per session",
     *         required=true,
     *         @OA\Schema(
     *           type="boolean",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully create referral click"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Referral session not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Number of clicks per url for session exceeds"
     *     ),
     *     deprecated=false,
     *     security={
     *         {"apiKeyAuth": {}}
     *     },
     * )
     */
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

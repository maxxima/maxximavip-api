<?php
namespace App\Http\Controllers;
use App\Constants\HttpStatusCodes;
use App\Services\MaxxApiServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Repositories\Eloquent\AffiliateReferralRepository;
use App\Models\AffiliateReferral;
use App\Repositories\AffiliateReferralRepositoryInterface;
use stdClass;

class AffiliateReferralController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private AffiliateReferralRepositoryInterface $affiliateReferralRepository;
    private MaxxApiServiceInterface  $maxxApiService;

    public function __construct(AffiliateReferralRepositoryInterface $affiliateReferralRepository,
                                MaxxApiServiceInterface  $maxxApiService)
    {
        $this->affiliateReferralRepository = $affiliateReferralRepository;
        $this->maxxApiService = $maxxApiService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/affiliates/{affiliateId}/referral-sessions",
     *     summary="Create a new affiliate referral session",
     *     tags={"affiliate-referrals"},
     *     description="Create a new affiliate referral session",
     *     operationId="",
     *     @OA\Parameter(
     *         name="affiliateId",
     *         in="path",
     *         description="Affiliate id",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect successful"
     *     ),
     *     deprecated=false,
     *     security={
     *         {"apiKeyAuth": {}}
     *     },
     * )
     */
    public function createAffiliateReferralV1(Request $request, string $affiliateId){
        $response = $this->maxxApiService->verifyMemberId($affiliateId);
        $responseData = json_decode($response);
        if($response->status() == 200){
            $affiliateReferral = $this->affiliateReferralRepository->createReferral($affiliateId,
                $request->query("source"), $request->ip());
            //$redirectUrl = "http://localhost:8089/?affiliate_session_key={$affiliateReferral->session_key}";
            $response = [
              "sessionKey"=>$affiliateReferral->session_key,
                "affiliateData"=>$responseData
            ];
            return Response()->json($response,HttpStatusCodes::OK);
        }else{
            $response = [
                "sessionKey"=>null,
                "affiliateData"=>$responseData
            ];
            return Response()->json($response,HttpStatusCodes::BAD_REQUEST);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/affiliate-referrals/session-keys/{sessionKey}/conversion-confirmations",
     *     summary="Confirm referral session",
     *     tags={"affiliate-referrals"},
     *     description="Confim referral session after successful purchase",
     *     operationId="",
     *     @OA\Parameter(
     *         name="sessionKey",
     *         in="path",
     *         description="Session key identifier of referral to confirm",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="Affiliate referral is already confirmed"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Affiliate referral with specified session key is not found"
     *     ),
     *     deprecated=false
     * )
     */
    public function confirmReferralSessionV1(Request $request,$sessionKey){

        $affiliateReferral = AffiliateReferral::find($sessionKey);
        if($affiliateReferral != null){
            if($affiliateReferral->is_confirmed == false){
                $result = AffiliateReferral::where('session_key',$sessionKey)->update(
                    [
                        'is_confirmed'=>true,
                        'confirmed_timestamp'=> Carbon::now('UTC')
                    ]);

                return [
                    "success"=>true,
                    "msg"=>""
                ];
            }else{
                return Response([
                    "success"=>false,
                    "msg"=>"Affiliate referral session is already confirmed"
                ],HttpStatusCodes::GONE);
            }
        }else{
            return Response([
                "success"=>false,
                "msg"=>"Affiliate referral session not found"
            ],HttpStatusCodes::NOT_FOUND);
        }
    }
}
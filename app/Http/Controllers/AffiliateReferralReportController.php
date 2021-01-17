<?php
namespace App\Http\Controllers;
use App\Constants\HttpStatusCodes;
use App\Repositories\AffiliateReferralRepositoryInterface;
use Illuminate\Http\Request;

class AffiliateReferralReportController extends Controller{
    private AffiliateReferralRepositoryInterface $affiliateReferralRepository;
    public function __construct(AffiliateReferralRepositoryInterface $affiliateReferralRepository)
    {
        $this->affiliateReferralRepository = $affiliateReferralRepository;
    }

    /**
     * @param $lastNumberOfDays
     * @param $errorMessage
     * @return bool
     */
    private function tryIsValidNumberOfDays($lastNumberOfDays, &$errorMessage):bool{
        $errorMessage = null;

        if(is_numeric($lastNumberOfDays) == false){
            $errorMessage = "last_number_of_days should be an int";
            return false;
        }

        if($lastNumberOfDays > 30){
            $errorMessage = "Last number of days should be less than 30 days";
            return false;
        }
        return true;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/reports/dashboard/{affiliate_id}",
     *     summary="Get dashboard",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get dashboard of commonly used analytics",
     *     operationId="",
     *     @OA\Parameter(
     *         name="affiliate_id",
     *         in="path",
     *         description="Affiliate id",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="last_number_of_days",
     *         in="query",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     */
    public function dashboardV1(Request $request,string $affiliate_id){
        $lastNumberOfDays = $request->query("last_number_of_days");

        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);

        $repo = $this->affiliateReferralRepository;
        $viewCountToday = $repo->getViewCountByLastNumberOfDays($affiliate_id,0);
        $conversionCountToday = $repo->getConversionCountByLastNumberOfDays($affiliate_id,0);
        $clickCountToday = $repo->getClickCountByLastNumberOfDays($affiliate_id,0);

        $viewCountTrendPerDates = $repo->getViewCountAcrossDates($affiliate_id,$lastNumberOfDays);
        $clickCountTrendPerDates = $repo->getClickCountAcrossDates($affiliate_id,$lastNumberOfDays);
        $conversionCountTrendPerDates = $repo->getConversionCountAcrossDates($affiliate_id,$lastNumberOfDays);

        $geo = $repo->getConversionGeoDistributionByCountryAcrossDates($affiliate_id,$lastNumberOfDays);

        $response = [
            "Today"=>[
                "viewCount"=>$viewCountToday,
                "conversionCount"=>$conversionCountToday,
                "clickCount"=>$clickCountToday
            ],
            "Trends"=>[
                "viewCount"=>$viewCountTrendPerDates,
                "clickCount"=>$clickCountTrendPerDates,
                "engagementCount"=>$conversionCountTrendPerDates
            ],
            "Geo"=>$geo
        ];
        return response()->json($response);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/total-view-counts/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get total view count for the last x number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get total view count for the last x number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getTotalViewCountByLastNumberOfDaysV1(string $affiliateId,int $lastNumberOfDays){
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);
        $viewCount = $this->affiliateReferralRepository->getViewCountByLastNumberOfDays($affiliateId,$lastNumberOfDays);
        $data = [
            "viewCount"=>$viewCount
        ];
        return response()->json($data);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/total-click-counts/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get total click count for the last x number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get total click count for the last x number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getClickCountByLastNumberOfDaysV1(string $affiliateId,int $lastNumberOfDays){
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);
        $clickCount = $this->affiliateReferralRepository->getClickCountByLastNumberOfDays($affiliateId,$lastNumberOfDays);
        $data = [
            "clickCount"=>$clickCount
        ];
        return response()->json($data);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/total-conversion-counts/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get total conversion for the last x number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get total conversion for the last x number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getTotalConversionCountByLastNumberOfDaysV1(string $affiliateId,int $lastNumberOfDays){
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);

        $conversionCount = $this->affiliateReferralRepository->getConversionCountByLastNumberOfDays($affiliateId,$lastNumberOfDays);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/view-counts-across-dates/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get view count trend across a range of dates for the last x number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get view count trend across a range of dates for the last x number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getViewCountAcrossDatesV1(string $affiliateId,int $lastNumberOfDays){
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);

        $viewCount = $this->affiliateReferralRepository->getViewCountAcrossDates($affiliateId, $lastNumberOfDays);
        $data = [
            "viewCount"=>$viewCount
        ];
        return response()->json($data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/click-counts-across-dates/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get click count trend across a range of dates for the last x number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get click count trend across a range of dates for the last x number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getClickCountAcrossDatesV1(string $affiliateId,int $lastNumberOfDays){
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);

        $viewCount = $this->affiliateReferralRepository->getClickCountAcrossDates($affiliateId, $lastNumberOfDays);
        $data = [
            "viewCount"=>$viewCount
        ];
        return response()->json($data);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/conversion-counts-across-dates/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get conversion count trend across a range of dates for the last x number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get conversion count trend across a range of dates for the last x number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getConversionCountAcrossDatesV1(string $affiliateId,int $lastNumberOfDays){

        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage)== false) return Response([
            "msg" => $errorMessage
        ], HttpStatusCodes::BAD_REQUEST);

        $conversionCount = $this->affiliateReferralRepository->getConversionCountAcrossDates($affiliateId, $lastNumberOfDays);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/geo-conversion-counts-across-dates/countries/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get affiliate referral conversion trend across a range of dates by country for the x last number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get affiliate referral conversion trend across a range of dates by country for the x last number of days",
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
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getConversionGeoDistributionByCountryAcrossDatesV1(string $affiliateId,int $lastNumberOfDays){
        $conversionCount = $this->affiliateReferralRepository
            ->getConversionGeoDistributionByCountryAcrossDates($affiliateId, $lastNumberOfDays);
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage) == false) return Response([
            "msg"=>$errorMessage
        ]);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/geo-conversion-counts-across-dates/countries/{countryCode}/regions/last-number-of-days/{lastNumberOfDays}",
     *     summary="Get affiliate referral conversion trend across a range of dates by country for the x last number of days",
     *     tags={"affiliate-referrals-reports"},
     *     description="Get affiliate referral conversion trend across a range of dates by country for the x last number of days",
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
     *     @OA\Parameter(
     *         name="countryCode",
     *         in="path",
     *         description="Country Code e.g MY",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lastNumberOfDays",
     *         in="path",
     *         description="The number of days for how far back to fetch the data. 30 is the maximum",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful"
     *     ),
     *      security={
     *         {"apiKeyAuth": {}}
     *     },
     *     deprecated=false
     * )
     **/
    public function getConversionGeoDistributionByRegionAcrossDatesV1(string $affiliateId,string $countryCode, int $lastNumberOfDays){
        $conversionCount = $this->affiliateReferralRepository
            ->getConversionGeoDistributionByRegionAcrossDates($affiliateId,$countryCode, $lastNumberOfDays);
        if($this->tryIsValidNumberOfDays($lastNumberOfDays,$errorMessage) == false) return Response([
            "msg"=>$errorMessage
        ]);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }

}

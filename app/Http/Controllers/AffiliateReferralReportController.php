<?php
namespace App\Http\Controllers;
use App\Constants\HttpStatusCodes;
use App\Repositories\AffiliateReferralRepositoryInterface;
use Carbon\Carbon;
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
     *     path="/api/v1/reports/dashboard/affiliates/{affiliateId}/date-range/{startDate}/{endDate}",
     *     summary="Get primary dashboard",
     *     tags={"affiliate-referrals-dashboard-reports"},
     *     description="Get primary dashboard of commonly used analytics",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function dashboardByDateRangeV1(Request $request,string $affiliateId,string $startDate,string $endDate){

        $repo = $this->affiliateReferralRepository;
        $viewCountToday = $repo->getViewCountByLastNumberOfDays($affiliateId,0);
        $conversionCountToday = $repo->getConversionCountByLastNumberOfDays($affiliateId,0);
        $clickCountToday = $repo->getClickCountByLastNumberOfDays($affiliateId,0);

        $viewCountForDateRange = $repo->getViewCountByDateRange($affiliateId,$startDate,$endDate);
        $clickCountForDateRange = $repo->getClickCountByDateRange($affiliateId,$startDate,$endDate);
        $conversionCountForDateRange = $repo->getConversionCountByDateRange($affiliateId,$startDate,$endDate);



        $clickCountTrendPerDates = $repo->getClickCountAcrossDatesByDateRange($affiliateId,$startDate, $endDate);
        $conversionCountTrendPerDates = $repo->getConversionCountAcrossDatesByDateRange($affiliateId,$startDate,$endDate);

        $geo = $repo->getConversionGeoDistributionByCountryByDateRange($affiliateId,$startDate,$endDate);

        $response = [
            "countForToday"=>[
                "viewCount"=>$viewCountToday,
                "conversionCount"=>$conversionCountToday,
                "clickCount"=>$clickCountToday
            ],
            "countForDateRange"=>[
                "viewCount"=>$viewCountForDateRange,
                "conversionCount"=>$conversionCountForDateRange,
                "clickCount"=>$clickCountForDateRange
            ],
            "trends"=>[
                "clickCount"=>$clickCountTrendPerDates,
                "conversionCount"=>$conversionCountTrendPerDates
            ],
            "Geo"=>$geo
        ];
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/dashboard/referral-stats/affiliates/{affiliateId}/{startDate}/{endDate}",
     *     summary="Get referral statistic dashboard",
     *     tags={"affiliate-referrals-dashboard-reports"},
     *     description="Get dashboard of commonly used statistics of affiliate referrals",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function dashboardReferralStatsByDateRangeV1(Request $request,string $affiliateId, $startDate, $endDate){
        $repo = $this->affiliateReferralRepository;

        $viewCount = $repo->getViewCountByDateRange($affiliateId,$startDate, $endDate);
        $conversionCount = $repo->getConversionCountByDateRange($affiliateId,$startDate,$endDate);
        $data = [
            "view"=>$viewCount,
            "conversion"=>$conversionCount
        ];
        return response()->json($data);
    }



    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/total-conversion-counts/date-range/{startDate}/{endDate}",
     *     summary="Get total conversion count between date range",
     *     tags={"affiliate-referrals-total-count-reports"},
     *     description="Get total conversion count between date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getConversionCountByDateRangeV1(string $affiliateId, $startDate, $endDate){
        $conversionCount = $this->affiliateReferralRepository->getConversionCountByDateRange($affiliateId,$startDate,$endDate);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/total-view-counts/date-range/{startDate}/{endDate}",
     *     summary="Get total view count between dates",
     *     tags={"affiliate-referrals-total-count-reports"},
     *     description="Get total view counts between date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getViewCountByDateRangeV1(string $affiliateId, string $startDate,string $endDate){
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        $count = $this->affiliateReferralRepository->getViewCountByDateRange($affiliateId,$startDateCarbon,$endDateCarbon);

        return response()->json([
            "viewCount"=>$count
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/total-click-counts/date-range/{startDate}/{endDate}",
     *     summary="Get total click count between dates",
     *     tags={"affiliate-referrals-total-count-reports"},
     *     description="Get total click count between date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getClickCountByDateRangeV1(string $affiliateId, string $startDate,string $endDate){
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        $count = $this->affiliateReferralRepository->getClickCountByDateRange($affiliateId,$startDateCarbon,$endDateCarbon);

        return response()->json([
            "clickCount"=>$count
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/click-counts-across-dates/date-range/{startDate}/{endDate}",
     *     summary="Get click count trend across a range of dates between a date range",
     *     tags={"affiliate-referrals-date-trend-reports"},
     *     description="Get click count trend across a range of dates between a date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getClickCountAcrossDatesByDateRangeV1(string $affiliateId,string $startDate, string $endDate){

        $viewCount = $this->affiliateReferralRepository->getClickCountAcrossDatesByDateRange($affiliateId, $startDate,$endDate);
        $data = [
            "viewCount"=>$viewCount
        ];
        return response()->json($data);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/conversion-counts-across-dates/date-range/{startDate}/{endDate}",
     *     summary="Get conversion count trend across a range of dates between a date range",
     *     tags={"affiliate-referrals-date-trend-reports"},
     *     description="Get conversion count trend across a range of dates between a date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getConversionCountAcrossDatesByDateRangeV1(string $affiliateId,string $startDate, string $endDate){

        $conversionCount = $this->affiliateReferralRepository->getConversionCountAcrossDatesByDateRange($affiliateId, $startDate, $endDate);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/geo-conversion-counts/countries/date-range/{startDate}/{endDate}",
     *     summary="Get affiliate referral conversion count by country by date range",
     *     tags={"affiliate-referrals-geo-reports"},
     *     description="Get affiliate referral conversion count by country between date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getConversionGeoDistributionByCountryByDateRangeV1(string $affiliateId,string $startDate, string $endDate){
        $conversionCount = $this->affiliateReferralRepository
            ->getConversionGeoDistributionByCountryByDateRange($affiliateId,$startDate,$endDate);
        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/reports/affiliates/{affiliateId}/geo-conversion-counts/countries/{countryCode}/regions/date-range/{startDate}/{endDate}",
     *     summary="Get affiliate referral conversion count by country region between date range",
     *     tags={"affiliate-referrals-geo-reports"},
     *     description="Get affiliate referral conversion count by country region between date range",
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
     *         name="startDate",
     *         in="path",
     *         description="start date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="path",
     *         description="end date in the format yyyy-mm-dd",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
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
    public function getConversionGeoDistributionByRegionByDateRangeV1(string $affiliateId,string $countryCode, string $startDate, string $endDate){

        $conversionCount = $this->affiliateReferralRepository
            ->getConversionGeoDistributionByRegionByDateRange($affiliateId,$countryCode,$startDate,$endDa);

        $data = [
            "conversionCount"=>$conversionCount
        ];
        return response()->json($data);
    }


}

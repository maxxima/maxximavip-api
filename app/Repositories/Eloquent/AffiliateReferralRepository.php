<?php
namespace App\Repositories\Eloquent;
use App\Models\AffiliateReferral;
use App\Models\AffiliateReferralClick;
use App\Repositories\AffiliateReferralRepositoryInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use stdClass;
use GuzzleHttp\Http\Client;
class AffiliateReferralRepository implements AffiliateReferralRepositoryInterface{
    public function __construct(){

    }

    public function createReferral(string $memberId,int $locationId,$source, string $ipAddress){
        $session_key = uniqid();
        $affiliateReferral = new AffiliateReferral();
        $affiliateReferral->session_key = $session_key;
        $affiliateReferral->affiliate_id = $memberId;
        $affiliateReferral->created_timestamp = Carbon::now('UTC');
        $affiliateReferral->is_confirmed = false;
        $affiliateReferral->client_ip_address = $ipAddress;
        $affiliateReferral->source= $source;
        $affiliateReferral->location_id = $locationId;
        $affiliateReferral->save();
        return $affiliateReferral;
    }
    public function getViewCountByLastNumberOfDays(string $affiliateId, int $lastNumberOfDays){
        $date = Carbon::now('UTC')->subDays($lastNumberOfDays)->format('Y-m-d');
        return AffiliateReferral::
        where([['affiliate_id','=',$affiliateId],['created_timestamp','>=',$date]])->count();
    }

    public function getViewCountByDateRange(string $affiliateId, $startDate, $endDate){
        return AffiliateReferral::where([
            ['affiliate_id','=',$affiliateId],
            ['created_timestamp','>=',$startDate],['created_timestamp','<=',$endDate]])->count();
    }

    public function getClickCountByLastNumberOfDays(string $affiliateId, int $lastNumberOfDays){
        $date = Carbon::now('UTC')->subDays($lastNumberOfDays)->format('Y-m-d');
        return AffiliateReferralClick::
        where([['affiliate_id','=',$affiliateId],['created_timestamp','>=',$date]])->count();
    }

    public function getClickCountByDateRange(string $affiliateId, $startDate, $endDate){
        return AffiliateReferral::where([
            ['affiliate_id','=',$affiliateId],
            ['is_confirmed','=',1],
            ['created_timestamp','>=',$startDate],['created_timestamp','<=',$endDate]])->count();
    }

    public function getConversionCountByLastNumberOfDays(string $affiliateId, int $lastNumberOfDays)
    {
        $date = Carbon::now('UTC')->subDays($lastNumberOfDays)->format('Y-m-d');
        return AffiliateReferral::
        where([['affiliate_id','=',$affiliateId],['is_confirmed','=',true],['created_timestamp','>=',$date]])->count();
    }

    private function findTrendByDate($trends,$date){
        $dateString = $date->format('Y-m-d');
        foreach($trends as $trend){
            if($trend->date == $dateString){
                return $trend;
            }
        }
        return null;
    }

    private function createDateCountTrend(string $dateString, int $count){
        $dateCountTrend = new stdClass();
        $dateCountTrend->date = $dateString;
        $dateCountTrend->count = $count;
        return $dateCountTrend;
    }

    private function createDateCountTrendByDateRange(array $resultArray,string $startDate, string $endDate){
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $result = [];
        $trends =  $resultArray;
        foreach($dateRange as $date){
            $trendByDate = $this->findTrendByDate($trends,$date);
            $dateString = $date->format('Y-m-d');
            if($trendByDate != null){
                array_push($result,$this->createDateCountTrend($dateString,$trendByDate->count));
            }else{
                array_push($result,$this->createDateCountTrend($dateString,0));
            }
        }
        return $result;
    }

    private function getStartEndDateByLastNumberOfDays(int $lastNumberOfDays){
        $dateMinusDays = Carbon::now('UTC')->subDays($lastNumberOfDays)->format('Y-m-d');
        $dateToday = Carbon::now('UTC')->format('Y-m-d');
        return new StartEndDate($dateMinusDays,$dateToday);
    }

    public function getClickCountAcrossDates(string $affiliateId, $lastNumberOfDays){
        $startEndDate = $this->getStartEndDateByLastNumberOfDays($lastNumberOfDays);
        $sql = <<<EOD
SELECT DATE(created_timestamp) AS date,COUNT(created_timestamp) AS count FROM affiliate_referral_click
WHERE affiliate_id = :affiliateId AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
GROUP BY date ORDER BY date DESC
EOD;
        $resultArray = Db::select($sql,["affiliateId"=>$affiliateId,"startDate"=>$startEndDate->startDate,"endDate"=>$startEndDate->endDate]);

        return $this->createDateCountTrendByDateRange($resultArray,$startEndDate->startDate,$startEndDate->endDate);
    }

    public function getClickCountAcrossDatesByDateRange(string $affiliateId, $startDate, $endDate){
        $sql = <<<EOD
SELECT DATE(created_timestamp) AS date,COUNT(created_timestamp) AS count FROM affiliate_referral_click
WHERE affiliate_id = :affiliateId AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
GROUP BY date ORDER BY date DESC
EOD;
        $resultArray = Db::select($sql,["affiliateId"=>$affiliateId,"startDate"=>$startDate,"endDate"=>$endDate]);

        return $this->createDateCountTrendByDateRange($resultArray,$startDate,$endDate);
    }

    public function getViewCountAcrossDates(string $affiliateId,int $lastNumberOfDays){

        $startEndDate = $this->getStartEndDateByLastNumberOfDays($lastNumberOfDays);
        $sql = <<<EOD
SELECT DATE(created_timestamp) AS date,COUNT(created_timestamp) AS count FROM affiliate_referral
WHERE affiliate_id = :affiliateId AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
GROUP BY date ORDER BY date DESC
EOD;
        $resultArray = Db::select($sql,["affiliateId"=>$affiliateId,"startDate"=>$startEndDate->startDate,"endDate"=>$startEndDate->endDate]);

        return $this->createDateCountTrendByDateRange($resultArray,$startEndDate->startDate,$startEndDate->endDate);
    }

    public function getConversionCountAcrossDates(string $affiliateId, int $lastNumberOfDays){
        $sql= <<<EOD
SELECT DATE(confirmed_timestamp) AS date, COUNT(confirmed_timestamp) AS count FROM affiliate_referral
WHERE is_confirmed = 1 AND affiliate_id = :affiliateId AND DATE(confirmed_timestamp) >= :startDate AND DATE(confirmed_timestamp) <= :endDate
GROUP BY date ORDER BY date DESC
EOD;
        $startEndDate = $this->getStartEndDateByLastNumberOfDays($lastNumberOfDays);

        $resultArray = Db::select($sql,["affiliateId"=>$affiliateId,"startDate"=>$startEndDate->startDate,"endDate"=>$startEndDate->endDate]);
        return $this->createDateCountTrendByDateRange($resultArray,$startEndDate->startDate,$startEndDate->endDate);
    }

    public function getConversionCountAcrossDatesByDateRange(string $affiliateId, string $startDate, string $endDate){
        $sql= <<<EOD
SELECT DATE(confirmed_timestamp) AS date, COUNT(confirmed_timestamp) AS count FROM affiliate_referral
WHERE is_confirmed = 1 AND affiliate_id = :affiliateId AND DATE(confirmed_timestamp) >= :startDate AND DATE(confirmed_timestamp) <= :endDate
GROUP BY date ORDER BY date DESC
EOD;

        $resultArray = Db::select($sql,["affiliateId"=>$affiliateId,"startDate"=>$startDate,"endDate"=>$endDate]);
        return $this->createDateCountTrendByDateRange($resultArray,$startDate,$endDate);
    }

    public function getConversionGeoDistributionByCountryAcrossDates(string $affiliateId, int $lastNumberOfDays){
        $startEndDate = $this->getStartEndDateByLastNumberOfDays($lastNumberOfDays);
        /*
        $affiliateReferrals = AffiliateReferral::
        where([['affiliate_id','=',$affiliateId],
            ['is_confirmed','=',true]])
            ->where_date('created_timestamp','>=', $startEndDate->startDate)
            ->where_date('created_timestamp','<=',$startEndDate->endDate)
            ->get();
        */
        $sql = <<<EOD
SELECT * FROM affiliate_referral
WHERE affiliate_id = :affiliateId
AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
AND is_confirmed = true
EOD;

        $affiliateReferrals = AffiliateReferral::fromQuery($sql,["affiliateId"=>$affiliateId,"startDate"=>$startEndDate->startDate,"endDate"=>$startEndDate->endDate]);

        $ipAddressesWithNoInformationList = $affiliateReferrals->whereNull('geo_origin_country_name')->map(function ($referral) {
                return $referral->client_ip_address;
        })->unique();

        $totalPages = (int)floor((count($ipAddressesWithNoInformationList) / 100))+1;


        for($i=0; $i < $totalPages; $i++){
            $pagedIpAddressesWithNoInformationList = $ipAddressesWithNoInformationList->forPage($i, 100);
            $responseString = Http::post('http://ip-api.com/batch', $pagedIpAddressesWithNoInformationList->toArray());
            $ipAddressInfoList = collect(json_decode($responseString));
            $ipAddressInfoByIpAddressList = $ipAddressInfoList->groupBy('query');
            foreach($ipAddressInfoByIpAddressList as $ipAddress=>$ipAddressInfoList){
                $ipAddressInfo = $ipAddressInfoList[0];
                if($ipAddressInfo->status == "success"){
                    $ipAddress = $ipAddressInfo->query;
                    $countryName = $ipAddressInfo->country;
                    $countryCode = $ipAddressInfo->countryCode;
                    $lat = $ipAddressInfo->lat;
                    $lng = $ipAddressInfo->lon;
                    $region = $ipAddressInfo->region;
                    $regionName = $ipAddressInfo->regionName;
                    $city = $ipAddressInfo->city;

                    $sql = <<<EOD
UPDATE affiliate_referral SET
geo_origin_country_name = :geoOriginCountryName,
geo_origin_country_code = :geoOriginCountryCode,
geo_origin_country_lat = :geoOriginCountryLat,
geo_origin_country_lng = :geoOriginCountryLng,
geo_origin_region = :region,
geo_origin_region_name = :regionName,
geo_origin_city = :city                       
WHERE affiliate_id = :affiliateId AND client_ip_address = :clientIpAddress
AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
AND is_confirmed = true
EOD;
                    DB::statement($sql,['affiliateId'=>$affiliateId,
                        'startDate'=>$startEndDate->startDate,
                        'endDate'=>$startEndDate->endDate,
                        'geoOriginCountryCode'=>$countryCode,
                        'geoOriginCountryName'=>$countryName,
                        'geoOriginCountryLat'=>$lat,
                        'geoOriginCountryLng'=>$lng,
                        'clientIpAddress'=>$ipAddress,
                        'region'=>$region,
                        'regionName'=>$regionName,
                        'city'=>$city
                        ]);
                }
            }
        }
        $sql = <<<EOD
SELECT geo_origin_country_name AS countryName,
geo_origin_country_code As countryCode,
COUNT(geo_origin_country_code) count
FROM affiliate_referral
WHERE
affiliate_id = :affiliateId AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
AND is_confirmed = 1 AND geo_origin_country_code IS NOT NULL
GROUP BY
geo_origin_country_name,
geo_origin_country_code
ORDER BY count DESC
EOD;
        return Db::select($sql,["affiliateId"=>$affiliateId,"startDate"=>$startEndDate->startDate,"endDate"=>$startEndDate->endDate]);

    }

    public function getConversionGeoDistributionByRegionAcrossDates(string $affiliateId, string $countryCode, int $lastNumberOfDays){
        $startEndDate = $this->getStartEndDateByLastNumberOfDays($lastNumberOfDays);
        $sql = <<<EOD
SELECT geo_origin_region AS regionCode,
geo_origin_region_name As regionName,
COUNT(geo_origin_region) count
FROM affiliate_referral
WHERE
affiliate_id = :affiliateId 
AND geo_origin_country_code = :countryCode
AND DATE(created_timestamp) >= :startDate AND DATE(created_timestamp) <= :endDate
AND is_confirmed = 1 AND geo_origin_country_code IS NOT NULL
GROUP BY
geo_origin_region,
geo_origin_region_name
ORDER BY count DESC
EOD;
        return Db::select($sql,["affiliateId"=>$affiliateId,"countryCode"=>$countryCode,"startDate"=>$startEndDate->startDate,"endDate"=>$startEndDate->endDate]);
    }

    public function getReferralBySessionKey(string $sessionKey){
        return AffiliateReferral::find($sessionKey);
    }

    public function getLastReferralClick(string $sessionKey, string $url){
        return AffiliateReferralClick::where([['session_key','=',$sessionKey],
            ['url','=',$url]])->orderBy('created_timestamp',"DESC")->take(1)->first();
    }

    public function createReferralClick(string $affiliateId,string $sessionKey, string $url)
    {
        $newClick = new AffiliateReferralClick();
        $newClick->affiliate_id = $affiliateId;
        $newClick->session_key = $sessionKey;
        $newClick->url = $url;
        $newClick->created_timestamp = Carbon::now('UTC');
        $newClick->save();
        return $newClick;
    }
}

class StartEndDate{
    public string $startDate;
    public string $endDate;
    public function __construct(string $startDate,string $endDate){
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}

<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
/*
$router->get('/', function () use ($router) {
    return $router->app->version();
});
*/
$router->get('hello-world', 'HelloWorldController@helloWorld');

$router->get('/api/v1/health','HealthController@health');
$router->get('/api/v1/swagger-lumen-doc-generations','HealthController@generateSwaggerLumenDocs');

//affiliate-referral-click routes

$router->post('/api/v1/affiliate-clicks/sessions/{sessionKey}', [
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralClickController@createNewClickV1']);

//affiliate-referral routes
$router->get('/api/v1/affiliates/{affiliateId}/referral-sessions/locations/{locationId}', [
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralController@createAffiliateReferralV1']);

$router->post('/api/v1/affiliate-referrals/session-keys/{sessionKey}/conversion-confirmations', [
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralController@confirmReferralSessionV1']);


//affiliate-report routes
$router->get('/api/v1/reports/dashboard/affiliates/{affiliateId}/date-range/{startDate}/{endDate}',
    [
        'middleware'=>'apiKeyAuth',
        'uses'=>'AffiliateReferralReportController@dashboardByDateRangeV1']);

$router->get('/api/v1/reports/dashboard/affiliates/{affiliateId}/date-range/{startDate}/{endDate}',
    [
        'middleware'=>'apiKeyAuth',
        'uses'=>'AffiliateReferralReportController@dashboardByDateRangeV1']);

$router->get('/api/v1/reports/dashboard/referral-stats/affiliates/{affiliateId}/{startDate}/{endDate}',
    [
        'middleware'=>'apiKeyAuth',
        'uses'=>'AffiliateReferralReportController@dashboardReferralStatsByDateRangeV1']);


$router->get('/api/v1/reports/affiliates/{affiliateId}/total-conversion-counts/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getConversionCountByDateRangeV1'
]);


$router->get('/api/v1/reports/affiliates/{affiliateId}/geo-conversion-counts/countries/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getConversionGeoDistributionByCountryByDateRangeV1'
]);


$router->get('/api/v1/reports/affiliates/{affiliateId}/geo-conversion-counts/countries/{countryCode}/regions/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getConversionGeoDistributionByRegionByDateRangeV1'
]);


$router->get('/api/v1/reports/affiliates/{affiliateId}/total-view-counts/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getViewCountByDateRangeV1'
]);

$router->get('/api/v1/reports/affiliates/{affiliateId}/total-click-counts/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getClickCountByDateRangeV1'
]);


$router->get('/api/v1/reports/affiliates/{affiliateId}/conversion-counts-across-dates/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getConversionCountAcrossDatesByDateRangeV1'
]);

$router->get('/api/v1/reports/affiliates/{affiliateId}/clicks/total-counts/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralClickController@getTotalClickPerUrlDateRangeV1'
]);

$router->get('/api/v1/reports/affiliates/{affiliateId}/click-counts-across-dates/date-range/{startDate}/{endDate}',[
    'middleware'=>'apiKeyAuth',
    'uses'=>'AffiliateReferralReportController@getClickCountAcrossDatesByDateRangeV1'
]);



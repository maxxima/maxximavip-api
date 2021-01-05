<?php

    namespace App\Repositories;
    interface AffiliateReferralRepositoryInterface{
        public function createReferral(string $memberId,int $locationId,$source, string $ipAddress);
        public function getViewCountByLastNumberOfDays(string $affiliateId, int $lastNumberOfDays);
        public function getClickCountByLastNumberOfDays(string $affiliateId, int $lastNumberOfDays);
        public function getConversionCountByLastNumberOfDays(string $affiliateId, int $lastNumberOfDays);
        public function getViewCountAcrossDates(string $affiliateId,int $lastNumberOfDays);
        public function getConversionCountAcrossDates(string $affiliateId, int $lastNumberOfDays);
        public function getConversionGeoDistributionAcrossDates(string $affiliateId, int $lastNumberOfDays);
        public function createReferralClick(string $affiliateId, string $sessionKey,string $url);
        public function getReferralBySessionKey(string $sessionKey);
        public function getLastReferralClick(string $sessionKey, string $url);
        public function getClickCountAcrossDates(string $affiliateId, $lastNumberOfDays);
    }

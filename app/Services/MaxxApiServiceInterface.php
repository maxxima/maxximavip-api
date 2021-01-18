<?php


namespace App\Services;


interface MaxxApiServiceInterface
{
    public function verifyMemberId(string $memberId);
    public function verifyMember(string $memberId);
}

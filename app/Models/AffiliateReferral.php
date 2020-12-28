<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models;
    class AffiliateReferral extends Model{
        public $primaryKey = 'session_key';
        public $table = "affiliate_referral";
        public $timestamps = false;
        public $incrementing = false;
    }

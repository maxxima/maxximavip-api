<?php 


namespace App\Providers; 
use Illuminate\Support\ServiceProvider;
use App\Repositories\AffiliateReferralRepositoryInterface;
use App\Repositories\Eloquent\AffiliateReferralRepository;
class RepositoryServiceProvider extends ServiceProvider 
{ 
   /** 
    * Register services. 
    * 
    * @return void  
    */ 
   public function register() 
   { 
       $this->app->bind(AffiliateReferralRepositoryInterface::class, AffiliateReferralRepository::class);
   }
}
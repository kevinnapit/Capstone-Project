<?php

namespace App\Providers;

use App\Models\OrderChannel;
use App\Models\PaperType;
use App\Models\PaymentMethod;
use App\Models\PrintMode;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\Unit;
use App\Models\User;
use App\Observers\MasterDataAuditObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            User::class,
            Role::class,
            ProductCategory::class,
            Unit::class,
            Product::class,
            PaperType::class,
            ServiceType::class,
            PrintMode::class,
            ServicePrice::class,
            OrderChannel::class,
            PaymentMethod::class,
        ] as $model) {
            $model::observe(MasterDataAuditObserver::class);
        }
    }
}

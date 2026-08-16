<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Listeners\RegistrarEntrada;
use App\Listeners\RegistrarSalida;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

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
public function boot()
{
    Gate::before(function ($user, $ability) {
        return $user->hasRole('Superadmin') ? true : null;
    });
}




    protected $listen = [
    Login::class => [
        RegistrarEntrada::class,
    ],
    Logout::class => [
        RegistrarSalida::class,
    ],
];
}

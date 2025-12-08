<?php

namespace App\Providers;

use App\Models\WaffleDay;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_BEFORE,
            function () {
                $user = Auth::user();
                if (! $user) {
                    return null;
                }

                $nextWaffleDay = WaffleDay::where('date', '>=', now())->orderBy('date')->first();

                if (! $nextWaffleDay) {
                    return null;
                }

                $dateFormatted = $nextWaffleDay->date->format('l, M j, Y'); // e.g. Thursday, Dec 11, 2025

                return view('components.next-waffle-day-banner', [
                    'nextWaffleDay' => $nextWaffleDay,
                    'dateFormatted' => $dateFormatted,
                ]);
            }
        );
    }
}

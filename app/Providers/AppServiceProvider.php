<?php

namespace App\Providers;

use App\Models\WaffleDay;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

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

                $dateFormatted = $nextWaffleDay->date
                    ->locale(app()->getLocale())
                    ->translatedFormat('l, M j, Y');  // e.g. Thursday, Dec 11, 2025 or Donnerstag, Jan 8, 2026

                return view('components.next-waffle-day-banner', [
                    'nextWaffleDay' => $nextWaffleDay,
                    'dateFormatted' => $dateFormatted,
                ]);
            }
        );

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en','de']);
        });
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\RemoteWaffleEating;
use App\Models\WaffleEating;
use BackedEnum;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class Leaderboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';
    protected string $view = 'filament.pages.leaderboard';
    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('Leaderboard');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Waffle Leaderboard');
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (array $filters): Collection {
                $selectedYear = $filters['year']['value'] ?? now()->year;
                $location = $filters['location']['value'] ?? 'all';

                /** -------------------------
                 * Office Waffle Eatings
                 * ------------------------- */
                $office = collect();
                if (in_array($location, ['all', 'office'], true)) {
                    $office = WaffleEating::query()
                        ->selectRaw('user_id, SUM(count) as total')
                        ->whereYear('date', $selectedYear)
                        ->groupBy('user_id')
                        ->pluck('total', 'user_id'); // [user_id => total]
                }

                /** -------------------------
                 * Remote waffle eatings (approved only)
                 * ------------------------- */
                $remote = collect();
                if (in_array($location, ['all', 'remote'], true)) {
                    $remote = RemoteWaffleEating::query()
                        ->selectRaw('user_id, SUM(count) as total')
                        ->whereNotNull('approved_by')
                        ->whereYear('date', $selectedYear)
                        ->groupBy('user_id')
                        ->pluck('total', 'user_id');
                }

                /** -------------------------
                 * Collect totals
                 * ------------------------- */
                $totals = collect();
                foreach ($office as $userId => $count) {
                    $totals[$userId] = ($totals[$userId] ?? 0) + (int) $count;
                }
                foreach ($remote as $userId => $count) {
                    $totals[$userId] = ($totals[$userId] ?? 0) + (int) $count;
                }

                /** -------------------------
                 * Build leaderboard rows
                 * ------------------------- */
                return User::query()
                    ->get()
                    ->map(fn (User $user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar_url' => $user->getFilamentAvatarUrl(),
                        'waffles_this_year' => (int) ($totals[$user->id] ?? 0),
                    ])
                    ->sortByDesc('waffles_this_year')
                    ->values()
                    ->map(fn ($row, $index) => [
                        ...$row,
                        'rank' => $index + 1,
                    ]);
            })
            ->columns([
                TextColumn::make('rank')
                    ->label(__('Rank'))
                    ->state(fn(array $record) => match ($record['rank']) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => (string) $record['rank'],
                    }),
                ImageColumn::make('avatar_url')->label('')->circular(),
                TextColumn::make('name')->label(__('User')),
                TextColumn::make('waffles_this_year')->label(__('Waffles Eaten')),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label(__('Year'))
                    ->options(function () {
                        $minOffice = WaffleEating::min('date');
                        $minRemote = RemoteWaffleEating::min('date');
                        $earliest = collect([$minOffice, $minRemote])->filter()->min();
                        $startYear = $earliest ? (int) date('Y', strtotime($earliest)) : now()->year;
                        $years = range(now()->year, $startYear);
                        return array_combine($years, $years);
                    })
                    ->default(now()->year)
                    ->selectablePlaceholder(false),

                SelectFilter::make('location')
                    ->label(__('waffle-dashboard.location'))
                    ->options([
                        'all' => __('All'),
                        'office' => __('Office'),
                        'remote' => __('Remote'),
                    ])
                    ->default('all')
                    ->selectablePlaceholder(false),
            ])
            ->defaultSort('waffles_this_year', 'desc');
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\RemoteWaffleEating;
use App\Models\WaffleEating;
use BackedEnum;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class Leaderboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Leaderboard';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $title = 'Waffle Leaderboard';
    protected string $view = 'filament.pages.leaderboard';
    protected static ?int $navigationSort = 20;

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
                    ->label('Rank')
                    ->state(fn(array $record) => match ($record['rank']) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => (string) $record['rank'],
                    }),
                TextColumn::make('name')->label('User'),
                TextColumn::make('waffles_this_year')->label('Waffles Eaten'),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Year')
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
                    ->label('Location')
                    ->options([
                        'all' => 'All',
                        'office' => 'Office',
                        'remote' => 'Remote',
                    ])
                    ->default('all')
                    ->selectablePlaceholder(false),
            ])
            ->defaultSort('waffles_this_year', 'desc');
    }
}

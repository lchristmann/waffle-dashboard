<?php

namespace App\Filament\Pages;

use App\Models\WaffleEating;
use BackedEnum;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
            ->records(function (?array $filters) {
                // Always use a valid year: selected filter or default to current year
                $selectedYear = $filters['year']['value'] ?? now()->year;

                // Sum waffles per user for the year
                $waffleCounts = WaffleEating::query()
                    ->selectRaw('user_id, COALESCE(SUM(count), 0) as total')
                    ->whereYear('date', $selectedYear)
                    ->groupBy('user_id')
                    ->pluck('total', 'user_id'); // [user_id => total]

                // Get all users
                $users = User::all();

                // Combine data
                $data = $users->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'year' => $selectedYear,
                    'waffles_this_year' => $waffleCounts[$user->id] ?? 0,
                ]);

                // Sort descending by waffles eaten and add rank
                return $data
                    ->sortByDesc('waffles_this_year')
                    ->values()
                    ->map(fn($record, $index) => array_merge($record, [
                        'rank' => $index + 1,
                    ]));
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
                        $oldestYear = (int) WaffleEating::selectRaw('EXTRACT(YEAR FROM MIN(date)) AS year')->value('year');
                        $years = range(now()->year, $oldestYear);
                        return array_combine($years, $years);
                    })
                    ->default(now()->year)
                    ->selectablePlaceholder(false),
            ])
            ->defaultSort('waffles_this_year', 'desc');
    }
}

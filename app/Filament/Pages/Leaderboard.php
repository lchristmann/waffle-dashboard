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

    public function table(Table $table): Table
    {
        // Get the oldest year with a waffle record
        $oldestYear = WaffleEating::selectRaw('EXTRACT(YEAR FROM MIN(date)) AS year')->value('year');
        // Generate years for the SelectFilter (current year down to oldest)
        $years = range(now()->year, $oldestYear);

        return $table
            ->records(function (?array $filters, ?string $sortColumn, ?string $sortDirection) use ($years) {
                // Always use a valid year: selected filter or default to current year
                $selectedYear = $filters['year']['value'] ?? now()->year;

                // Map users to their waffle counts for the selected year
                $data = User::all()->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'year' => $selectedYear,
                    'waffles_this_year' => $user->wafflesEatenInYear($selectedYear),
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
                    ->options(array_combine($years, $years))
                    ->default(now()->year)
                    ->selectablePlaceholder(false),
            ])
            ->defaultSort('waffles_this_year', 'desc');
    }
}

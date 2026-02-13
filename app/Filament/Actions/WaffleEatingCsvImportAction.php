<?php

namespace App\Filament\Actions;

use App\Models\User;
use App\Models\WaffleEating;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class WaffleEatingCsvImportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'waffleEatingCsvImport';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('Import CSV'))
            ->modalHeading(__('Import Waffle Eatings from CSV'))
            ->schema([
                FileUpload::make('file')
                    ->label(__('CSV File'))
                    ->helperText(__('CSV must have a header row (e.g. Person, 2026-01-22, 2026-02-12). First column is user names. Other columns are waffle counts with date as header cell. Stop processing at first fully empty line.'))
                    ->acceptedFileTypes(['text/csv', 'text/plain'])
                    ->directory('csv-imports')
                    ->required(),

                TextInput::make('date_header')
                    ->label(__('Date Column Header'))
                    ->placeholder(__('2026-01-15'))
                    ->helperText(__('Enter the exact column header for the date you want to import.'))
                    ->required(),
            ])
            ->action(fn (array $data) => $this->handleImport($data));
    }

    protected function handleImport(array $data): void
    {
        try {
            $path = storage_path('app/private/' . $data['file']);
            $dateHeader = trim($data['date_header']);

            $rows = array_map('str_getcsv', file($path));

            if (empty($rows)) {
                throw new Exception(__('Empty CSV file.'));
            }

            if (!isset($rows[0]) || count(array_filter($rows[0])) === 0) {
                throw new Exception(__('CSV header row missing.'));
            }

            $header = $rows[0];
            $dateIndex = array_search($dateHeader, $header);

            if ($dateIndex === false) {
                throw new Exception(__('Selected date column header not in CSV.'));
            }

            $createdCount = 0;

            DB::transaction(function () use ($rows, $dateIndex, $dateHeader, &$createdCount) {
                $date = Carbon::parse($dateHeader);

                foreach (array_slice($rows, 1) as $row) {
                    // stop at first fully empty line
                    if (count(array_filter($row)) === 0) {
                        break;
                    }

                    $name = trim($row[0] ?? '');
                    $count = trim($row[$dateIndex] ?? '');

                    if ($name === '' || $count === '') {
                        continue;
                    }

                    $user = User::where('name', $name)->first();

                    if (!$user) {
                        throw new Exception(__("User ':name' not found.", ['name' => $name]));
                    }

                    WaffleEating::create([
                        'date' => $date,
                        'count' => (int)$count,
                        'user_id' => $user->id,
                        'entered_by_user_id' => auth()->id(),
                    ]);

                    $createdCount++;
                }
            });

            Notification::make()
                ->title(__('Import Successful'))
                ->body(__('Created :count waffle eatings.', ['count' => $createdCount]))
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Import Failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Hash;

class UserBulkCreateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'userBulkCreate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Bulk Create')
            ->modalHeading('Create Multiple Users')
            ->schema([
                Repeater::make('users')
                    ->addActionLabel('Add another')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('name')->unique()->required()->maxLength(255),
                                TextInput::make('email')->unique()->email()->maxLength(255)
                                    ->required(fn (Get $get): bool => filled($get('password')))
                                    ->live(onBlur: true),

                                TextInput::make('password')
                                    ->password()
                                    ->required(fn (Get $get): bool => filled($get('email')))
                                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                                    ->dehydrated()
                                    ->live(onBlur: true),

                                Toggle::make('is_admin')->label('Admin')->inline(false),
                            ])
                    ])
                    ->reorderable(false)
                    ->defaultItems(2),
            ])
            ->action(function (array $data): void {
                foreach ($data['users'] as $userData) {
                    User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'] ?? null,
                        'password' => $userData['password'] ?? null,
                        'is_admin' => $userData['is_admin'] ?? false,
                    ]);
                }
            });
    }
}

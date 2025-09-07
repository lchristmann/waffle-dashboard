<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(fn (string $context) => $context === 'create')->maxLength(255),
                TextInput::make('email')->required(fn (string $context) => $context === 'create')->email()->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),

                Toggle::make('is_admin')->label('Admin')->inline(false)
                    ->disabled(fn (?User $record) => $record && $record->id === auth()->id())
                    ->helperText(fn (?User $record) => $record && $record->id === auth()->id()
                        ? 'You cannot remove your own admin status'
                        : null)
            ]);
    }
}

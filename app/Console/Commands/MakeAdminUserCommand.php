<?php

namespace App\Console\Commands;

use Filament\Commands\MakeUserCommand;

class MakeAdminUserCommand extends MakeUserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filament-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Filament admin user';

    /**
     * @return array{'name': string, 'email': string, 'password': string, 'is_admin': bool}
     */
    protected function getUserData(): array
    {
        $data = parent::getUserData();

        // Always create as admin (no prompt)
        $data['is_admin'] = true;

        return $data;
    }
}

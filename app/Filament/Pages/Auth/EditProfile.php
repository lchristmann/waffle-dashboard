<?php

namespace App\Filament\Pages\Auth;

use App\Constants\StorageConstants;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
                FileUpload::make('avatar')
                    ->label(__('Profile Picture'))
                    ->directory(StorageConstants::AVATARS)
                    ->disk('local')
                    ->visibility('private')
                    ->image()
                    ->automaticallyCropImagesToAspectRatio('1:1')
                    ->automaticallyResizeImagesMode('cover')
                    ->automaticallyResizeImagesToWidth('512')
                    ->automaticallyResizeImagesToHeight('512')
                    ->maxSize(2048)
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file) => auth()->id() . '.' . $file->extension()
                    ),
            ]);
    }

    protected function afterSave(): void
    {
        if ($this->getUser()->wasChanged('avatar')) {
            $this->redirect(request()->header('Referer'));
        }
    }
}

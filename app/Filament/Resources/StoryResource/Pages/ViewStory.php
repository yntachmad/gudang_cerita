<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Models\Story;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\StoryResource;

class ViewStory extends ViewRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('Approve')
            ->label('Approve')
            ->requiresConfirmation()
            ->visible(fn (Story $record) => auth()->user()->hasRole('Reviewer') && $record->status === 'in review' && $record->reviewer_id === auth()->id())
            ->action(function (Story $record) {
                    $record->update([
                        'status' => 'approved',
                    ]);
                    return redirect(Static::getUrl('list'));
                }),
        ];
    }
}

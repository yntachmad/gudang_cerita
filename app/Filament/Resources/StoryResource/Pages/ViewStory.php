<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Models\Story;
use Filament\Actions;
use Filament\Actions\Action;
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

            ->visible(fn (Story $record) => auth()->user()->hasRole('Reviewer') && $record->status === 'in review' && $record->reviewer_id === auth()->id())
            ->form([
                \Filament\Forms\Components\Textarea::make('feedback')
                ->label('Feedback')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            ])
            ->Action(function (Story $record, array $data) {
                $record->update([
                    'status' => 'approved',
                    'feedback' => $data['feedback'],
                ]);
                // return redirect(Static::getUrl(['list']));

            }),

        ];
    }
}

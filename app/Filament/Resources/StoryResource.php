<?php

namespace App\Filament\Resources;

use Filament\Forms;
// use App\Models\User;
use Filament\Tables;
use App\Models\Story;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Foundation\Auth\User;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StoryResource\RelationManagers;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Guard;

class StoryResource extends Resource
{
    protected static ?string $model = Story::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('story_content')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull(),
                // Forms\Components\TextInput::make('status')
                //     ->required()
                //     ->maxLength(100)
                //     ->default('waiting for review'),
                // Forms\Components\TextInput::make('author_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('reviewer_id')
                //     ->numeric(),
                // Forms\Components\Textarea::make('feedback')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (Story $record) => match ($record->status) {
                    'waiting for review' => 'yellow',
                    'in review' => 'blue',
                    'rework' => 'orange',
                    'cancelled' => 'red',
                    'approved' => 'green',
                    default => 'gray',
                })
                ->searchable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->visible(fn () =>auth()->user()->hasRole('Admin'))
                ->requiresConfirmation()
                ->successNotificationTitle('Story deleted successfully.')
                ->failureNotificationTitle('Failed to delete the story.'),
                Tables\Actions\Action::make('Review')
                ->label('Review')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn (Story $record) => auth()->user()->hasRole('Reviewer') && $record->status === 'waiting for review' && ($record->reviewer_id === auth()->user()->id || is_null($record->reviewer_id)))
                ->requiresConfirmation()
                ->action(function (Story $record) {
                    $record->update([
                        'status' => 'in review',
                        'reviewer_id' => Auth::id(),
                    ]);
                    return redirect(Static::getUrl('view', ['record' => $record]));
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->visible(fn () => auth()->user()->hasRole('Admin')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStory::route('/create'),
            'view' => Pages\ViewStory::route('/{record}'),
            'edit' => Pages\EditStory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
           ->when(auth()->user()->hasRole('Writer'), function ($query) {
                $query->where('author_id', auth()->user()->id);
            });
    }


}

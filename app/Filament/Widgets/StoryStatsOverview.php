<?php

namespace App\Filament\Widgets;

use App\Models\Story;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Container\Attributes\Auth as AttributesAuth;
use Illuminate\Support\Facades\Auth;

class StoryStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {

        return [

            Stat::make('Waiting for Review', Story::where('status', 'waiting for review')->when(auth()->user()->hasRole('Writer'), function ($query) {
                $query->where('author_id', Auth::id());
            })->count()),


            Stat::make('In Review', Story::where('status', 'in review')->when(auth()->user()->hasRole('Writer'), function ($query) {
                $query->where('author_id', Auth::id());
            })->count()),

            Stat::make('Rework', Story::where('status', 'rework')->when(auth()->user()->hasRole('Writer'), function ($query) {
                $query->where('author_id', Auth::id());
            })->count()),

            Stat::make('Cancelled', Story::where('status', 'cancelled')->when(auth()->user()->hasRole('Writer'), function ($query) {
                $query->where('author_id', Auth::id());
            })->count()),

            Stat::make('Approved', Story::where('status', 'approved')->when(auth()->user()->hasRole('Writer'), function ($query) {
                $query->where('author_id', Auth::id());
            })->count()),

        ];

        }
    }


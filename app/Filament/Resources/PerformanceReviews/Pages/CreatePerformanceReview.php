<?php

namespace App\Filament\Resources\PerformanceReviews\Pages;

use App\Filament\Resources\PerformanceReviews\PerformanceReviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerformanceReview extends CreateRecord
{
    protected static string $resource = PerformanceReviewResource::class;
}

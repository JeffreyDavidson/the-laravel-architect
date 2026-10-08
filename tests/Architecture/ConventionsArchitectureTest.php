<?php

use App\Services\ContentArchive\ProductionContentSource;
use App\Services\ContentArchive\PublicContentArchiveExporter;
use App\Services\ContentArchive\PublicContentArchiveImporter;
use App\Services\FeaturedImageGenerator;
use App\Services\Health\NightwatchHealthMonitor;
use App\Services\OgImageGenerator;
use App\Services\YouTubeService;

arch('declares strict types in every application file')
    ->expect('App')
    ->toUseStrictTypes();

arch('makes application classes final')
    ->expect('App')
    ->classes()
    ->toBeFinal()
    ->ignoring([
        // Tests replace these with jasonmccreary/double test doubles, which subclass the target.
        FeaturedImageGenerator::class,
        NightwatchHealthMonitor::class,
        OgImageGenerator::class,
        ProductionContentSource::class,
        PublicContentArchiveExporter::class,
        PublicContentArchiveImporter::class,
        YouTubeService::class,
    ]);

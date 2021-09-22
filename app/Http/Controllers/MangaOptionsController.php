<?php

namespace App\Http\Controllers;

use App\Http\Resources\DemographicResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\ThemeResource;
use App\Models\MangaDemographic;
use App\Models\MangaGenre;
use App\Models\MangaTheme;
use Illuminate\Http\Request;

class MangaOptionsController extends Controller
{
    public function searchParams(){
        $genres = GenreResource::collection(MangaGenre::all());
        $demographics = DemographicResource::collection(MangaDemographic::all());
        $themes = ThemeResource::collection(MangaTheme::all());
        return ['g' => $genres, 'd' => $demographics, 't' => $themes];
    }
}

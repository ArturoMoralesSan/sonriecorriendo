<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Product;
use App\Models\Race;
use App\Models\RaceGallery;
use App\Models\Sponsor;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();

        $races = Race::query()
            ->with([
                'distances.prices' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('price');
                },
            ])
            ->whereIn('status', [
                'published',
                'registration_open',
            ])
            ->whereDate('event_date', '>=', $today)
            ->orderBy('event_date')
            ->limit(4)
            ->get();

        $clubs = Club::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(4)
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->limit(4)
            ->get();

        $gallery = RaceGallery::query()
            ->with([
                'race:id,name,slug,status',
            ])
            ->where('is_active', true)
            ->whereHas('race', function ($query) {
                $query->whereIn('status', [
                    'published',
                    'registration_open',
                    'finished',
                ]);
            })
            ->orderBy('sort_order')
            ->latest('id')
            ->limit(4)
            ->get();

        $sponsors = Sponsor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(12)
            ->get();

        return Inertia::render('Welcome', [
            'races' => $races,
            'clubs' => $clubs,
            'products' => $products,
            'gallery' => $gallery,
            'sponsors' => $sponsors,
        ]);
    }

    /**
     * Todos los eventos públicos.
     */
    public function races(): Response
    {
        $races = Race::query()
            ->with([
                'distances.prices' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('price');
                },
            ])
            ->whereIn('status', [
                'published',
                'registration_open',
                'registration_closed',
                'finished',
            ])
            ->orderBy('event_date')
            ->get();

        return Inertia::render('Races', [
            'events' => $races,
        ]);
    }

    public function race(string $slug): Response
    {
        $race = Race::query()
            ->with([
                'distances' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with([
                            'prices' => function ($query) {
                                $query
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->orderBy('price');
                            },

                            'inclusions' => function ($query) {
                                $query
                                    ->where('included', true)
                                    ->orderBy('sort_order');
                            },

                            'categories' => function ($query) {
                                $query
                                    ->where('is_active', true)
                                    ->orderBy('sort_order');
                            },
                        ]);
                },

                'gallery' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->where('slug', $slug)
            ->whereIn('status', [
                'published',
                'registration_open',
                'registration_closed',
                'finished',
            ])
            ->firstOrFail();

        return Inertia::render('RaceShow', [
            'race' => $race,
        ]);
    }

    public function galleries(): Response
    {
        $gallery = RaceGallery::query()
            ->with([
                'race:id,name,slug,status',
            ])
            ->where('is_active', true)
            ->whereHas('race', function ($query) {
                $query->whereIn('status', [
                    'published',
                    'registration_open',
                    'finished',
                ]);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Gallery', [
            'gallery' => $gallery,
        ]);
    }

    public function gallery(string $slug)
    {
        $race = Race::query()
            ->where('slug', $slug)
            ->whereIn('status', [
                'published',
                'registration_open',
                'finished',
            ])
            ->firstOrFail();

        $gallery = RaceGallery::query()
            ->where('race_id', $race->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'race_id',
                'image',
                'sort_order',
            ]);

        return Inertia::render('GalleryShow', [
            'race' => $race->only([
                'id',
                'name',
                'slug',
            ]),
            'gallery' => $gallery,
        ]);
    }

    public function shop(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Shop', [
            'products' => $products,
        ]);
    }

    public function product(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('ProductShow', [
            'product' => $product,
        ]);
    }

    public function clubs(): Response
    {
        $clubs = Club::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Clubs', [
            'clubs' => $clubs,
        ]);
    }

    public function club(string $slug): Response
    {
        $club = Club::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('ClubShow', [
            'club' => $club,
        ]);
    }
}

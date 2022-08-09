<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = Request::input('perPage') ?: 5;

        return Inertia::render('Movies/Index', [
            'movies' => Movie::query()
                ->when(Request::input('search'), function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%");
                })->when(Request::has('column'), function($query){
                    $query->orderBy(Request::input('column'), Request::input('direction'));
                })
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => Request::only(['search', 'perPage', 'column', 'direction'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $movie = Movie::where('tmdb_id', Request::input('movieTMDBId'))->exists();
        if ($movie) {
            return Redirect::back()->with('flash.banner', 'Movie Exists.');
        }

        $apiMovie = Http::asJson()->get(config('services.tmdb.endpoint').'movie/'. Request::input('movieTMDBId'). '?api_key=' . config('services.tmdb.secret') . '&language=en-US');

        if ($apiMovie->successful()) {

            $created_movie = Movie::create([
                'tmdb_id' => $apiMovie['id'],
                'title' => $apiMovie['title'],
                'runtime' => $apiMovie['runtime'],
                'rating' => $apiMovie['vote_average'],
                'release_date' => $apiMovie['release_date'],
                'lang' => $apiMovie['original_language'],
                'video_format' => 'HD',
                'is_public' => false,
                'overview' => $apiMovie['overview'],
                'poster_path' => $apiMovie['poster_path'],
                'backdrop_path' => $apiMovie['backdrop_path']
            ]);
            $tmdb_genres = $apiMovie['genres'];
            $tmdb_genres_ids = collect($tmdb_genres)->pluck('id');
            $genres = Genre::whereIn('tmdb_id', $tmdb_genres_ids)->get();
            $created_movie->genres()->attach($genres);
            return Redirect::back()->with('flash.banner', 'Movie create.');

        } else {
            return Redirect::back()->with('flash.banner', 'Api Error.');

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Movie $movie)
    {
        return Inertia::render('Movies/Edit', ['movie' => $movie]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Movie $movie)
    {
        $validated = Request::validate([
            'title' => 'required',
            'poster_path' => 'required',
            'runtime' => 'required',
            'lang' => 'required',
            'video_format' => 'required',
            'rating' => 'required',
            'backdrop_path' => 'required',
            'overview' => 'required',
            'is_public' => 'required'
        ]);

        $movie->update($validated);
        return Redirect::route('admin.movies.index')->with('flash.banner', 'Movie Updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
        return Redirect::route('admin.movies.index')->with('flash.banner', 'Movie Deleted.')->with('flash.bannerStyle', 'danger');
    }
}

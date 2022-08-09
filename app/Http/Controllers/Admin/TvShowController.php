<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvShow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TvShowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = Request::input('perPage') ?: 5;

        return Inertia::render('TvShows/Index', [
            'tvShows' => TvShow::query()
                ->when(Request::input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => Request::only(['search', 'perPage'])
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
    public function store()
    {
        $tvShow = TvShow::where('tmdb_id', Request::input('tvShowTMDBId'))->first();
        if ($tvShow) {
            return Redirect::back()->with('flash.banner', 'Tv Show Exists.');
        }

        $tmdb_tv = Http::asJson()->get(config('services.tmdb.endpoint') . 'tv/' . Request::input('tvShowTMDBId') . '?api_key=' . config('services.tmdb.secret') . '&language=en-US');
        if ($tmdb_tv->successful()) {
            TvShow::create([
                'tmdb_id' => $tmdb_tv['id'],
                'name'    => $tmdb_tv['name'],
                'slug' => Str::slug($tmdb_tv['name']),
                'poster_path' => $tmdb_tv['poster_path'],
                'created_year' => $tmdb_tv['first_air_date']
            ]);
            return Redirect::back()->with('flash.banner', 'Tv Show created.');
        } else {
            return Redirect::back()->with('flash.banner', 'Api error.');
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
    public function edit(TvShow $tvShow)
    {
        return Inertia::render('TvShows/Edit', ['tvShow' => $tvShow]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TvShow $tvShow)
    {
        $validated = Request::validate([
            'name'    => 'required',
            'poster_path' => 'required'
        ]);
        $tvShow->update($validated);
        return Redirect::route('admin.tv-shows.index')->with('flash.banner', 'Tv Show updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TvShow $tvShow)
    {
        $tvShow->delete();
        return Redirect::route('admin.tv-shows.index')->with('flash.banner', 'Tv Show deleted.')->with('flash.bannerStyle', 'danger');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\Movie;
use App\Models\Tag;
use App\Models\TrailerUrl;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class MovieAttachController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Movie $movie)
    {
        return Inertia::render('Movies/Attach', [
            'movie' => $movie,
            'trailers' => $movie->trailers,
            'downloads' => $movie->downloads,
            'casts' => Cast::all('id', 'name'),
            'tags' => Tag::all('id', 'tag_name'),
            'movieCasts' => $movie->casts,
            'movieTags' => $movie->tags,
        ]);
    }

    public function addTrailer(Movie $movie)
    {
        $movie->trailers()->create(Request::validate([
            'name' => 'required',
            'embed_html' => 'required',
        ]));
        return Redirect::back()->with('flash.banner', 'Trailer Added.');
    }

    public function destroyTrailer(TrailerUrl $trailerUrl)
    {
        $trailerUrl->delete();
        return Redirect::back()->with('flash.banner', 'Trailer deleted.');
    }

    public function addCast(Movie $movie)
    {
        $casts = Request::input('casts');
        $cast_ids = collect($casts)->pluck('id');
        $movie->casts()->sync($cast_ids);
        return Redirect::back()->with('flash.banner', 'Csats attached.');
    }
    public function addTag(Movie $movie)
    {
        $tags = Request::input('tags');
        $tag_ids = collect($tags)->pluck('id');
        $movie->tags()->sync($tag_ids);
        return Redirect::back()->with('flash.banner', 'Tags attached.');
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
        //
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

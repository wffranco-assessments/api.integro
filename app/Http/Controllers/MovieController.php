<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index()
    {
        return Movie::get();
    }

    public function show($id)
    {
        $movie = Movie::find($id);

        if ($movie) {
            return response()->json($movie);
        }

        return response()->json([
            __('movie.message') => trans_choice('movie.not_found', 1),
        ], 400);
    }

    public function store(StoreMovieRequest $request)
    {
        $data = $request->all();
        // dd($data);
        $movie = Movie::create($request->all());

        if ($movie) {
            return response()->json($movie);
        }

        return response()->json([
            __('movie.message') => __('movie.not_added'),
        ], 500);
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                __('movie.message') => trans_choice('movie.not_found', 1),
            ], 400);
        }

        if ($movie->created_by === auth()->user()->id) {
            $updated = $movie->fill($request->all())->save();

            if ($updated) {
                return response()->json($movie);
            }
        }

        return response()->json([
            __('movie.message') => __('movie.not_updated'),
        ], 500);
    }

    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                __('movie.message') => trans_choice('movie.not_found', 1),
            ], 400);
        }

        if ($movie->created_by === auth()->user()->id && $movie->delete()) {
            return response()->json([
                __('movie.message') => __('movie.deleted'),
            ], 200);
        }

        return response()->json([
            __('movie.message') => __('movie.not_deleted'),
        ], 500);
    }
}

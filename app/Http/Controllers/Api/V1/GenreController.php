<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;


class GenreController extends Controller
{
	public function index()
	{
		return new GenreCollection(Genre::paginate());
	}

	public function store(Request $request) {}

	public function show(Genre $genre) {
		return new GenreResource($genre);
	}

	public function update(Request $request, Genre $genre) {}

	public function destroy(Genre $genre) {}
}

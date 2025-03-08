<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;


class GenreController extends Controller
{
	public function index()
	{
		
	}

	public function store(Request $request) {}

	public function show(Genre $genre) {}

	public function update(Request $request, Genre $genre) {}

	public function destroy(Genre $genre) {}
}

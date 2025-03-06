<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;


class AuthorController extends Controller
{
	public function index()
	{
		
	}

	public function store(Request $request) {}

	public function show(Author $author) {}

	public function update(Request $request, Author $author) {}

	public function destroy(Author $author) {}
}

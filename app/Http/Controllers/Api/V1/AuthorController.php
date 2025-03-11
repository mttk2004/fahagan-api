<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;


class AuthorController extends Controller
{
	/**
	 * Get all authors
	 *
	 * @return void
	 * @group Authors
	 * @unauthenticated
	 */
	public function index()
	{

	}

	/**
	 * Create a new author
	 *
	 * @param Request $request
	 * @return void
	 * @group Authors
	 */
	public function store(Request $request) {}

	/**
	 * Get an author
	 *
	 * @param Author $author
	 * @return void
	 * @group Authors
	 * @unauthenticated
	 */
	public function show(Author $author) {}

	/**
	 * Update an author
	 *
	 * @param Request $request
	 * @param Author $author
	 * @return void
	 * @group Authors
	 */
	public function update(Request $request, Author $author) {}

	/**
	 * Delete an author
	 *
	 * @param Author $author
	 * @return void
	 * @group Authors
	 */
	public function destroy(Author $author) {}
}

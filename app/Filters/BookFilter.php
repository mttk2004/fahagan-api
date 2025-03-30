<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BookFilter
{
    protected Request $request;

    protected array $filters;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->filters = $request->get('filter', []);
    }

    public function apply(Builder $query): Builder
    {
        if (empty($this->filters)) {
            return $query;
        }

        $this->filterByPrice($query);
        $this->filterByAuthors($query);
        $this->filterByEdition($query);
        $this->filterByPublisher($query);
        $this->filterByGenres($query);
        $this->filterByPublicationDate($query);

        return $query;
    }

    protected function filterByPrice(Builder $query): void
    {
        if (isset($this->filters['price_from'])) {
            $query->where('price', '>=', $this->filters['price_from']);
        }

        if (isset($this->filters['price_to'])) {
            $query->where('price', '<=', $this->filters['price_to']);
        }
    }

    protected function filterByAuthors(Builder $query): void
    {
        if (isset($this->filters['authors'])) {
            $authorIds = explode(',', $this->filters['authors']);
            $query->whereHas('authors', function ($q) use ($authorIds) {
                $q->whereIn('authors.id', $authorIds);
            });
        }
    }

    protected function filterByEdition(Builder $query): void
    {
        if (isset($this->filters['edition'])) {
            $editions = explode(',', $this->filters['edition']);
            $query->whereIn('edition', $editions);
        }
    }

    protected function filterByPublisher(Builder $query): void
    {
        if (isset($this->filters['publisher'])) {
            $publisherIds = explode(',', $this->filters['publisher']);
            $query->whereIn('publisher_id', $publisherIds);
        }
    }

    protected function filterByGenres(Builder $query): void
    {
        if (isset($this->filters['genres'])) {
            $genreIds = explode(',', $this->filters['genres']);
            $query->whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('genres.id', $genreIds);
            });
        }
    }

    protected function filterByPublicationDate(Builder $query): void
    {
        if (isset($this->filters['pub_date_from'])) {
            $query->where('publication_date', '>=', $this->filters['pub_date_from']);
        }

        if (isset($this->filters['pub_date_to'])) {
            $query->where('publication_date', '<=', $this->filters['pub_date_to']);
        }
    }
}

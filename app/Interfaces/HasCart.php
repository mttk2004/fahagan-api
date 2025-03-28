<?php

namespace App\Interfaces;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasCart
{
    public function cartItems(): HasMany;

    public function isBookInCart($bookId): bool;

    public function booksInCart(): BelongsToMany;

    public function getCartItemByBook($bookId): ?CartItem;
}

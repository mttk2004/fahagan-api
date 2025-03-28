<?php

namespace App\Interfaces;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface HasCart
{
    public function cartItems(): HasMany;
    public function isBookInCart($bookId): bool;
    public function booksInCart(): BelongsToMany;
    public function getCartItemByBook($bookId): ?CartItem;
}

<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollection extends ResourceCollection
{
    protected bool $isDirectResponse = true;

    /**
     * Đánh dấu collection có phải là response trực tiếp hay không
     *
     * @return $this
     */
    public function isDirectResponse(bool $isDirectResponse = true): self
    {
        $this->isDirectResponse = $isDirectResponse;

        return $this;
    }

    /**
     * Kiểm tra collection có phải là response trực tiếp hay không
     */
    public function getIsDirectResponse(): bool
    {
        return $this->isDirectResponse;
    }
}

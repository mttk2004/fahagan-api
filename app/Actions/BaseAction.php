<?php

namespace App\Actions;

/**
 * Base class cho tất cả các Action classes
 */
abstract class BaseAction
{
    /**
     * Thực thi action
     *
     * @param mixed ...$args Các tham số cần thiết cho action
     * @return mixed
     */
    abstract public function execute(...$args);
}

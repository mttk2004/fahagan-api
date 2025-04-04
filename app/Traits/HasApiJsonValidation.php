<?php

namespace App\Traits;

trait HasApiJsonValidation
{
    /**
     * Biến đổi các rules validation cho API JSON:API data.attributes.*
     */
    protected function mapAttributesRules(array $rules): array
    {
        $mapped = [];
        foreach ($rules as $field => $rule) {
            $mapped["data.attributes.$field"] = $rule;
        }

        return $mapped;
    }

    /**
     * Biến đổi các thông báo lỗi validation cho API JSON:API data.attributes.*
     */
    protected function mapAttributesMessages(array $messages): array
    {
        $mapped = [];
        foreach ($messages as $key => $message) {
            $fieldParts = explode('.', $key);
            if (count($fieldParts) < 2) {
                continue;
            }

            $field = $fieldParts[0];
            $rule = $fieldParts[1];

            $mapped["data.attributes.$field.$rule"] = $message;
        }

        return $mapped;
    }

    /**
     * Biến đổi các rules validation cho API JSON:API data.relationships.*
     */
    protected function mapRelationshipsRules(array $rules): array
    {
        $mapped = [];
        foreach ($rules as $relation => $relationRules) {
            if (is_array($relationRules)) {
                foreach ($relationRules as $field => $rule) {
                    $mapped["data.relationships.$relation.$field"] = $rule;
                }
            } else {
                $mapped["data.relationships.$relation"] = $relationRules;
            }
        }

        return $mapped;
    }
}

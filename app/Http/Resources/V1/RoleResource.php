<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

/** @mixin Role
 */
class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'role',
          'id' => $this->id,
          'attributes' => [
            'name' => $this->name,
            'permissions' => $this->permissions->pluck('name'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          ],
        ];
    }
}

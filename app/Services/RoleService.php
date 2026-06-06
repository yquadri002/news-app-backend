<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Str;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->roleRepository->paginate($perPage, $filters);
    }

    public function create(array $data): Role
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->roleRepository->create($data);
    }

    public function update(int $id, array $data): Role
    {
        $role = $this->roleRepository->findOrFail($id);

        if ($role->is_system && isset($data['permissions'])) {
            abort(403, 'System roles cannot have permissions modified.');
        }

        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->roleRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $role = $this->roleRepository->findOrFail($id);

        if ($role->is_system) {
            abort(403, 'System roles cannot be deleted.');
        }

        if ($role->admins()->exists()) {
            abort(422, 'Cannot delete role with assigned admins.');
        }

        return $this->roleRepository->delete($id);
    }
}

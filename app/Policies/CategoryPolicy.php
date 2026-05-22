<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Employee;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Employee $employee): bool
    {
        return $employee->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */

    /**
     * Determine whether the user can create models.
     */
    public function create(Employee $employee): bool
    {
        return $employee->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Employee $employee, Category $category): bool
    {
        return $employee->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Employee $employee, Category $category): bool
    {
        return $employee->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Employee $employee, Category $category): bool
    {
        return $employee->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Employee $employee, Category $category): bool
    {
        return $employee->role === 'admin';
    }
}

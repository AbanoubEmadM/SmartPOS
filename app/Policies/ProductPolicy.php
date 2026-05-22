<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Product;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Employee $employee): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Employee $employee, Product $product): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Employee $employee): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Employee $employee, Product $product): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Employee $employee, Product $product): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Employee $employee, Product $product): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Employee $employee, Product $product): bool
    {
        return isset($employee->role) && $employee->role === 'admin';
    }
}

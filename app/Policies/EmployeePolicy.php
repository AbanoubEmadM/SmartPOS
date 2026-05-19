<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
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
    public function view(Employee $loggedInEmployee, Employee $employee): bool
    {
        return $loggedInEmployee->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Employee $loggedInEmployee): bool
    {
        return $loggedInEmployee->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Employee $loggedInEmployee, Employee $employee): bool
    {
        return $loggedInEmployee->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Employee $loggedInEmployee, Employee $employee): bool
    {
        return $loggedInEmployee->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Employee $loggedInEmployee, Employee $employee): bool
    {
        return $loggedInEmployee->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Employee $loggedInEmployee, Employee $employee): bool
    {
        return $loggedInEmployee->role === 'admin';
    }
}

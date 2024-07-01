<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Enums\Role;
use Illuminate\Http\Request;

class CompanyUserController extends Controller
{
    public function index(Company $company)
    {
        $users = $users = $company->users()->where('role_id', '=', Role::CUSTOMER->value)->get();

        return view('companies.users.index', compact('company', 'users'));
    }
}

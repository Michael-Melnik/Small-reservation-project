<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\RegistrationInvite;
use App\Models\Company;
use App\Enums\Role;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyUserController extends Controller
{
    public function index(Company $company)
    {
        $this->authorize('viewAny', $company);
        $users = $users = $company->users()->where('role_id', '=', Role::COMPANY_OWNER->value)->get();

        return view('companies.users.index', compact('company', 'users'));
    }

    public function create(Company $company)
    {
        $this->authorize('create', $company);
        return view('companies.users.create', compact('company'));
    }

    public function store(StoreUserRequest $request, Company $company)
    {
        $this->authorize('create', $company);

        $invitation = UserInvitation::create([
            'email' => $request->input('email'),
            'token' => Str::uuid(),
            'company_id' => $company->id,
            'role_id' => Role::COMPANY_OWNER->value,
        ]);
//        $company->users()->create([
//            'name' => $request->input('name'),
//            'email' => $request->input('email'),
//            'password' => bcrypt($request->input('password')),
//            'role_id' => Role::COMPANY_OWNER->value,
//        ]);

        Mail::to($request->input('email'))->send(new RegistrationInvite($invitation));

        return to_route('companies.users.index', $company);
    }

    public function edit(Company $company, User $user)
    {
        $this->authorize('update', $company);
        return view('companies.users.edit', compact('company', 'user'));
    }

    public function update(UpdateUserRequest $request ,Company $company, User $user)
    {
        $this->authorize('update', $company);
        $user->update($request->validated());

        return to_route('companies.users.index', $company);
    }

    public function destroy(Company $company, User $user)
    {
        $this->authorize('delete', $company);
        $user->delete();
        return to_route('companies.users.index', $company);
    }
}

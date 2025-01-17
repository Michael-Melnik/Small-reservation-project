<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuideRequest;
use App\Http\Requests\UpdateGuideRequest;
use App\Mail\RegistrationInvite;
use App\Models\Company;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use App\Enums\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CompanyGuideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Company $company)
    {
        $this->authorize('viewAny', $company);

        $guides = $company->users()->where('role_id', '=', Role::GUIDE->value)->get();

        return view('companies.guides.index', compact('company', 'guides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Company $company)
    {
        $this->authorize('create', $company);

        return view('companies.guides.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuideRequest $request, Company $company)
    {
        $this->authorize('create', $company);

        $invitation = UserInvitation::create([
            'email' => $request->input('email'),
            'token' => Str::uuid(),
            'company_id' => $company->id,
            'role_id' => Role::GUIDE->value,
        ]);
//        $company->users()->create([
//            'name' => $request->input('name'),
//            'email' => $request->input('email'),
//            'password' => bcrypt($request->input('password')),
//            'role_id' => Role::COMPANY_OWNER->value,
//        ]);

        Mail::to($request->input('email'))->send(new RegistrationInvite($invitation));
//        $company->users()->create([
//            'name' => $request->input('name'),
//            'email' => $request->input('email'),
//            'password' => bcrypt($request->input('password')),
//            'role_id' => Role::GUIDE->value,
//        ]);
        return to_route('companies.guides.index', $company);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company, User $guide)
    {
        $this->authorize('update', $company);
        return view('companies.guides.edit', compact('company', 'guide'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGuideRequest $request, Company $company, User $guide)
    {
        $this->authorize('update', $company);
        $guide->update($request->validated());

        return to_route('companies.guides.index', $company);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company, User $guide)
    {
        $this->authorize('delete', $company);
        $guide->delete();
        return to_route('companies.guides.index', $company);
    }
}

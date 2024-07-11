<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Company;
use App\Models\User;
//use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CompanyActivityController extends Controller
{
    public function index(Company $company)
    {
        $this->authorize('viewAny', $company);

        $company->load('activities');

        return view('companies.activities.index', compact('company'));
    }

    public function create(Company $company)
    {
        $this->authorize('create', $company);

        $guides = User::where('company_id', '=', $company->id)
            ->where('role_id', '=', Role::GUIDE->value)->pluck('name', 'id');

        return view('companies.activities.create', compact('company', 'guides'));
    }

    public function store(StoreActivityRequest $request, Company $company)
    {
        $this->authorize('create', $company);

        $filename = $this->uploadImage($request);
//        if ($request->hasFile('image')) {
//            $path = $request->file('image')->store('activities', 'public');
//        }

        Activity::create($request->validated() + [
                'company_id' => $company->id,
                'photo' => $filename,
            ]);

        return to_route('companies.activities.index', compact('company'));
    }

    public function edit(Company $company, Activity $activity)
    {
        $this->authorize('update', $company);

        $guides = User::where('company_id', '=', $company->id)
            ->where('role_id', '=', Role::GUIDE->value)->pluck('name', 'id');

        return view('companies.activities.edit', compact('company', 'guides', 'activity'));
    }

    public function update(UpdateActivityRequest $request, Company $company, Activity $activity)
    {
        $this->authorize('update', $company);

//        if ($request->hasFile('image')) {
//            $path = $request->file('image')->store('activities', 'public');
//            if($activity->photo) {
//                Storage::disk('public')->delete($activity->photo);
//            }
//        }
        $filename = $this->uploadImage($request);

        $activity->update($request->validated() + [
            'photo' => $filename ?? $activity->photo
            ]);

        return to_route('companies.activities.index', $company);
    }

    public function destroy(Company $company, Activity $activity)
    {
        $this->authorize('delete', $company);

        if($activity->photo) {
            Storage::disk('public')->delete($activity->photo);
        }
        $activity->delete();

        return to_route('companies.activities.index', $company);
    }

    private function uploadImage(StoreActivityRequest|UpdateActivityRequest $request)
    {
        if(! $request->hasFile('image')) {
            return null;
        }

        $filename = $request->file('image')->store(options: 'activities');

        $img = Image::read(Storage::disk('activities')->get($filename))
            ->resize(274, 274, function ($constraint) {
                $constraint->aspectRatio();
            });

        Storage::disk('activities')->put('thumbs/' . $request->file('image')->hashName(), $img->toJpeg()->toFilePointer());

        return $filename;
    }

}

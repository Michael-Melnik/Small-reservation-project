<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MyActivityController extends Controller
{
    public function show()
    {
        $activities = auth()->user()->activities()->orderBy('start_time')->get();
//        dd($activities);
        return view('activities.my-activities', compact('activities'));
    }

    public function destroy(Activity $activity)
    {
        abort_if(! auth()->user()->activities->contains($activity), Response::HTTP_FORBIDDEN);

        auth()->user()->activities()->detach($activity);
        return to_route('my-activity.show')->with('success', 'Activity removed.');
    }
}

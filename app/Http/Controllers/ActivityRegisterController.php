<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Notifications\RegisteredToActivityNotification;
use Illuminate\Http\Request;

class ActivityRegisterController extends Controller
{
    public function store(Activity $activity)
    {
        if (! auth()->check()) {
            return to_route('register', ['activity' => $activity->id]);
        }

        auth()->user()->activities()->attach($activity->id);

        auth()->user()->notify(new RegisteredToActivityNotification($activity));

        return to_route('my-activity.show')->with('success', 'You have successfully registered.');
    }
}

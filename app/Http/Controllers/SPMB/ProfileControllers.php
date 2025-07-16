<?php

namespace App\Http\Controllers\SPMB;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function create()
    {
        return view('pages.profile');
    }

    public function update()
    {

        $user = request()->user();
        $attributes = request()->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'name' => 'required',
            'phone' => 'required|max:10',
            'about' => 'required:max:150',
            'location' => 'required'
        ]);

        $activeID = Auth::id();
        $editUser = User::find($activeID);

        // auth()->user()->update($attributes);

        $editUser->name = $attributes['name'];
        $editUser->email = $attributes['email'];
        $editUser->phone = $attributes['phone'];
        $editUser->about = $attributes['about'];
        $editUser->location = $attributes['location'];

        $editUser->save();

        return back()->withStatus('Profile successfully updated.');
    }
}

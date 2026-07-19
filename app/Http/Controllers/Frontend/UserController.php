<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index($username) {
        $user = User::where("status", true)->where("username", $username)->first();
        if ($user) {
            $posts = $user->posts()->with("category")->where("status", true)->orderBy("id", "DESC")->paginate(10);
            return view("frontend.user.index", compact("user", "posts"));
        }
        return abort(404);
    }

    public function members() {
        $members = User::where("status", true)
            ->whereIn("role", [User::IS_AUTHOR, User::IS_ADMIN])
            ->withCount(['posts' => function($query) {
                $query->where('status', true);
            }])
            ->orderBy("id", "ASC")
            ->paginate(12);

        return view("frontend.user.list", compact("members"));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        if ($request->only) {
            return Account::distinct($request->only)->get($request->only);
        }

        return Account::with("client")->get();
    }

    public function search(string $searchAccount) {
        return Account::where('account_number', 'like', "___%$searchAccount%")->with("client")->get();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->only(['name', 'industry_category']));

        return redirect()
            ->route($request->redirectRouteName())
            ->with('status', __(':name was added.', ['name' => $request->string('name')]));
    }
}

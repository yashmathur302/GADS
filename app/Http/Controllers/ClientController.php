<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteClientRequest;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
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

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->only(['name', 'industry_category']));

        return redirect()
            ->route($request->redirectRouteName())
            ->with('status', __(':name was updated.', ['name' => $request->string('name')]));
    }

    public function destroy(DeleteClientRequest $request, Client $client): RedirectResponse
    {
        $name = $client->name;
        $client->delete();

        return redirect()
            ->route($request->redirectRouteName())
            ->with('status', __(':name and its keyword lists were deleted.', ['name' => $name]));
    }
}

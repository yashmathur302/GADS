<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->only(['name', 'industry_category'])
            + ['type' => $request->input('context')]);

        return redirect()
            ->route($request->redirectRouteName())
            ->with('status', __(':name was added.', ['name' => $request->string('name')]));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->only(['name', 'industry_category']));

        return redirect()
            ->route($client->sectionIndexRouteName())
            ->with('status', __(':name was updated.', ['name' => $request->string('name')]));
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $name = $client->name;
        $sectionLabel = $client->sectionLabel();
        $redirectRoute = $client->sectionIndexRouteName();

        $client->delete();

        return redirect()
            ->route($redirectRoute)
            ->with('status', __(':name and its :section were deleted.', ['name' => $name, 'section' => $sectionLabel]));
    }
}

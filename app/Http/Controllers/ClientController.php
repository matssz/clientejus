<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $clients = $request->user()
            ->clients()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = $request->user()
            ->clients()
            ->create($request->validated());

        return redirect()
            ->route('clientes.show', $client)
            ->with('status', 'Cliente cadastrado com sucesso.');
    }

    public function show(Request $request, int $cliente): View
    {
        return view('clients.show', [
            'client' => $this->clientForUser($request, $cliente),
        ]);
    }

    public function edit(Request $request, int $cliente): View
    {
        return view('clients.edit', [
            'client' => $this->clientForUser($request, $cliente),
        ]);
    }

    public function update(ClientRequest $request, int $cliente): RedirectResponse
    {
        $client = $this->clientForUser($request, $cliente);
        $client->update($request->validated());

        return redirect()
            ->route('clientes.show', $client)
            ->with('status', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Request $request, int $cliente): RedirectResponse
    {
        $client = $this->clientForUser($request, $cliente);

        if ($client->legalCases()->exists()) {
            return back()->withErrors([
                'delete' => 'Este cliente possui casos vinculados e não pode ser excluído.',
            ]);
        }

        $client->delete();

        return redirect()
            ->route('clientes.index')
            ->with('status', 'Cliente excluído com sucesso.');
    }

    private function clientForUser(Request $request, int $clientId): Client
    {
        return $request->user()
            ->clients()
            ->findOrFail($clientId);
    }
}

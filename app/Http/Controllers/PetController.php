<?php

namespace App\Http\Controllers;

use App\Helpers\QRCodeHelper;
use App\Models\Pet;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PetController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pet::with(['owner', 'unit', 'condominium'])
            ->byCondominium(Auth::user()->condominium_id)
            ->active();

        // Filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        $pets = $query->latest()->get();

        return view('pets.index', compact('pets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Pet::class);

        $units = Unit::byCondominium(Auth::user()->condominium_id)
            ->active()
            ->orderBy('number')
            ->get();

        return view('pets.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Pet::class);

        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:dog,cat,bird,other',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'size' => 'required|in:small,medium,large',
            'birth_date' => 'nullable|date',
            'observations' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Verificar se o owner é da unidade selecionada
        $owner = User::findOrFail($validated['owner_id']);
        if ($owner->unit_id != $validated['unit_id']) {
            return back()->withErrors(['owner_id' => 'O morador selecionado não pertence à unidade escolhida.']);
        }

        // Verificar se o owner não é agregado
        if ($owner->isAgregado()) {
            return back()->withErrors(['owner_id' => 'Agregados não podem ser donos de pets.']);
        }

        $validated['condominium_id'] = Auth::user()->condominium_id;

        // Upload da foto
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('pets', 'public');
        }

        $pet = Pet::create($validated);

        return redirect()->route('pets.index')
            ->with('success', 'Pet cadastrado com sucesso! QR Code gerado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pet $pet)
    {
        $this->authorize('view', $pet);

        $pet->load(['owner', 'unit', 'condominium']);

        return view('pets.show', compact('pet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pet $pet)
    {
        $this->authorize('update', $pet);

        $units = Unit::byCondominium(Auth::user()->condominium_id)
            ->active()
            ->orderBy('number')
            ->get();

        return view('pets.edit', compact('pet', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:dog,cat,bird,other',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'size' => 'required|in:small,medium,large',
            'birth_date' => 'nullable|date',
            'observations' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Verificar se o owner é da unidade selecionada
        $owner = User::findOrFail($validated['owner_id']);
        if ($owner->unit_id != $validated['unit_id']) {
            return back()->withErrors(['owner_id' => 'O morador selecionado não pertence à unidade escolhida.']);
        }

        // Verificar se o owner não é agregado
        if ($owner->isAgregado()) {
            return back()->withErrors(['owner_id' => 'Agregados não podem ser donos de pets.']);
        }

        // Upload da nova foto
        if ($request->hasFile('photo')) {
            // Deletar foto antiga
            if ($pet->photo) {
                Storage::disk('public')->delete($pet->photo);
            }
            $validated['photo'] = $request->file('photo')->store('pets', 'public');
        }

        $pet->update($validated);

        return redirect()->route('pets.index')
            ->with('success', 'Pet atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pet $pet)
    {
        $this->authorize('delete', $pet);

        // Deletar foto
        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return redirect()->route('pets.index')
            ->with('success', 'Pet removido com sucesso!');
    }

    /**
     * Buscar moradores de uma unidade (AJAX)
     */
    public function getOwnersByUnit($unitId)
    {
        $owners = User::where('unit_id', $unitId)
            ->whereHas('roles', function($query) {
                $query->where('name', 'Morador');
            })
            ->where('is_active', true)
            ->select('id', 'name', 'phone')
            ->get();

        return response()->json($owners);
    }

    /**
     * Exibir QR Code do pet
     */
    public function showQrCode($qrCode)
    {
        $pet = Pet::where('qr_code', $qrCode)
            ->with(['owner', 'unit', 'condominium'])
            ->firstOrFail();

        return view('pets.qr-show', compact('pet'));
    }

    /**
     * Download QR Code do pet
     */
    public function downloadQrCode(Pet $pet)
    {
        $this->authorize('view', $pet);

        $qrCode = QrCode::format('svg')
            ->size(400)
            ->errorCorrection('H')
            ->generate(route('pets.show-qr', $pet->qr_code));

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="pet-' . $pet->id . '-qrcode.svg"');
    }

    /**
     * Exibir página de verificação de QR Code
     */
    public function verify()
    {
        return view('pets.verify');
    }

    /**
     * Verificar pet pelo QR Code (para leitor)
     */
    public function verifyQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $pet = Pet::where('qr_code', $request->qr_code)
            ->with(['owner', 'unit', 'condominium'])
            ->first();

        if (!$pet) {
            return response()->json([
                'success' => false,
                'message' => 'Pet não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'type' => $pet->type_label,
                'breed' => $pet->breed,
                'color' => $pet->color,
                'size' => $pet->size_label,
                'photo' => $pet->photo_url,
                'description' => $pet->description,
                'owner' => [
                    'name' => $pet->owner->name,
                    'phone' => $pet->owner->phone,
                    'whatsapp_link' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $pet->owner->phone),
                ],
                'unit' => [
                    'identifier' => $pet->unit->full_identifier,
                ],
                'condominium' => [
                    'name' => $pet->condominium->name ?? 'Condomínio',
                ],
            ],
        ]);
    }
}

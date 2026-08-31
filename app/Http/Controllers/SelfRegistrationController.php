<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelfRegisterRequest;
use App\Models\Condominium;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SelfRegistrationController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function create()
    {
        return view('auth.register');
    }

    public function store(SelfRegisterRequest $request)
    {
        $condominium = $request->condominium();
        $role = $request->input('registration_type') === 'compossuidor' ? 'Morador' : 'Agregado';

        $user = User::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $request->input('registration_type') === 'compossuidor' ? $request->input('unit_id') : null,
            'morador_vinculado_id' => $request->input('registration_type') === 'dependente'
                ? $request->input('morador_vinculado_id')
                : null,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'cpf' => $request->input('cpf'),
            'telefone_celular' => $request->input('telefone_celular'),
            'phone' => $request->input('telefone_celular'),
            'data_nascimento' => $request->input('data_nascimento'),
            'data_entrada' => now()->toDateString(),
            'is_active' => false,
            'registration_status' => 'pending',
            'senha_temporaria' => false,
            'possui_dividas' => false,
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $this->fileUploadService->uploadUserPhoto($request->file('photo'), $user->id);
            $user->update(['photo' => $photoPath]);
        }

        $user->syncRoles([$role]);
        $user->generateQRCode();

        $this->notifyManagers($user, $condominium, $role);

        return redirect()
            ->route('register.success')
            ->with('registered_name', $user->name);
    }

    public function success()
    {
        if (!session('registered_name')) {
            return redirect()->route('register');
        }

        return view('auth.register-success');
    }

    public function lookupCode(Request $request)
    {
        $request->validate([
            'registration_code' => ['required', 'string', 'max:20'],
        ]);

        $condominium = Condominium::query()
            ->active()
            ->where('registration_code', strtoupper(trim((string) $request->input('registration_code'))))
            ->first(['id', 'name', 'city', 'state']);

        if (!$condominium) {
            return response()->json([
                'valid' => false,
                'message' => 'Código não encontrado. Verifique com a administração do condomínio.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'condominium' => [
                'id' => $condominium->id,
                'name' => $condominium->name,
                'location' => trim(($condominium->city ?? '') . ($condominium->state ? ' - ' . $condominium->state : '')),
            ],
        ]);
    }

    public function units(Request $request)
    {
        $condominium = $this->resolveCondominium($request);
        if (!$condominium) {
            return response()->json(['message' => 'Código inválido.'], 422);
        }

        $units = Unit::query()
            ->active()
            ->byCondominium($condominium->id)
            ->orderBy('block')
            ->orderBy('number')
            ->get(['id', 'number', 'block', 'type'])
            ->map(fn (Unit $unit) => [
                'id' => $unit->id,
                'label' => $unit->full_identifier,
            ]);

        return response()->json(['units' => $units]);
    }

    public function moradores(Request $request)
    {
        $condominium = $this->resolveCondominium($request);
        if (!$condominium) {
            return response()->json(['message' => 'Código inválido.'], 422);
        }

        $term = trim((string) $request->get('term', ''));
        if (strlen($term) < 2) {
            return response()->json(['moradores' => []]);
        }

        $moradores = User::query()
            ->active()
            ->byCondominium($condominium->id)
            ->with('unit:id,number,block')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('cpf', 'like', "%{$term}%");
            })
            ->whereHas('roles', fn ($q) => $q->where('name', 'Morador'))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'cpf', 'unit_id'])
            ->map(fn (User $morador) => [
                'id' => $morador->id,
                'name' => $morador->name,
                'cpf' => $morador->cpf,
                'unit' => $morador->unit?->full_identifier,
            ]);

        return response()->json(['moradores' => $moradores]);
    }

    private function resolveCondominium(Request $request): ?Condominium
    {
        $code = strtoupper(trim((string) $request->get('registration_code', '')));
        if ($code === '') {
            return null;
        }

        return Condominium::query()
            ->active()
            ->where('registration_code', $code)
            ->first();
    }

    private function notifyManagers(User $newUser, Condominium $condominium, string $role): void
    {
        $managers = User::query()
            ->active()
            ->byCondominium($condominium->id)
            ->get()
            ->filter(fn (User $user) => $user->hasAssignedRole('Administrador') || $user->hasAssignedRole('Síndico'));

        foreach ($managers as $manager) {
            Notification::create([
                'condominium_id' => $condominium->id,
                'user_id' => $manager->id,
                'type' => 'registration_pending',
                'title' => 'Novo cadastro aguardando aprovação',
                'message' => "{$newUser->name} solicitou cadastro como {$role}.",
                'data' => [
                    'user_id' => $newUser->id,
                    'role' => $role,
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }
    }
}

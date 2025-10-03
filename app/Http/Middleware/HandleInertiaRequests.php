<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? $request->user()->only('id', 'name', 'email', 'Alias') : null,
            ],
            // --- ¡ASEGÚRATE DE QUE ESTA SECCIÓN ESTÉ PRESENTE Y CORRECTA! ---
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'newNodeId' => fn () => $request->session()->get('newNodeId'), // <-- Asegúrate de que esta línea esté aquí
                // Puedes agregar más mensajes flash si los usas
            ],
            // -----------------------------------------------------------------
            'permisos' => session('permisos', []), // Esto lo tienes
            'jetstream' => session('jetstream', []), // Y esto
            // ... (cualquier otra prop que estés compartiendo globalmente)
        ]);
    }
}

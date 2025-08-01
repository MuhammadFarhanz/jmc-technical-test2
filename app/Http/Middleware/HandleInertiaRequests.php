<?php

namespace App\Http\Middleware;

use App\Models\Kategori;
use App\Models\SubKategori;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    // public function share(Request $request): array
    // {
    //     return [
    //         ...parent::share($request),
    //         'auth' => [
    //             'user' => $request->user(),
    //         ],
    //     ];
    // }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn() => $request->user()
                    ? $request->user()->only(['id', 'name', 'email', 'role'])
                    : null,
            ],
            'operators' => fn() => User::where('role', 'Operator')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'kategori' => fn() => Kategori::all(),
            'subkategoris' => fn() => SubKategori::all(),
        ]);
    }
}

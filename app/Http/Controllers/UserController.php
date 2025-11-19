<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\Order;
use App\Models\Tarif;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return UserResource::collection(
            User::where('role_id', User::ROLE_CLIENT)->orderBy('created_at', 'desc')->get()
        );
    }

    public function allClients()
    {
        return Company::all(['id', 'user_id', 'title']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return UserResource
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users,phone',
            'password' => 'min:6|confirmed',

            'title' => 'required',
            'type' => 'required',
        ]);

        $user_data = [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ];

        if ($request->input('password')) {
            $user_data['password'] = Hash::make($request->input('password'));
        } else {
            $user_data['password'] = Hash::make(Str::random(8));
        }

        $user = User::create($user_data);

        $company = Company::create([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            "inn" => $request->input('inn'),
            "ogrn" => $request->input('ogrn'),
            "okpo" => $request->input('okpo'),
            "address" => $request->input('address'),
            "oktmo" => $request->input('oktmo'),
            "okved" => $request->input('okved'),
            "pfr" => $request->input('pfr'),
            "rs" => $request->input('rs'),
            "bank" => $request->input('bank'),
            "bank_address" => $request->input('bank_address'),
            "bik" => $request->input('bik'),
            "ks" => $request->input('ks'),
            "phone" => $request->input('company_phone'),
            "mail" => $request->input('mail'),
            "kpp" => $request->input('kpp'),
            'user_id' => $user->id
        ]);

        $tarif = Tarif::create([
            'foot_today' => $request->input('foot_today') ?? 0,
            'foot' => $request->input('foot') ?? 0,
            'car_today' => $request->input('car_today') ?? 0,
            'car' => $request->input('car') ?? 0,
            'company_id' => $company->id
        ]);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return UserResource
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return UserResource
     */
    public function update(Request $request, User $user): UserResource
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'password' => 'min:6|confirmed',

            'title' => 'required',
            'type' => 'required',
        ]);
        $user_data = [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ];

        if ($request->input('password')) {
            $user_data['password'] = Hash::make($request->input('password'));
        }

        $user->update($user_data);

        $company_data = [
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            "inn" => $request->input('inn'),
            "ogrn" => $request->input('ogrn'),
            "okpo" => $request->input('okpo'),
            "address" => $request->input('address'),
            "oktmo" => $request->input('oktmo'),
            "okved" => $request->input('okved'),
            "pfr" => $request->input('pfr'),
            "rs" => $request->input('rs'),
            "bank" => $request->input('bank'),
            "bank_address" => $request->input('bank_address'),
            "bik" => $request->input('bik'),
            "ks" => $request->input('ks'),
            "phone" => $request->input('company_phone'),
            "mail" => $request->input('mail'),
            "kpp" => $request->input('kpp'),
            'user_id' => $user->id
        ];

        if ($company = $user->company) {
            $user->company->update($company_data);
        } else {
            $company = Company::create($company_data);
        }

        $tarif_data = [
            'foot_today' => $request->input('foot_today') ?? 0,
            'foot' => $request->input('foot') ?? 0,
            'car_today' => $request->input('car_today') ?? 0,
            'car' => $request->input('car') ?? 0,
            'company_id' => $company->id
        ];
        if ($user->company && $user->company->tarif) {
            $user->company->tarif->update($tarif_data);
        } else {
            Tarif::create($tarif_data);
        }

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return bool
     */
    public function destroy(User $user)
    {
        return $user->delete();
    }

    public function exportOrders(Request $request)
    {
        $user = User::query()->find($request->input('client_id'));
        $start = Carbon::parse($request->input('start'));
        $finish = Carbon::parse($request->input('finish'));

        return Excel::download(
            new OrdersExport($user, $start, $finish),
            sprintf("export_user_%s_%s_to_%s.xlsx", $user->id, $start->format('d_m_Y'), $finish->format('d_m_Y'))
        );
    }
}

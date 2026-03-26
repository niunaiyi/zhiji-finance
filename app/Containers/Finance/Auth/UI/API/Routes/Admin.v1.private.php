<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
 * SuperAdmin API — cross-tenant, no 'tenant' middleware
 * All endpoints require auth:api only.
 */

Route::middleware(['auth:api'])->prefix('admin')->group(function () {

    // ── System Stats ─────────────────────────────────────────────────────────
    Route::get('stats', function () {
        $companies = DB::table('companies')->count();
        $users     = DB::table('users')->count();
        $vouchers  = DB::table('vouchers')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $roles     = DB::table('user_company_roles')->count();

        return response()->json([
            'data' => [
                'total_companies'        => $companies,
                'total_users'            => $users,
                'monthly_vouchers'       => $vouchers,
                'total_role_assignments' => $roles,
            ],
        ]);
    });

    // ── Users ─────────────────────────────────────────────────────────────────
    Route::get('users', function (Request $request) {
        $users = DB::table('users')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderByDesc('users.id')
            ->paginate($request->input('per_page', 20));

        // Attach company/role info for each user
        $userIds = collect($users->items())->pluck('id');
        $roleMap = DB::table('user_company_roles')
            ->join('companies', 'companies.id', '=', 'user_company_roles.company_id')
            ->whereIn('user_company_roles.user_id', $userIds)
            ->select(
                'user_company_roles.user_id',
                'user_company_roles.role',
                'user_company_roles.is_active',
                'companies.name as company_name',
                'companies.id as company_id'
            )
            ->get()
            ->groupBy('user_id');

        $items = collect($users->items())->map(function ($user) use ($roleMap) {
            $user->companies = $roleMap->get($user->id, collect())->values();
            return $user;
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'per_page'     => $users->perPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    });

    Route::post('users', function (Request $request) {
        $data = $request->validate([
            'name'     => 'required|string|max:50',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $id = DB::table('users')->insertGetId([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data'    => DB::table('users')->find($id),
            'message' => '用户创建成功',
        ], 201);
    });

    // ── 1:1 Enforcement Helper ───────────────────────────────────────────────
    $checkUserAssignment = function ($userId) {
        $existing = DB::table('user_company_roles')
            ->where('user_id', $userId)
            ->first();
        if ($existing) {
            $companyId = data_get($existing, 'company_id');
            $company = DB::table('companies')->where('id', $companyId)->value('name');
            throw new \Exception("该用户已关联账套：{$company}，请先移除原关联。");
        }
    };

    // ── Companies ─────────────────────────────────────────────────────────────
    Route::get('companies', function () {
        $companies = DB::table('companies')
            ->select('id', 'code', 'name', 'fiscal_year_start', 'status', 'created_at')
            ->orderByDesc('id')
            ->get();

        // Attach user count per company
        $counts = DB::table('user_company_roles')
            ->selectRaw('company_id, count(*) as user_count')
            ->groupBy('company_id')
            ->pluck('user_count', 'company_id');

        $companies = $companies->map(function ($c) use ($counts) {
            $c->user_count = $counts->get($c->id, 0);
            return $c;
        });

        return response()->json(['data' => $companies]);
    });

    Route::patch('companies/{id}/status', function (Request $request, $id) {
        $data = $request->validate([
            'status' => 'required|in:active,suspended',
        ]);

        $updated = DB::table('companies')->where('id', $id)->update([
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => '账套不存在'], 404);
        }

        return response()->json(['message' => '状态已更新', 'data' => DB::table('companies')->find($id)]);
    });

    Route::get('companies/{id}/users', function ($id) {
        $members = DB::table('user_company_roles')
            ->join('users', 'users.id', '=', 'user_company_roles.user_id')
            ->where('user_company_roles.company_id', $id)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'user_company_roles.role',
                'user_company_roles.is_active'
            )
            ->get();

        return response()->json(['data' => $members]);
    });

    Route::post('companies/{id}/admins', function (Request $request, $id) use ($checkUserAssignment) {
        $data = $request->validate([
            'name'     => 'required|string|max:50',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        return DB::transaction(function () use ($data, $id) {
            $userId = DB::table('users')->insertGetId([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_company_roles')->insert([
                'user_id'    => $userId,
                'company_id' => $id,
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => '管理员创建并分配成功',
                'data'    => DB::table('users')->find($userId),
            ], 201);
        });
    });

    // ── Role Assignment ───────────────────────────────────────────────────────
    Route::post('roles', function (Request $request) use ($checkUserAssignment) {
        $data = $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'company_id' => 'required|integer|exists:companies,id',
            'role'       => 'required|in:admin,accountant,auditor,viewer',
        ]);

        try {
            $checkUserAssignment($data['user_id']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        DB::table('user_company_roles')->updateOrInsert(
            ['user_id' => $data['user_id'], 'company_id' => $data['company_id']],
            ['role' => $data['role'], 'is_active' => true, 'updated_at' => now()]
        );

        return response()->json(['message' => '角色分配成功']);
    });

    Route::delete('roles', function (Request $request) {
        $data = $request->validate([
            'user_id'    => 'required|integer',
            'company_id' => 'required|integer',
        ]);

        DB::table('user_company_roles')
            ->where('user_id', $data['user_id'])
            ->where('company_id', $data['company_id'])
            ->delete();

        return response()->json(['message' => '已移除角色']);
    });
});

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /** Allowed columns for sorting */
    private const ALLOWED_SORTS = ['created_at', 'first_name', 'last_name', 'email', 'account_type'];

    /**
     * Get all users with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortBy    = in_array($request->sort_by, self::ALLOWED_SORTS) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        $perPage = min((int) $request->get('per_page', 20), 100);
        $users   = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($users)->response()->getData(true),
        ]);
    }

    /**
     * Update a user's details (Admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name'     => 'sometimes|required|string|max:255',
            'last_name'      => 'sometimes|required|string|max:255',
            'email'          => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'phone'          => 'sometimes|required|string|max:20|unique:users,phone,' . $id,
            'role'           => 'sometimes|required|in:admin,seller,buyer',
            'wallet_balance' => 'sometimes|required|numeric|min:0',
            'is_active'      => 'sometimes|required|boolean',
        ]);

        // Prevent admin from disabling or demoting themselves
        if ($user->id === auth()->id()) {
            if (isset($validated['is_active']) && !$validated['is_active']) {
                return response()->json(['success' => false, 'message' => 'لا يمكنك تعطيل حسابك الخاص.'], 422);
            }
            if (isset($validated['role']) && $validated['role'] !== 'admin') {
                return response()->json(['success' => false, 'message' => 'لا يمكنك تغيير دورك الخاص.'], 422);
            }
        }

        $user->fill($request->only(['first_name', 'last_name', 'email', 'phone', 'wallet_balance', 'is_active']));

        if ($request->filled('role')) {
            $roleName = $request->role;
            if ($roleName === 'admin') {
                $user->syncRoles(['admin']);
            } elseif ($roleName === 'seller') {
                $user->account_type = 'seller';
                $user->is_approved  = true;
                $user->syncRoles(['seller']);
            } else {
                $user->account_type = 'buyer';
                $user->syncRoles(['buyer']);
            }
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات المستخدم بنجاح!',
            'data'    => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Delete a user account (Admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك حذف حسابك الخاص.'], 422);
        }

        $hasActiveAuctions = \App\Models\Auction::where('seller_id', $id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveAuctions) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المستخدم لوجود مزادات نشطة مرتبطة بحسابه.',
            ], 422);
        }

        $user->syncRoles([]);
        $user->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف حساب المستخدم بنجاح!']);
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك تعطيل حسابك الخاص.'], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'تم تفعيل حساب المستخدم.' : 'تم تعطيل حساب المستخدم.',
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * Get sellers pending approval (paginated).
     */
    public function pendingSellers(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $sellers = User::where('account_type', 'seller')
            ->where('is_approved', false)
            ->with('roles')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($sellers)->response()->getData(true),
        ]);
    }

    /**
     * Approve a seller account.
     */
    public function approveSeller(int $id): JsonResponse
    {
        $seller = User::findOrFail($id);

        if ($seller->account_type !== 'seller') {
            return response()->json(['success' => false, 'message' => 'هذا المستخدم ليس بائعاً.'], 422);
        }

        if ($seller->is_approved) {
            return response()->json(['success' => false, 'message' => 'هذا البائع موافق عليه بالفعل.'], 422);
        }

        $seller->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على البائع بنجاح!',
            'data'    => new UserResource($seller),
        ]);
    }

    /**
     * Reject (disapprove) a seller account.
     */
    public function rejectSeller(int $id): JsonResponse
    {
        $seller = User::findOrFail($id);

        if ($seller->account_type !== 'seller') {
            return response()->json(['success' => false, 'message' => 'هذا المستخدم ليس بائعاً.'], 422);
        }

        $seller->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض البائع.',
            'data'    => new UserResource($seller),
        ]);
    }
}

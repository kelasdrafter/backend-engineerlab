<?php

namespace App\Http\Controllers;

use Socialite;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\File;
use App\Models\User;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\Filters\CallbackFilter;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $allowedColumns = (new User)->getFillable();

            $data = QueryBuilder::for(User::class)
                ->withCount(['transactions as total_transactions' => function ($query) {
                    $query->where('status', 'success');
                }])
                ->allowedFilters([
                    ...$allowedColumns,
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::callback('total_transactions', function ($query, $value) {
                        $query->having('total_transactions', '=', $value);
                    }),
                ])
                ->allowedSorts([...$allowedColumns, 'total_transactions', 'created_at', 'updated_at'])
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $this->generateData($request);
            $data = User::create($data);

            return $this->responseSuccess('Create Data Succcessfully', new UserResource($data), 200);
        } catch (Exception $exeception) {
            return $this->responseError($exeception, [], 500);
        }
    }

    public function update(User $user, UpdateUserRequest $request)
    {
        // Cek apakah photo_url baru dan tidak sama dengan yang lama
        if ($request->has('photo_url') && $request->photo_url != $user->photo_url) {
            // Hapus file jika photo_url di perbarui
            $url = $user->photo_url;

            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $url), '/');

                File::deleteByPath($key);
            } catch (Exception $exeception) {}
        }

        $user->fill($this->generateData($request));
        $user->save();

        return $this->responseSuccess('Update Data Succcessfully', new UserResource($user), 200);
    }

    public function show(User $user)
    {
        return $this->responseSuccess('Get Data Succcessfully', new UserResource($user), 200);
    }

    public function destroy(User $user)
    {
        try {
            $url = $user->photo_url;

            // Ekstrak key dari URL
            $baseUrl = rtrim(env('AWS_URL'), '/');
            $key = ltrim(str_replace($baseUrl, '', $url), '/');

            File::deleteByPath($key);
        } catch (Exception $exception) {}

        $user->delete();
        return $this->responseSuccess('Delete Data Succcessfully', new UserResource($user), 200);
    }

public function redirectToGoogle(Request $request)
{
    // ✅ TAMBAHAN: Simpan callbackUrl ke session sebelum redirect
    $callbackUrl = $request->get('callbackUrl', '/courses');
    $request->session()->put('google_oauth_callback', $callbackUrl);
    
    return Socialite::driver('google')->redirect();
}

public function handleGoogleCallback(Request $request)
{
    try {
        $user = Socialite::driver('google')->user();
        $finduser = User::where('email', $user->email)->first();

        // ✅ TAMBAHAN: Ambil callbackUrl dari session atau request
        $callbackUrl = $request->session()->get('google_oauth_callback', '/courses');

        if($finduser){
            // Cek apakah kolom 'email_verified_at' masih null
            if (is_null($finduser->email_verified_at)) {
                $finduser->google_id = $user->id;
                $finduser->email_verified_at = now();
                $finduser->save();
            }

            $token = JWTAuth::fromUser($finduser);
            // ✅ UBAH: Redirect ke callbackUrl, bukan hardcoded /login
            return redirect()->away(env('APP_FRONTEND_URL') . $callbackUrl . (strpos($callbackUrl, '?') !== false ? '&' : '?') . "token=" . $token);
        }else{
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'google_id'=> $user->id,
                'password' => Hash::make(Str::random(12)),
                'email_verified_at' => now(),
                'role' => 'user',
                'is_active' => true,
            ]);

            $token = JWTAuth::fromUser($newUser);
            // ✅ UBAH: Redirect ke callbackUrl, bukan hardcoded /login
            return redirect()->away(env('APP_FRONTEND_URL') . $callbackUrl . (strpos($callbackUrl, '?') !== false ? '&' : '?') . "token=" . $token);
        }
    } catch (Exception $e) {
        return redirect('login/google');
    }
}

    public function exportUsers()
    {
        try {
            return Excel::download(new UserExport, 'users.xlsx');
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function generateData($request)
    {
        return [
            'name' => $request->name,
            'email' => $request->email,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'city' => $request->city,
            'birthdate' => $request->birthdate,
            'photo_url' => $request->photo_url,
            'institution' => $request->institution,
            'occupation' => $request->occupation,
            'password' => $request->password ?? Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => $request->email_verified_at,
            'is_active' => $request->is_active,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    // Register Admin
    public function registerAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create user for admin
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'status' => 'active',
            ]);

            // Generate token for the new admin
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'code' => '000',
                'message' => 'Admin registered successfully!',
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'An error occurred while registering the admin',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Register Customer
    public function registerCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'username' => 'required|string|unique:users',
            'birth_date' => 'required|date',
            'phone_number' => 'required|string',
            'email' => 'required|string|email|unique:customers',
            'password' => 'required|string|min:6',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create user for customer
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'status' => 'active',
            ]);

            // Create customer profile
            $customer = Customer::create([
                'user_id' => $user->user_id,
                'full_name' => $request->full_name,
                'birth_date' => $request->birth_date,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
            ]);

            // Generate token for the new customer
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'code' => '000',
                'message' => 'Customer registered successfully!',
                'token' => $token,
                'user' => $user,
                'customer_profile' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'Failed to register customer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Register Seller
    public function registerSeller(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|unique:sellers',
            'email' => 'required|string|email|unique:sellers',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create user for seller
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'seller',
                'status' => 'active',
            ]);

            // Create seller profile
            $seller = Seller::create([
                'user_id' => $user->user_id,
                'phone_number' => $request->phone_number,
                'email' => $request->email,  // Add this line
                'store_name' => null,
                'store_address' => null,
                'store_logo' => null,
                'store_description' => null,
                'store_rating' => 0,
                'total_sales' => 0,
            ]);

            // Generate token for the new seller
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'code' => '000',
                'message' => 'Seller registered successfully!',
                'token' => $token,
                'user' => $user,
                'seller_profile' => $seller,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'Failed to register seller',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // Login

    public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // Ambil user berdasarkan username
    $user = User::where('username', $request->username)->first();
    
    if ($user) {
        // Cek apakah password yang di-input cocok dengan password yang di-hash di database
        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
        'code' => '000',
                'message' => 'Login successful!',
                'token' => $token,
                'user' => $user,
            ], 200);
        } else {
            return response()->json([
        'code' => '101',
                'message' => 'Invalid password!',
            ], 401);
        }
    }

    return response()->json([
        'code' => '102',
        'message' => 'User not found!',
    ], 401);
}
public function logout(Request $request)
{
// Menghapus token autentikasi yang sedang digunakan
$request->user()->currentAccessToken()->delete();

return response()->json([
'code' => '000',
    'message' => 'Logout successful!'
], 200);
}
}

//     public function login(Request $request)
// {
//     $credentials = $request->validate([
//         'username' => 'required|string',
//         'password' => 'required|string',
//     ]);


    
//     \Log::info('Attempting login with credentials:', $credentials);

    
    // if (Auth::attempt($credentials)) {
    //     $user = Auth::user();
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login successful!',
    //         'token' => $token,
    //         'user' => $user,
    //     ], 200);
    // }

    // return response()->json([
    //     'message' => 'Invalid login credentials!',
    // ], 401); }



<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    // Update Customer Biodata 
    public function updateCustomer(Request $request)
    {
        Log::info('Request method: ' . $request->method());
        Log::info('Request data: ' . json_encode($request->all()));

        if (!$request->isMethod('put')) {
            return response()->json([
                'code' => '103',
                'message' => 'Method not allowed. Use PUT method to update customer biodata.',
            ], 405);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'birth_date' => 'sometimes|required|date',
            'phone_number' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|string|email|max:255',
            'address' => 'sometimes|required|string|max:500',
            'profile_image' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|required|in:male,female,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::user()) {
            return response()->json([
                'code' => '102',
                'message' => 'Unauthorized access',
            ], 401);
        }

        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return response()->json([
                'code' => '102',
                'message' => 'Customer not found',
            ], 404);
        }

        $updatedFields = [];
        foreach ($request->all() as $key => $value) {
            if ($customer->isFillable($key)) {
                $customer->{$key} = $value;
                $updatedFields[] = $key;
            }
        }

        if (!empty($updatedFields)) {
            $customer->save();
            return response()->json([
                'code' => '000',
                'message' => 'Customer biodata updated successfully!',
                'updated_fields' => $updatedFields
            ]);
        } else {
            return response()->json([
                'code' => '000',
                'message' => 'No changes were made to the customer biodata.'
            ]);
        }
    }

    // Get All Customers
    public function indexCustomer()
    {
        $customers = Customer::all();

        return response()->json([
            'code' => '000',
            'customers' => $customers
        ], 200);
    }

    // Get Single Customer by ID
    public function showCustomer($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'code' => '102',
                'message' => 'Customer not found!'
            ], 404);
        }

        return response()->json([
            'code' => '000',
            'customer' => $customer
        ], 200);
    }

    // Update Seller Biodata
    public function updateSeller(Request $request)
    {
        if (!$request->isMethod('put')) {
            return response()->json([
                'code' => '103',
                'message' => 'Method not allowed. Use PUT method to update seller biodata.',
            ], 405);
        }

        $validator = Validator::make($request->all(), [
            'store_name' => 'sometimes|required|string|max:255',
            'store_address' => 'sometimes|required|string|max:500',
            'store_logo' => 'sometimes|required|string|max:255',
            'store_description' => 'sometimes|required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::user()) {
            return response()->json([
                'code' => '102',
                'message' => 'Unauthorized access',
            ], 401);
        }

        $seller = Seller::where('user_id', Auth::id())->first();

        if (!$seller) {
            return response()->json([
                'code' => '102',
                'message' => 'Seller not found',
            ], 404);
        }

        $updatedFields = [];
        foreach ($request->all() as $key => $value) {
            if ($seller->isFillable($key)) {
                $seller->{$key} = $value;
                $updatedFields[] = $key;
            }
        }

        if (!empty($updatedFields)) {
            $seller->save();
            return response()->json([
                'code' => '000',
                'message' => 'Seller biodata updated successfully!',
                'updated_fields' => $updatedFields
            ]);
        } else {
            return response()->json([
                'code' => '000',
                'message' => 'No changes were made to the seller biodata.'
            ]);
        }
    }

    // Get All Sellers
    public function indexSeller()
    {
        $sellers = Seller::all();

        return response()->json([
            'code' => '000',
            'sellers' => $sellers
        ], 200);
    }

    // Get Single Seller by ID
    public function showSeller($id)
    {
        $seller = Seller::find($id);

        if (!$seller) {
            return response()->json([
                'code' => '102',
                'message' => 'Seller not found!'
            ], 404);
        }

        return response()->json([
            'code' => '000',
            'seller' => $seller
        ], 200);
    }
}

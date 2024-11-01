<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['details.product'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Hitung total amount
            $totalAmount = 0;
            $orderDetails = [];

            foreach ($request->details as $detail) {
                $product = Product::findOrFail($detail['product_id']);
                
                if ($product->stock < $detail['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $subtotal = $product->price * $detail['quantity'];
                $totalAmount += $subtotal;

                $orderDetails[] = [
                    'product_id' => $product->id,
                    'quantity' => $detail['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ];

                $product->decrement('stock', $detail['quantity']);
            }

            // Buat order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . Str::random(10),
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'shipping_address' => $request->shipping_address,
                'shipping_method' => $request->shipping_method,
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'notes' => $request->notes
            ]);

            // Simpan order details
            foreach ($orderDetails as $detail) {
                $order->details()->create($detail);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order->load('details.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('details.product'));
    }

    public function updateStatus(Order $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled,refunded'
        ]);

        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'message' => 'Order cannot be cancelled'
            ], 400);
        }

        DB::transaction(function () use ($order) {
            // Kembalikan stok
            foreach ($order->details as $detail) {
                $detail->product->increment('stock', $detail->quantity);
            }

            $order->update([
                'status' => 'cancelled'
            ]);
        });

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => $order
        ]);
    }

    public function getOrderHistory()
    {
        $orders = Order::with(['details.product'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function getPendingOrders()
    {
        $orders = Order::with(['details.product'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }
}

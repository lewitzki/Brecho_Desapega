<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'required|integer',
            'shipping_address' => 'required|array',
            'shipping_address.fullName' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.zipCode' => 'required|string',
            'shipping_address.phone' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $total = 0;
            $orderItems = [];

            // Validar estoque e calcular total
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'error' => "Estoque insuficiente para {$product->name}"
                    ], 400);
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'qty' => $item['quantity'],
                    'price' => $product->price,
                    'size' => $item['size']
                ];

                // Atualizar estoque
                $product->decrement('stock', $item['quantity']);
            }

            // Adicionar frete
            $shippingFee = $total >= 200 ? 0 : 15;
            $total += $shippingFee;

            // Criar pedido
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => $total,
                'status' => 'processing',
                'shipping_address' => $request->shipping_address
            ]);

            // Criar itens do pedido
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            return response()->json(
                $order->load('items'), 
                201
            );
        });
    }

    public function show(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->findOrFail($id);

        return response()->json($order);
    }

    public function adminIndex()
    {
        $orders = Order::with(['items', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json($order);
    }
}
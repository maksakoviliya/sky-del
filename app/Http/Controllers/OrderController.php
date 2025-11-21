<?php

namespace App\Http\Controllers;

use App\Events\CourierAppointed;
use App\Exports\OrdersExport;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\OrderCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            $orders = Order::with(['courier', 'client', 'client.company', 'client.company.tarif'])
                ->orderBy('created_at', 'desc')
                ->filter($request->query())
                ->paginate(20);
        } else {
            $orders = Order::with('courier')
                ->orderBy('created_at', 'desc')
                ->where('user_id', $user->id)
                ->filter($request->query())
                ->paginate(20);
        }
        return new OrderCollection($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            "recipient_id" => "nullable",
            "type" => "required|in:foot,car",
            "delivery_date" => "required",
            "delivery_interval" => "required|array",
            "assessed_value" => "nullable",
            "cod" => "nullable|boolean",
            "cod_price" => "required_if:cod,true",
            "payment_type" => "required|in:cash,card",
            "price" => "required",
            "today" => "boolean",
            "comment" => "nullable",
            "weight" => "required",
            "quantity" => "required|alpha_num|min:0",
        ]);

        $recipient = Recipient::updateOrCreate([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'product_name' => $request->input('product_name'),
            'user_id' => Auth::user()->id,
        ]);


        $order = Order::create([
            'type' => $request->input('type'),
            'recipient_id' => $recipient->id,
            'delivery_date' => Carbon::parse($request->input('delivery_date')),
            'delivery_interval' => $request->input('delivery_interval'),
            'assessed_value' => $request->input('assessed_value'),
            'weight' => $request->input('weight'),
            'price' => $request->input('price'),
            'today' => $request->input('today'),
            'cod_price' => $request->input('cod_price'),
            'cod' => $request->input('cod') ? $request->input('cod') : false,
            'payment_type' => $request->input('payment_type'),
            'user_id' => Auth::user()->id,
            'comment' => $request->input('comment'),
            'quantity' => $request->input('quantity'),
        ]);

        Notification::send(User::where('role_id', 1)->get(), new OrderCreated($order));

        return new OrderResource($order);
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(Order $order, Request $request)
    {
        $request->validate([
            "recipient_id" => "nullable",
            "type" => "required|in:foot,car",
            "delivery_date" => "required",
            "delivery_interval" => "required|array",
            "assessed_value" => "nullable",
            "cod" => "nullable|boolean",
            "cod_price" => "required_if:cod,true",
            "payment_type" => "required|in:cash,card",
            "price" => "required",
            "comment" => "nullable",
            "today" => "boolean",
            "weight" => "nullable",
            "status" => "required|in:processing,work,delivered,undelivered",
            "quantity" => "required|alpha_num|min:0",
        ]);

        $recipient_data = [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'product_name' => $request->input('product_name'),
            'user_id' => $order->user_id,
        ];

        if ($order->recipient) {
            $order->recipient->update($recipient_data);
            $recipient = $order->recipient;
        } else {
            $recipient = Recipient::create($recipient_data);
        }


        $order->update([
            'type' => $request->input('type'),
            'recipient_id' => $recipient->id,
            'delivery_date' => Carbon::parse($request->input('delivery_date')),
            'delivery_interval' => $request->input('delivery_interval'),
            'assessed_value' => $request->input('assessed_value'),
            'weight' => $request->input('weight'),
            'price' => $request->input('price'),
            'cod' => $request->input('cod') ? $request->input('cod') : false,
            'cod_price' => $request->input('cod_price'),
            'payment_type' => $request->input('payment_type'),
            'today' => $request->input('today'),
            'comment' => $request->input('comment'),
            'status' => $request->input('status'),
            'courier_id' => $request->input('courier_id'),
            'quantity' => $request->input('quantity'),
        ]);
        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        if (Auth::user()->role_id == User::ROLE_ADMIN || $order->status == 'processing') {
            return $order->delete();
        }
        abort(403);
    }

    public function setOrderStatus(Request $request, Order $order)
    {
        $order->update([
            'status' => $request->input('status')
        ]);

        return new OrderResource($order);
    }

    public function setOrderCourier(Request $request, Order $order)
    {
        $order->update([
            'courier_id' => $request->input('courier_id')
        ]);

        // TODO: Сделать queueable
        CourierAppointed::dispatch($order);

        return new OrderResource($order);
    }

    public function setOrderPayment(Request $request, Order $order)
    {
        $order->update([
            'payment' => $request->input('payment')
        ]);

        return new OrderResource($order);
    }

    public function ordersPay(Request $request)
    {
        $request->validate([
            'orders' => 'required'
        ]);

        Order::whereIn('id', $request->input('orders'))->update([
            'payment' => 'payed'
        ]);

        return response()->json(['success']);
    }

    public function fetchOrdersAnalytics()
    {
        if (!Auth::user()->isAdmin()) {
            $delivered = Auth::user()->orders()->where('status', 'delivered')->count();
            $undelivered = Auth::user()->orders()->where('status', 'undelivered')->count();
            $price = Auth::user()->orders()->sum('price');
            $payed = Auth::user()->orders()->where('payment', 'payed')->sum('price');
        } else {
            $delivered = Order::where('status', 'delivered')->count();
            $undelivered = Order::where('status', 'undelivered')->count();
            $price = Order::sum('price');
            $payed = Order::where('payment', 'payed')->sum('price');
        }
        $data = [
            'delivered' => round($delivered, 2),
            'undelivered' => round($undelivered, 2),
            'price' => round($price, 2),
            'payed' => round($payed, 2),
            'debt' => round($price - $payed, 2),
        ];

        return response()->json(['data' => $data]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $orders = Order::query()
            ->whereIn('id', $request->input('orders'))
            ->get();

        return Excel::download(
            new OrdersExport($orders),
            "orders_export.xlsx"
        );
    }
}

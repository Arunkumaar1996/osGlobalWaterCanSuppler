@extends('layouts.customer')

@section('title', 'My Water Orders')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Order History</h1>
            <p class="text-xs text-slate-500 mt-0.5">Track live water deliveries and view invoices</p>
        </div>
        <a href="{{ route('home') }}#products" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
            <span>+ New Order</span>
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">No Orders Placed Yet</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Need drinking water? Choose your can capacity and schedule a delivery today.
            </p>
            <a href="{{ route('home') }}#products" class="inline-flex items-center gap-2 mt-5 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md">
                Browse Products
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-brand-300 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="font-extrabold text-sm text-slate-900">{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $order->order_status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                  ($order->order_status === 'out_for_delivery' ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                                  ($order->order_status === 'delivered' ? 'bg-slate-100 text-slate-700' : 'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                                {{ str_replace('_', ' ', $order->order_status) }}
                            </span>
                        </div>

                        <p class="text-slate-500">
                            Scheduled: <strong class="text-slate-800">{{ $order->delivery_date->format('d M Y') }}</strong> ({{ $order->deliverySlot?->name ?? 'Standard Slot' }})
                        </p>

                        <p class="text-slate-600">
                            Items: {{ $order->items->map(fn($i) => "{$i->quantity}x {$i->product_name_snapshot}")->join(', ') }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-0 border-slate-100">
                        <div class="text-left sm:text-right">
                            <span class="text-xs text-slate-400 block font-medium">Total Payable</span>
                            <span class="text-base font-black text-slate-900">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>

                        <a href="{{ route('customer.orders.show', $order->id) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition-colors">
                            Track Order &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection

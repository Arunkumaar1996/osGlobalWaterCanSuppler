@extends('layouts.staff')

@section('title', 'Driver Cash Collection Log')

@section('content')
<div class="space-y-4">
    <!-- Cash in Hand Banner -->
    <div class="bg-gradient-to-r from-emerald-900 to-slate-900 rounded-2xl p-5 border border-emerald-700/50 shadow-lg text-white">
        <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-300 block">Total Cash in Hand</span>
        <p class="text-3xl font-black mt-1 font-mono text-emerald-400">
            ₹{{ number_format($staff->current_cash_in_hand, 2) }}
        </p>
        <p class="text-[11px] text-slate-300 mt-2">
            Please deposit all collected cash with the branch hub cashier at the end of your shift runsheet.
        </p>
    </div>

    <!-- Cash Collections Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700 p-4 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-700/50">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Collected Cash Receipts</h3>
            <span class="text-[11px] font-bold text-slate-400">{{ $payments->total() }} Entries</span>
        </div>

        @if($payments->isEmpty())
            <div class="py-8 text-center text-slate-400 text-xs">
                No COD cash payments recorded yet today.
            </div>
        @else
            <div class="space-y-2">
                @foreach($payments as $pmt)
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-700/50 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono font-bold text-white block">#{{ $pmt->payment_number }}</span>
                            <span class="text-[11px] text-slate-400 block mt-0.5">
                                {{ $pmt->order ? 'Order #' . $pmt->order->order_number : 'Direct Settlement' }} &bull; {{ $pmt->created_at->format('h:i A') }}
                            </span>
                        </div>
                        <span class="font-mono font-black text-emerald-400 text-sm">
                            ₹{{ number_format($pmt->amount, 2) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="pt-2">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

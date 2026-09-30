<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSponsorPaymentRequest;
use App\Models\RaceSponsor;
use App\Models\SponsorPayment;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SponsorPaymentController extends Controller
{
    public function store(
        StoreSponsorPaymentRequest $request,
        RaceSponsor $raceSponsor
    ) {
        $data = $request->validated();

        $paid = (float) $raceSponsor
            ->payments()
            ->sum('amount');

        $agreed = (float) $raceSponsor->amount;

        $remaining = max(
            0,
            $agreed - $paid
        );

        $paymentAmount = (float) $data['amount'];

        if ($paymentAmount > $remaining) {
            return back()
                ->withErrors([
                    'amount' => 'El pago no puede ser mayor al saldo pendiente de $'.
                        number_format(
                            $remaining,
                            2
                        ).
                        '.',
                ]);
        }

        $paymentData = [
            'race_sponsor_id' => $raceSponsor->id,

            'amount' => $paymentAmount,

            'paid_at' => $data['paid_at'],

            'payment_method' => $data['payment_method'] ?? null,

            'reference' => $data['reference'] ?? null,

            'notes' => $data['notes'] ?? null,
        ];

        $receipt = $request->file('receipt');

        if ($receipt && $receipt->isValid()) {
            $filename =
                uniqid().
                '.'.
                $receipt->getClientOriginalExtension();

            $directory = storage_path(
                'app/public/sponsor-payments'
            );

            if (! is_dir($directory)) {
                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            $receipt->move(
                $directory,
                $filename
            );

            $paymentData['receipt'] =
                'sponsor-payments/'.$filename;
        }

        SponsorPayment::create(
            $paymentData
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'El pago se registró correctamente.',
        ]);

        return back();
    }

    public function destroy(
        RaceSponsor $raceSponsor,
        SponsorPayment $payment
    ) {
        abort_unless(
            $payment->race_sponsor_id === $raceSponsor->id,
            404
        );

        if ($payment->receipt) {
            Storage::disk('public')->delete(
                $payment->receipt
            );
        }

        $payment->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'El pago se eliminó correctamente.',
        ]);

        return back();
    }
}

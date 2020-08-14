<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use smasif\ShurjopayLaravelPackage\ShurjopayService;

class PaymentController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function pay(Request $request)
    {
        $shurjopay_service = new ShurjopayService();
        $tx_id = $shurjopay_service->generateTxId();
//        $tx_id = $shurjopay_service->generateTxId('MY_CUSTOM_ID');

        /*
        * SAVE TX_ID
        * needs migration
        */
        DB::table('orders')->insert([
            'tx_id' => $tx_id,
            'user_id' => auth()->id(),
            'amount' => $request->input('amount')
        ]);

//        $shurjopay_service->sendPayment($request->input('amount'), '/success'); // NO LOCALHOST
        $shurjopay_service->sendPayment($request->input('amount'));
    }

    public function success(Request $request)
    {
        if ($request->status == 'Success') {
            /*
            * PAYMENT SUCCESS
            * needs migration
            */
            DB::table('orders')
                ->where('tx_id', $request->tx_id)
                ->where('user_id', auth()->id())
                ->update([
//                    'amount' => $request->amount,
                    'bank_tx_id' => $request->bank_tx_id,
                    'bank_status' => $request->bank_status,
                    'sp_code' => $request->sp_code,
                    'sp_code_des' => $request->sp_code_des,
                    'sp_payment_option' => $request->sp_payment_option,
                    'status' => $request->status
                ]);
        } else {
            /*
            * PAYMENT FAILED
            * needs migration
            */
            DB::table('orders')
                ->where('tx_id', $request->tx_id)
                ->where('user_id', auth()->id())
                ->update([
//                    'amount' => $request->amount,
                    'bank_status' => $request->bank_status,
                    'sp_code' => $request->sp_code,
                    'sp_code_des' => $request->sp_code_des,
                    'sp_payment_option' => $request->sp_payment_option,
                    'status' => $request->status
                ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Wallet;
use App\Models\Transaction;
use Auth;
class PayPalController extends Controller
{
    /**
     * create transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTransaction()
    {
        return view('transaction');
    }

    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(Request $request)
    {

        session(['return_url' => $request->return_url]);   
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successTransaction'),
                "cancel_url" => $request->cancel_url,
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->amount.".00"
                    ]
                ]
            ]
        ]);
        echo "<pre>";
        print_r($response);die;
        return response()->json($response);
    }

    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        $userid = Auth::user()->id;

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $amount = $response['purchase_units'][0]['payments']['captures'][0]['amount'];
            $Transaction = new Transaction;
            $Transaction->transaction_id = $response['id'];
            $Transaction->amount = $amount['value'];
            $Transaction->payer_name = $response['payer']['name']['given_name'];
            $Transaction->email_address = $response['payer']['email_address'];
            $Transaction->user_id = $userid;
            $Transaction->save();
            $Wallet = Wallet::where('user_id', $userid)->first();

            if( empty($Wallet) ){
                $Wallet = new Wallet; 
                $Wallet->user_id  = $userid;
                $Wallet->balance  =  $amount['value'];
                $Wallet->save();
            }else{
                $Wallet->balance = ($Wallet->balance)+$amount['value'];
                $Wallet->save();
            }
            
        }
        
        return redirect()
            ->route('home');
    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction(Request $request)
    {
        return redirect()
            ->route('createTransaction')
            ->with('error', $response['message'] ?? 'You have canceled the transaction.');
    }
}
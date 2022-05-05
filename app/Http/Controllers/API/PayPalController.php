<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Wallet;
use App\Models\Transaction;
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
    public function processTransaction(Request $request)
    {  
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" =>$request->return_url,
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
        $response = $provider->showOrderDetails($request->transaction_id);
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
        return response()->json(['transaction' => $Transaction, 'wallet' => $Wallet]);
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

    /**
     * Pay With Card 
     * 
    */


    public function PayWithCard(Request $request)
    {
        $request_params = array (
          	'METHOD' => 'DoDirectPayment',
          	'USER' => 'dushant555sharma_api1.youpmail.com',
          	'PWD' => '9AJXF6JJLU5NAL5Y',
          	'SIGNATURE' => 'AdsLMB.zs8b3BHI0m1huNKPvN73nAV0dAYcRPjTtX6.upqA-I2u4.bDe',
          	'VERSION' => '85.0',
          	'PAYMENTACTION' => 'Sale',
          	'IPADDRESS' => '127.0.0.1',
          	'CREDITCARDTYPE' => 'Visa',
          	'ACCT' => '4032037713246250',
          	'EXPDATE' => '052027',
          	'CVV2' => '123',
          	'FIRSTNAME' => 'Yang',
          	'LASTNAME' => 'Ling',
          	'STREET' => '1 Main St',
          	'CITY' => 'San Jose',
          	'STATE' => 'CA',
          	'COUNTRYCODE' => 'US',
          	'ZIP' => '95131',
          	'AMT' => '100.00',
          	'CURRENCYCODE' => 'USD',
          	'DESC' => 'Testing Payments Pro'
       );     
     
       $nvp_string = '';     
       foreach($request_params as $var=>$val)
       {
          $nvp_string .= '&'.$var.'='.urlencode($val);
       }
     
       	$curl = curl_init();     
       	curl_setopt($curl, CURLOPT_VERBOSE, 0);     
       	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);     
       	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);     
       	curl_setopt($curl, CURLOPT_TIMEOUT, 30);     
       	curl_setopt($curl, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp');     
       	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);     
       	curl_setopt($curl, CURLOPT_POSTFIELDS, $nvp_string); 

       	$result = curl_exec($curl);     
       	curl_close($curl);   

       	$data = $this->NVPToArray($result);

       	if($data['ACK'] == 'Success') {
       		# Database integration...
       		return response()->json(['transaction' => $data]);
       		
       	} if ($data['ACK'] == 'Failure') {
       		# Database integration...
       		echo "Your payment was declined/fail.";
       	} else {
       		echo "Something went wront please try again letter.";
       	}
    }

    public function  NVPToArray($NVPString)
    {
       $proArray = array();
       while(strlen($NVPString)) {
            // key
            $keypos= strpos($NVPString,'=');
            $keyval = substr($NVPString,0,$keypos);
            //value
            $valuepos = strpos($NVPString,'&') ? strpos($NVPString,'&'): strlen($NVPString);
            $valval = substr($NVPString,$keypos+1,$valuepos-$keypos-1);

            $proArray[$keyval] = urldecode($valval);
            $NVPString = substr($NVPString,$valuepos+1,strlen($NVPString));
        }
        return $proArray;
    }
}
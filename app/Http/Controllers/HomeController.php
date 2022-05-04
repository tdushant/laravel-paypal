<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Auth;
use Omnipay\Omnipay;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {   

        $id = Auth::user()->id;
        $balance = Wallet::selectRaw('sum(balance) as balance')->where('user_id', $id)->first();
        return view('home', compact('balance'));
    }


    public function payment(Request $request)
    {
        $gateway = Omnipay::create('PayPal_Pro');
        $gateway->setUsername(env('PAYPAL_USER'));
        $gateway->setPassword(env('PAYPAL_USER'));
        $gateway->setSignature(PAYPAL_API_SIGNATURE);
        $gateway->setTestMode(true);
        echo "<pre>";
        print_r($gateway);die;
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

    function get_transaction_details( $transaction_id ) { 
        $api_request = 'USER=' . urlencode( env('PAYPAL_USER') )
                    .  '&PWD=' . urlencode( env('PAYPAL_PWD') )
                    .  '&SIGNATURE=' . urlencode( env('PAYPAL_SIGNATURE') )
                    .  '&VERSION=76.0'
                    .  '&METHOD=GetTransactionDetails'
                    .  '&TransactionID=' . $transaction_id;
    
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp' ); // For live transactions, change to 'https://api-3t.paypal.com/nvp'
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
    
        // Uncomment these to turn off server and peer verification
        // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        // curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
    
        // Set the API parameters for this transaction
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_request );
    
        // Request response from PayPal
        $response = curl_exec( $ch );
        // print_r($response);
    
        // If no response was received from PayPal there is no point parsing the response
        if( ! $response )
            die( 'Calling PayPal to change_subscription_status failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
    
        curl_close( $ch );
    
        // An associative array is more usable than a parameter string
        parse_str( $response, $parsed_response );
    
        return $parsed_response;
    }
}

<?php
namespace App\Http\Controllers;

use App\Bank;
use App\Currency;
use App\Exceptions\HttpException;
use App\Portfolio;
use App\Security;
use App\Transaction;
use App\User;
use Dingo\Api\Exception\InternalHttpException;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Tymon\JWTAuth\Facades\JWTAuth;

class TestController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        $portfolios = Portfolio::all();
        $banks = Bank::all();
        $securities = Security::all();
        
        return view('tests.index')
            ->with('currencies', $currencies)
            ->with('portfolios', $portfolios)
            ->with('banks', $banks)
            ->with('securities', $securities);
    }

    public function handleTransaction(Request $request)
    {
        try {
            $action_url = $request->get('action_url');

            // Make sure to translate security symbols to security ID's
            if ($request->has('security_id')) {
                $security_id = $request->get('security_id');
                if (!is_numeric($security_id)) {
                    $security = Security::where('symbol', $security_id)->first();
                    if (!$security)
                        throw new HttpException("Security not found by symbol: $security_id");
                    $security_id = $security->id;
                    $all = $request->all();
                    $all['security_id'] = $security_id;
                    $request->replace($all);
                }
            }

            $request->replace($request->except('action_url'));
            //dd($request->all());
            $response = $this->dispatchRequest('put', $action_url, $request);
            return view('tests.components.successTx', [
                'item' => $response['item'],
            ]);
        }
        catch (InternalHttpException $error) {
    
            $response = $error->getResponse()->getOriginalContent();
            return view('tests.components.errorTx', ['error' => $response['message']]);
        }
        catch (\Exception $error) {
    
            return view('tests.components.errorTx', ['error' => $error->getMessage()]);
        }
    }

    public function getTransactionHistory($portfolio_id)
    {
        try {
            $transactions = Transaction::where('portfolio_id', $portfolio_id)->orderBy('date', 'desc')->get();
            return view('tests.fragments.transaction-history', ['transactions' => $transactions]);
        }
        catch (\Exception $error) {
            return [
                'status' => 'error',
                'message' => $error->getMessage()
            ];
        }
    }

    public function testCalculate(Request $request)
    {
        
        $buyTransaction =
            [
                "trade_value" => 100,
                "book_value" => 100,
                "commision" => 14,
                "is_commision_included" => true,
                "local_currency_rate" => 6.2,
                "local_currency_rate_book_value" => 5.2,
                "use_quantity" => 40,
                "is_same_currency" => false,
                "quantity" => 40,
            ];
        
        $trans = new \App\Pecoonia\Calculator\Transaction($buyTransaction, []);
        
        $calculator = new \App\Pecoonia\Calculator\TransactionCalculator($trans);
        
        print $calculator->TradeValue->getLocal() . "<hr>";
        
        return "Test.";
    }
    
    public function setupSecurity(Request $request, $security)
    {
        try {
            $response = $this->dispatchRequest('get', '/api/security/find/' . $security);
            $result = view('tests.fragments.security-result', [
                'symbol' => $security,
                'item' => $response['item']
            ]);
            return $result;
        }
        catch (InternalHttpException $error) {
            $response = $error->getResponse()->getOriginalContent();
            return $response['message'];
        }
        catch (\Exception $error) {
            return $error->getMessage();
        }
    }
    
    private function dispatchRequest($verb, $url, Request $request = null, $use_token = true)
    {
        $dispatcher = app('Dingo\Api\Dispatcher');

        // Create a token to dispatch this request with
        if ($use_token) {
            $user = User::find(1);
            if (!$user)
                $user = User::query()->orderByRaw('rand()')->first();
            if (!$user)
                return "Error: No user available. Please create one first.";
            $token = JWTAuth::fromUser($user);
            $dispatcher->header('Authorization', "Bearer $token");
        }

        if ($request)
            return $dispatcher->$verb($url, $request->all());
        else
            return $dispatcher->$verb($url);
    }
}

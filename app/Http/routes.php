<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::group(['prefix' => 'angular'], function () {
        Route::get('{a}/{b?}/{c?}', [function ($a, $b = '', $c = '') {
            $b = ($b != '') ? '.' . $b : '';
            $c = ($c != '') ? '.' . $c : '';
            $template = 'angular.' . $a . $b . $c;
            return view($template);
        }]);
    });

    // GET user/activation/{token}
    // Handles user activation
    Route::get('user/activation/{token}', 'AuthController@activateUser')->name('user.activate');
});

Route::group(array('domain' => env('API_DOMAIN')), function(){

    Route::group(['middleware' => 'web'], function () {
        Route::auth();
        Route::get("facebook/redirect/{from}/{remember}", "SocialAuthController@redirect");
        Route::get("facebook/callback", "SocialAuthController@facebookCallback");
        Route::get("google/redirect/{from}/{remember}", "SocialAuthController@googleRedirect");
        Route::get("google/callback", "SocialAuthController@googleCallback");
        Route::get("/", "FrontendController@index");
    });

    // Register a custom exception handler for SQL errors
    /*app('Dingo\Api\Exception\Handler')->register(function (\Illuminate\Database\QueryException $exception) {
        $code = $exception->getCode();
        $response = [
            "status" => 'error',
            "message" => "Internal DB error [code = $code]"
        ];

        return Response::make($response, 500);
    });*/



    $api = app('Dingo\Api\Routing\Router');


    // This route will be available here: api/hello
    $api->version('v1', function ($api) {

        $api->post('send-feedback', 'App\Http\Controllers\Controller@sendFeedback');
        $api->get('cms/content', 'App\Http\Controllers\Controller@getCmsContent')->name('get_cms_content');

        //----------------------------------- TEST ROUTES -----------------------------------
        // Welcome route
        $api->get('hello', function () {
            return ["message" => "Hello World", "status" => "ok"];
        });

        // Check fields for unique value
        $api->get('input', 'App\Http\Controllers\FrontendController@input');

        $api->get('test', 'App\Http\Controllers\TestController@index');
        $api->get('test/tx_history/{portfolio_id}', 'App\Http\Controllers\TestController@getTransactionHistory');
        $api->post('test/handle_transaction', 'App\Http\Controllers\TestController@handleTransaction');
        $api->get('test/security/find/{security_id}', 'App\Http\Controllers\TestController@setupSecurity');
        $api->get('calculate', 'App\Http\Controllers\TestController@testCalculate');

        // ----------------------------------- AUTHENTICATE ROUTES -----------------------------------

        // POST /authenticate
        // Authenticates a user using existing credentials. Returns a token that can be used for subsequent requests.
        $api->post('authenticate', 'App\Http\Controllers\AuthController@authenticate');

        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // POST /refresh_token
            // Extends a token's lifetime.
            $api->post('refreshtoken', 'App\Http\Controllers\AuthController@refreshToken');

            // POST /end_token
            // Invalidates a token - same effect as logging out.
            $api->post('endtoken', 'App\Http\Controllers\AuthController@endToken');
        });


        // --------------------------------------- USER ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // GET /me
            // Retrieves information of currently logged-in user
            $api->get('me', 'App\Http\Controllers\UserController@getUser');

            // POST /me
            // Updates the currently logged-in user
            $api->post('me', 'App\Http\Controllers\UserController@updateUser');

            // POST /me/delete
            // Deletes currently logged in user account if the password in POST is correct
            $api->post('/me/delete', 'App\Http\Controllers\UserController@deleteUserAccount');

            // POST /user/force_change_password
            // Forces the user to change password after some interval
            $api->post('/user/force_change_password', 'App\Http\Controllers\UserController@forceChangePassword');

            // GET /user/close_welcome_msg
            $api->get('/user/close_welcome_msg', 'App\Http\Controllers\UserController@closeWelcomeMsg');
        });

        /*
         * Password Reset Handling
         */
        $api->group(['prefix' => 'password'], function ($api) {
            $api->post('email', 'App\Http\Controllers\Auth\PasswordController@postEmail');
            $api->post('reset', 'App\Http\Controllers\Auth\PasswordController@postReset');
        });

        // PUT /me
        // Creates a new user.
        $api->put('me', 'App\Http\Controllers\UserController@createUser');

        // PUT /user
        // Creates a new user.
        $api->put('user', 'App\Http\Controllers\UserController@createUser');


        // --------------------------------------- PORTFOLIO ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // GET /portfolio
            // Retrieves only Active portfolios from the current user logged in
            $api->get('portfolio', 'App\Http\Controllers\PortfolioController@index');

            // GET /portfolio/all
            // Retrieves ALL portfolios from the current user logged in
            $api->get('portfolio/all', 'App\Http\Controllers\PortfolioController@getAllPortfolios');

            // GET /portfolio/id
            // Retrieves portfolio with ID from the current user logged in
            $api->get('portfolio/{id}', 'App\Http\Controllers\PortfolioController@getPortfolio');

            // GET /portfolio/id/bank
            // Retrieves bank of portfolio with ID from the current user logged in
            //$api->get('portfolio/{id}/banks', 'App\Http\Controllers\PortfolioController@getPortfolio');

            // POST /portfolio/id
            // Updates the portfolio with ID from the current user logged in
            $api->post('portfolio/{id}', 'App\Http\Controllers\PortfolioController@updatePortfolio');

            // POST /portfolio/id
            // Deletes the portfolio with ID from the current user logged in
            $api->post('portfolio/{id}/delete', 'App\Http\Controllers\PortfolioController@deletePortfolio');

            // PUT /portfolio
            // Creates a new portfolio.
            $api->put('portfolio', 'App\Http\Controllers\PortfolioController@createPortfolio');

            // GET /portfolio/id/daily_values
            // Retrieves daily values of portfolio
            $api->get('/portfolio/{id}/daily_values/{lastDays}', 'App\Http\Controllers\PortfolioController@getDailyValues');

            // GET /portfolio/id/statistics
            // Retrieves statistics of portfolio
            $api->get('/portfolio/{id}/statistics/', 'App\Http\Controllers\PortfolioController@getStatistics');
        });


   // --------------------------------------- PORTFOLIO GUIDELINES ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // GET /portfolio/{portfolio_id}/currencies
            // Retrives currencies from the portfolio with ID from the current user logged in
            $api->get('portfolio/{portfolio_id}/currencies', 'App\Http\Controllers\PortfolioGuidelineController@getCurrencies');

            // GET /portfolio/{portfolio_id}/security_types
            // Retrives security_types from the portfolio with ID from the current user logged in
            $api->get('portfolio/{portfolio_id}/security_types', 'App\Http\Controllers\PortfolioGuidelineController@getSecurityTypes');

            // GET /portfolio_guidelines/{portfolio_id}
            // Retrieves only Active portfolio guidelines from the current user logged in
            $api->get('portfolio_guidelines/{portfolio_id}', 'App\Http\Controllers\PortfolioGuidelineController@index');

            // POST /portfolio_guidelines/{portfolio_id}
            // Add Guidelines for the given portfolio id of the current user logged in.
            $api->post('portfolio_guidelines_add/{portfolio_id}', 'App\Http\Controllers\PortfolioGuidelineController@addGuideline');

            // DELETE /portfolio_guidelines/{portfolio_id}/{guideline_id}
            // Remove Guidelines for the given portfolio id of the current user logged in.
            $api->delete('portfolio_guidelines/{portfolio_id}/{guideline_id}', 'App\Http\Controllers\PortfolioGuidelineController@removeGuideline');

            // GET /portfolio_guidelines_list
            $api->get('portfolio_guidelines_list', 'App\Http\Controllers\PortfolioGuidelineController@getGuidelinesList');

        });

        // --------------------------------------- BANK ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // GET /bank
            // Retrieves bank from the current user logged in
            $api->get('bank', 'App\Http\Controllers\BankController@index');

            // GET /bank/id
            // Retrieves bank with ID from the current user logged in
            $api->get('bank/{id}', 'App\Http\Controllers\BankController@getBank');

            // POST /bank/id
            // Updates the bank with ID from the current user logged in
            $api->post('bank/{id}', 'App\Http\Controllers\BankController@updateBank');

            // DELETE /bank/id
            // Deletes the bank with ID from the current user logged in
            $api->delete('bank/{id}', 'App\Http\Controllers\BankController@deleteBank');

            //PUT /bank
            // Creates a new bank.
            $api->put('bank', 'App\Http\Controllers\BankController@createBank');

            //GET /portfolio/{id}/banks
            // Retrieves banks with portfolio_id = ID
            $api->get('portfolio/{id}/banks', 'App\Http\Controllers\BankController@getBankByPortfolio');
        });


        // --------------------------------------- SECURITY ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // GET /security/{id}
            // Retrieves security with the id
            $api->get('security/{id}', 'App\Http\Controllers\SecurityController@getSecurity');

            // GET /security/find/{keyword}
            // Retrieves security with the the keyword match
            $api->get('security/find/{keyword} ', 'App\Http\Controllers\SecurityController@getSecuritiesByKeyword');

            // GET /security
            // Retrieves security with the user_id of logged in user
            $api->get('security', 'App\Http\Controllers\SecurityController@getUserSecurities');

            // GET /securities
            // Return all securities from database
            $api->get('/securities', 'App\Http\Controllers\SecurityController@getAllSecurities');
            $api->post('/securities', 'App\Http\Controllers\SecurityController@getAllSecurities');

            // GET  /portfolio/{id}/securities
            // Retrieves security with the user_id of logged in user and match with the ID
            $api->get('/portfolio/{id}/securities', 'App\Http\Controllers\SecurityController@getPortfolioSecurities');

            $api->get('/portfolio/{id}/holdings', 'App\Http\Controllers\SecurityController@getPortfolioHoldings');

            $api->post('/portfolio/{id}/holdings', 'App\Http\Controllers\SecurityController@getPreviousPortfolioHoldings');

        });

        // --------------------------------------- TRANSACTION ROUTES ---------------------------------------

        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            $api->get('/holding_transactions/{portfolio_id}/{security_id}', 'App\Http\Controllers\SecurityController@getTransactionsBySecurity');

            $api->post('/get_key_figures/{portfolio_id}', 'App\Http\Controllers\SecurityController@getKeyFigures');

        });

        // --------------------------------------- WATCHLIST ROUTES ---------------------------------------

        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            $api->get('/get_unwatched_securities/{portfolio_id}','App\Http\Controllers\SecurityController@getUnwatchedSecurities');

            $api->post('/add_securities_to_watchlist', 'App\Http\Controllers\SecurityController@addSecuritiesToWatchlist');

            $api->get('/get_security_watchlist/{portfolio_id}', 'App\Http\Controllers\SecurityController@getSecurityWatchlist');

            $api->get('/remove_security_from_watchlist/{portfolio_id}/{security_id}', 'App\Http\Controllers\SecurityController@removeSecurityFromWatchlist');

            $api->post('/add_new_securities', 'App\Http\Controllers\SecurityController@addNewSecurities');
        });


        // ---------------------------------------GAIN LOSS BY CURRENCY --------------------------------------

        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            $api->post('/get_holdings_analysis/{portfolio_id}/', 'App\Http\Controllers\SecurityController@getHoldingsAnalysis');

            $api->get('/get_holdings_analysis_attributes/{portfolio_id}/', 'App\Http\Controllers\SecurityController@getHoldingsAnalysisAttributes');


        });


        // ---------------------------------------INCOME ANALYSIS --------------------------------------

        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            $api->post('/get_income_analysis/{portfolio_id}/', 'App\Http\Controllers\SecurityController@getIncomeAnalysis');
        });



        // --------------------------------------- LOGIC ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {


            $api->get('transaction/test', 'App\Http\Controllers\LogicController@test');

            // PUT  /transaction/bookvalue/count
            //
            $api->put('transaction/bookvalue/count', 'App\Http\Controllers\LogicController@getBookValueAmount');

            // PUT  /transaction/buy
            //
            $api->put('transaction/buy', 'App\Http\Controllers\LogicController@buyTransaction');

            // PUT  /transaction/sell
            //
            $api->put('transaction/sell', 'App\Http\Controllers\LogicController@sellTransaction');

            // PUT  /transaction/bookvalue
            //
            $api->put('transaction/bookvalue', 'App\Http\Controllers\LogicController@bookValueTransaction');

            // PUT  /transaction/dividend
            //
            $api->put('transaction/dividend', 'App\Http\Controllers\LogicController@dividendTransaction');

            // PUT  /transaction/cash
            //
            $api->put('transaction/cash', 'App\Http\Controllers\LogicController@cashTransaction');

            // GET /transaction
            // Return the last 50 transactions of logged in user, paginated by 10
            $api->get('transaction', 'App\Http\Controllers\LogicController@getTransactions');

            // GET /transaction/search - Search for transactions belonging to the current user.
            // Able to search by
            //  security symbol ( one or many )
            //  currencies ( one or many )
            //  transaction_type ( one or many )
            //  date_interval ( date (Not created or updated at dates, but the transaction date added by the client) between x AND y )
            $api->get('transaction/search', 'App\Http\Controllers\LogicController@findTransactions');

            // GET /transaction/{id}
            // Return the transaction with the ID if belongs to the logged in user
            $api->get('transaction/{id}', 'App\Http\Controllers\LogicController@getTransaction');

            // GET /transaction/{id}/all
            // Displays the transaction with the given ID, and all transaction history, if the
            // transaction belongs to the currently logged in user.
            $api->get('transaction/{id}/all', 'App\Http\Controllers\LogicController@getTransactionAll');

            // DELETE /transaction/{id}
            // Deletes the Transaction
            $api->delete('transaction/{id}', 'App\Http\Controllers\LogicController@deleteTransaction');

            // GET /portfolio/{id}/transactions
            // Returns the last 20 transactions belonging to the given portfolio, if the portfolio is owned by the
            // currently logged in user
            $api->get('portfolio/{id}/transactions', 'App\Http\Controllers\LogicController@getPortfolioTransactions');

            // GET /bank/{id}/transactions
            // Returns the last 50 transactions belonging to the given bank, if the bank is owned
            // by the currently logged in user.
            $api->get('bank/{id}/transactions', 'App\Http\Controllers\LogicController@getBankTransactions');

            // GET /portfolio/{id}/tags
            // Returns portfolio tags
            $api->get('portfolio/{id}/tags', 'App\Http\Controllers\LogicController@getPortfolioTags');

            // GET /all_tags
            // Returns all the tags
            $api->get('all_tags', 'App\Http\Controllers\LogicController@getAllTags');

            // DELETE /tag/{tag}
            // Deletes the tag belonging to current user
            $api->delete('tag/{tag}', 'App\Http\Controllers\LogicController@deleteTag');
        });


        // --------------------------------------- CURRENCY ROUTES ---------------------------------------


        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            // GET  /currency
            //
            $api->get('currency', 'App\Http\Controllers\CurrencyController@allCurrencies');

            // GET /currency_pair
            $api->get('currency_pair/{symbol}/{portfolioId}', 'App\Http\Controllers\CurrencyController@yahooApiCurrenciesPairs');
        });
    });
});

Route::group(array('domain' => env('ADMIN_DOMAIN')), function(){

    // GET /
    // Administrator login page
    Route::get("/", "Dashboard\AuthController@loginForm");

    // GET /logout
    // Administrator logout
    Route::get("/logout", "Dashboard\AuthController@logout");

    // GET /login
    // Administrator login
    Route::post("/login", "Dashboard\AuthController@login");

    // GET /create-admin
    // Creating administrator
    Route::get("/create-admin", "Dashboard\AuthController@createAdmin");

    // --------------------------------------- SECURITY ROUTES ---------------------------------------

    Route::group(['middleware' => ['dashboard']], function () {

        Route::get("/profile", "Dashboard\AuthController@changePassForm");
        Route::put("/change-pass", "Dashboard\AuthController@changePass");

        Route::get("/dashboard", function() {
            return view('dashboard.statistics.user');
        });

        Route::get("/users-view", "Dashboard\UserController@viewUsers");
        Route::put("/edit-user", "Dashboard\UserController@editUser");
        Route::delete("/delete-user-first", "Dashboard\UserController@deleteUserFirst");
        Route::delete("/delete-user", "Dashboard\UserController@deleteUser");

    });
});

<!DOCTYPE html>
<html>
    <head>
        <meta name="charset" charset="utf-8"/>
        <title>Test Suite</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
              rel="stylesheet"
              integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
              crossorigin="anonymous">
        <script src="http://code.jquery.com/jquery-3.1.1.slim.js"
                integrity="sha256-5i/mQ300M779N2OVDrl16lbohwXNUdzL/R2aVUXyXWA="
                crossorigin="anonymous"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>


        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
                integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
                crossorigin="anonymous"></script>

        <style type="text/css">
            table {
                width: 100%;
                table-layout: fixed;
            }

            form h2 {
                font-size: 16pt;
                background-color: #f0f0f0;
                border-radius: 5px;
                padding: 10px;
                margin-top: 0px;
            }

            form section.block {
                border: 1px solid #f0f0f0;
                background-color: #f8f8f8;
                border-radius: 2px;
                padding: 10px;
            }

            form section.block + section.block {
                margin-top: 30px;
            }

            section.block section.row + hr {
                margin-top: 15px;
                margin-bottom: 15px;
                border-top: 4px solid dimgray;
            }

            #app {
                overflow: hidden;
            }

            #pager {
                float: left;
                width: 200px;

                box-sizing: border-box;
                padding: 0px 10px 10px 10px;

                margin: 0px;
            }

            #pager section {
                border: 1px solid #f0f0f0;
                border-radius: 2px;
                background-color: #fafafa;
                padding: 5px;
            }

            #pager ul {
                padding: 0px;
                margin: 0;
            }

            #pager ul li a.tab {
                border-radius: 0;
                padding: 4px;
                width: 100%;
                display: block;
                margin-bottom: 5px;
            }

            #body {
                float: right;
                width: calc(100% - 210px);
                min-width: 150px;
            }

            @media (max-width: 500px) {
                #body {
                    float: none;
                    width: 100%;
                    min-width: 400px;
                }

                #pager {
                    float: none;
                    width: 100%;
                    min-width: 200px;
                }
            }

            .page-inactive {
                display: none;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th, td {
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #f2f2f2
            }

            .portfolio-properties {
                display: none;
            }

            .security-result {
                display: none;
            }

            .tx-history {
                display: none;
            }

            .tx-history table td
            {
                font-size: 10px;
            }

            .tx-history table th {
                font-size:11px;
            }

            .preload {
                position: absolute;
                left: -2000px;
            }
        </style>
    </head>
    <body>
        <div id="app" class="container">
            <div class="header">
                <div class="title">
                    <h1>Test Suite</h1>
                </div>
            </div>

            <br>
            <br>

            {{-- A simple page system--}}
            @include('tests.components.left-pager')

            <div id="body" class="body">
                <form class="row"
                      method="post" id="#main-form">

                    <input type="hidden" id="tx-type">
                    {{-- Transactions page --}}
                    @include('tests.pages.transactions')

                    {{-- Portfolios page--}}
                    @include('tests.pages.portfolios')

                    {{-- Securities page--}}
                    @include('tests.pages.securities')

                    {{-- Right side info --}}
                    @include('tests.components.right-sidebar')

                </form>
            </div>
        </div>


        {{--  SELL Transaction, optional bank_id  --}}
        {{--  portfolio_id, security_id, date, quantity, trade_value, local_currency_rate, commision, is_commision  --}}
        <script type="text/html" id="tx-form-sell">
            <div id="tx-form-sell">
                <input type="hidden"
                       id="action_url"
                       name="action_url"
                       value="/api/transaction/sell" />

                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-sell-date">Date</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input type="date" id="tx-sell-date" name="tx-date"
                                   class="form-control"
                                   placeholder="Enter the date of the transaction"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-sell-quantity">Quantity</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-sell-quantity" name="tx-quantity" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the quantity (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-sell-trade-value">Trade Value</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-sell-trade-value" name="tx-trade-value" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the Trade value(Only numbers)"/>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <label for="tx-sell-local-currency-rate">Local Currency Rate</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number"
                                   id="tx-sell-local-currency-rate"
                                   name="tx-local-currency-rate"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="Enter the Local Currency Rate (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-sell-commision">Commision</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-sell-commision" name="tx-commision" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the Commision (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-bank-id">Optional selection of a bank</label>
                        <select name="tx-bank-id" id="tx-bank-id" class="form-control">
                            <option value="null">Empty</option>
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox">
                            <label for="tx-sell-is-commision-included">
                                <input name="tx-is-commision" id="tx-sell-is-commision-included" type="checkbox"/>
                                Is Commision Included</label>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        {{--  BUY TRANSACTION, optional bank id  --}}
        {{-- portfolio_id, security_id, date, quantity, trade_value, local_currency_rate, commision, is_commision --}}
        <script type="text/html" id="tx-form-buy">

            <div id="tx-form-buy">
                <input type="hidden"
                       id="action_url"
                       name="action_url"
                       value="/api/transaction/buy"/>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-buy-date">Date</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input type="date" id="tx-buy-date" name="tx-date"
                                   class="form-control"
                                   placeholder="Enter the date of the transaction"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-buy-quantity">Quantity</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-buy-quantity" name="tx-quantity" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the quantity (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-buy-trade-value">Trade Value</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-buy-trade-value" name="tx-trade-value" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the Trade value(Only numbers)"/>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <label for="tx-buy-local-currency-rate">Local Currency Rate</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number"
                                   id="tx-buy-local-currency-rate"
                                   name="tx-local-currency-rate"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="Enter the Local Currency Rate (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-buy-commision">Commision</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-buy-commision" name="tx-commision" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the Commision (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-bank-id">Optional selection of a bank</label>
                        <select name="tx-bank-id" id="tx-bank-id" class="form-control">
                            <option value="null">Empty</option>
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox">
                            <label for="tx-buy-is-commision-included">
                                <input name="tx-is-commision" id="tx-buy-is-commision-included" type="checkbox"/>
                                Is Commision Included</label>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        {{--  CASH transaction  --}}
        {{--  bank_id, amount, date, action, text  --}}
        <script type="text/html" id="tx-form-cash">
            <div id="tx-form-cash">
                <input type="hidden"
                       id="action_url"
                       name="action_url"
                       value="/api/transaction/cash"/>

                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-bank-id">Bank</label>
                        <select name="tx-bank-id" id="tx-bank-id" class="form-control">
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-cash-action">Action</label>
                        <select name="tx-action" id="tx-cash-action" class="form-control">
                            <option value="deposit">Deposit</option>
                            <option value="withdraw">Withdraw</option>
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-cash-date">Date</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input type="date" id="tx-cash-date" name="tx-date"
                                   class="form-control"
                                   placeholder="Enter the date of the transaction"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-cash-amount">Amount</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-cash-amount" name="tx-amount" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the amount (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>

                <div class="row">
                    <div class="col-md-12">
                        <label for="tx-cash-text">Text</label>
                        <textarea name="tx-text" id="tx-cash-text" rows="10" class="form-control"></textarea>
                    </div>
                </div>
                <br>
            </div>
        </script>

        {{--  BOOK VALUE transaction  --}}
        {{-- portfolio_id, security_id, date, bank_id, book_value, local_currency_rate --}}
        <script type="text/html" id="tx-form-bookvalue">
            <div id="tx-form-bookvalue">
                <input type="hidden"
                       id="action_url"
                       name="action_url"
                       value="/api/transaction/bookvalue"/>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-bookvalue-date">Date</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input type="date" id="tx-sell-date" name="tx-date"
                                   class="form-control"
                                   placeholder="Enter the date of the transaction"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-bookvalue-bookvalue">Book Value</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number"
                                   id="tx-bookvalue-bookvalue"
                                   name="tx-book_value"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="Enter the book value (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-bookvalue-local-currency-rate">Local Currency Rate</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number"
                                   id="tx-bookvalue-local-currency-rate"
                                   name="tx-local-currency-rate"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="Enter the Local Currency Rate (Only numbers)"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-bank-id">Bank</label>
                        <select name="tx-bank-id" id="tx-bank-id" class="form-control">
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
            </div>
        </script>

        {{-- DIVIDEND transaction --}}
        {{-- portfolio_id, security_id, date, bank_id, dividend, local_currency_rate, is_tax_included, tax --}}
        <script type="text/html" id="tx-form-dividend">
            <div id="tx-form-dividend">
                <input type="hidden"
                       id="action_url"
                       name="action_url"
                       value="/api/transaction/dividend"/>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-dividend-date">Date</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input type="date" id="tx-dividend-date" name="tx-date"
                                   class="form-control"
                                   placeholder="Enter the date of the transaction"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-dividend-dividend">Dividend</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number"
                                   id="tx-dividend-dividend"
                                   name="tx-dividend"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="Enter the dividend (Only numbers)"/>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-dividend-local-currency-rate">Local Currency Rate</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number"
                                   id="tx-dividend-local-currency-rate"
                                   name="tx-local-currency-rate"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="Enter the Local Currency Rate (Only numbers)"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tx-bank-id">Bank</label>
                        <select name="tx-bank-id" id="tx-bank-id" class="form-control">
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="tx-dividend-tax">Tax</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                            </div>
                            <input type="number" id="tx-dividend-tax" name="tx-tax" step="0.01" min="0"
                                   class="form-control"
                                   placeholder="Enter the Tax (Only numbers)"/>
                        </div>
                    </div>

                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <div class="checkbox">
                            <label for="tx-dividend-is-tax-included">
                                <input name="tx-is-tax-included" id="tx-dividend-is-tax-included" type="checkbox"/>
                                Is Tax Included</label>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="text/javascript">
            function getHtmlScriptFragment(id) {
                var html = $.parseHTML($('#' + id).html());
                return html;
            }


            function sendTransaction() {
                var data = {
                    action_url: $('[name=action_url]').val()
                };

                $('[name*="tx-"]:visible').each(function (index, object) {
                    var input = $(object);
                    var name = input.attr('name').substr(3).replace(/-/g, '_');
                    if (name == 'type')
                        return;
                    var value = 0;
                    if (input.is(':checkbox'))
                        value = input.prop('checked') ? 1 : 0;
                    else
                        value = $(object).val();
                    data[name] = value;
                });

                var tx_type = $('select#tx-type').val();
                if (tx_type != 'cash') {
                    // Get settings of portfolio
                    var portfolio = $('select#existing-portfolio option:selected').val();
                    if (portfolio == 'setup-portfolio' || portfolio == '') {
                        // Create portfolio and bank
                        alert('choose one portfolio please!');
                        return;
                    }

                    data['portfolio_id'] = portfolio;

                    // Get settings of security
                    var security = $('select#existing-security option:selected').val();
                    if (security == 'setup-security')
                        security = $('#security-symbol').val();

                    if (security == '') {
                        // Create security
                        alert('choose one security please!');
                        return;
                    }

                    data['security_id'] = security;
                }


                var bank_id = $('select#tx-bank-id option:selected').val();
                console.log('bank_id: ' + bank_id);
                if (bank_id != 'null')
                    data['bank_id'] = bank_id;

                $.ajax({
                    url: '/api/test/handle_transaction',
                    type: 'post',
                    data: data,
                    success: function (response) {
                        console.dir(response);
                        updateTxInfo(response);

                        // Reload transaction history
                        $('#existing-portfolio').change();
                    },
                    error: function (jqXhr, errorThrown, status) {
                        console.error('error: ' + errorThrown);
                        updateTxInfo(errorThrown);
                    }
                })
            }

            $(function () {
                $('#existing-portfolio').change(function () {

                    $('#tx-history').hide();

                    var v = $(this).val();
                    var show_portfolio_options = (v === "setup-portfolio");
                    if (show_portfolio_options) {
                        $('.portfolio-properties').show();
                        $('.tx-history').hide();
                    }
                    else {
                        $('.portfolio-properties').hide();
                        $('.security-properties').hide();


                        var spinner = $('<img>').attr('src', '/spinner-square.gif').css({width: '100%'});
                        $('#tx-history-info').text('').html('').find('*').remove().end().append(spinner);
                        $('.tx-history').show();

                        var portfolio_id = $(this).val();
                        $.ajax({
                            url: '/api/test/tx_history/' + portfolio_id,
                            type: 'get',
                            success: function(response) {
                                $('.tx-history').show();
                                $('#tx-history-info').text('').html('').find('*').remove().end().html(response).show();
                            }
                        })
                    }
                });
                $('#existing-security').change(function () {
                    var v = $(this).val();
                    var show_security_options = (v === "setup-security");
                    if (show_security_options) {
                        $('.security-properties').show();
                    }
                    else {
                        $('.portfolio-properties').hide();
                        $('.security-properties').hide();

                        var spinner = $('<img>').attr('src', '/spinner-square.gif').css({width: '100%'});
                        $('#security-result').text('').html('').find('*').remove().end().append(spinner).show();

                        var security = $(this).find('option:selected').text();
                        $.ajax({
                            url: 'test/security/find/' + security,
                            type: 'get',
                            success: function (response) {
                                $('#security-result').text('').html('').find('*').remove().end().html(response).show();
                            }
                        })
                    }
                });

                $('.tab').click(function () {
                    var page_id = $(this).data('tab');
                    $('.page').hide();
                    $('#' + page_id).show();
                });

                $('#opt-port-associate-bank').click(function () {
                    if ($(this).prop('checked'))
                        $('.bank-properties').show();
                    else
                        $('.bank-properties').hide();
                });

                $('select#tx-type').change(function () {
                    var value = $(this).val();
                    var fragment_id = 'tx-form-' + value;
                    var fragment = getHtmlScriptFragment(fragment_id);
                    $('#tx-form-container').html('');
                    $('#tx-form-container').append(fragment);
                    $("#type").val(value);
                });

                $('#btn-save').click(function () {
                    sendTransaction();
                });

            });

            function sendAjaxRequest(data, url) {
                console.log(url);
                $.ajax({
                    url: url,
                    type: 'post',
                    data: data,
                    success: function (data) {
                        console.log(data);
                        updateTxInfo(data);
                    },
                    error: function (data) {
                        console.log(data);
                        updateTxInfo(data);
                    }
                });
            }

            function updateTxInfo(data) {
                console.log("update Tx info ");
                $("#result-tx").html(data);
            }
        </script>
    </body>
</html>

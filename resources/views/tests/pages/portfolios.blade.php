<div id="page-1" class="page col-md-8">

    <section class="block">
        <h2>Select Portfolio</h2>
        <section class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <label for="existing-portfolio">Choose a Portfolio</label>
                <select name="existing-portfolio" id="existing-portfolio" class="form-control">
                    <option value="" selected>-- select an option --</option>
                    <optgroup label="Create a new Portfolio (Disabled for now)">
                        @if (0)
                            <option value="setup-portfolio">Setup Portfolio</option>
                        @endif
                    </optgroup>
                    <optgroup label="Use an existing Porfolio">
                        @foreach($portfolios as $portfolio)
                            <option value="{{$portfolio->id}}"
                                    class="existing-port"
                                    data-port-id="{{$portfolio->id}}">{{$portfolio->name}}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </section>
    </section>

    <section name="Setup-Portfolio">
        <section class="block portfolio-properties">
            <h2>Portfolio Properties</h2>
            <section class="row">
                {{-- PORTFOLIO NAME --}}
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <label for="opt-port-name">Name</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        </div>
                        <input type="text"
                               name="opt-port-name"
                               id="opt-port-name"
                               class="form-control"
                               placeholder="Enter the name of new portfolio"/>
                    </div>
                </div>
            </section>
            <br>
            <section class="row">
                {{-- PORTFOLIO: Associate currency --}}
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <label for="opt-port-currency">Choose Existing Currency</label>
                    <select name="opt-port-currency" id="opt-port-currency" class="form-control">
                        @foreach($currencies as $currency)
                            <option value="{{$currency->id}}">{{$currency->name}} ({{$currency->symbol}})</option>
                        @endforeach
                    </select>
                </div>

                {{-- PORTFOLIO OPTION: Date Format --}}
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <label for="opt-port-date-format">Date Format</label>
                    <select name="opt-port-date-format" id="opt-port-date-format" class="form-control">
                        <option value="dd-mm-yyy">dd-mm-yyy</option>
                        <option value="dd-mm-yyyy">dd-mm-yyyy</option>
                    </select>
                </div>

            </section>
            <br>
            <section class="row">

                {{-- PORTFOLIO OPTION: Comma Separator --}}
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="checkbox"><label for="opt-port-comma-separator">
                            <input id="opt-port-comma-separator" type="checkbox"/>
                            Comma Separator</label></div>
                </div>

                {{-- PORTFOLIO OPTION: Is Company --}}
                <div class="col-md-4 col-sm-4 col-xs-6">
                    <div class="checkbox"><label for="opt-port-is-company">
                            <input id="opt-port-is-company" type="checkbox"/>
                            Is Company</label></div>
                </div>


                {{-- OPTION: Associate Bank --}}
                <div class="col-md-4 col-sm-4 col-xs-6">
                    <div class="checkbox"><label for="opt-port-associate-bank">
                            <input id="opt-port-associate-bank" type="checkbox"/>
                            Associate a Bank</label></div>
                </div>
            </section>

        </section>

        <hr class="bank-properties">

        <section class="block bank-properties" hidden>
            <h2>Bank Properties</h2>
            <section class="row">
                {{-- BANK NAME --}}
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <label for="opt-bank-name">Name</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        </div>
                        <input type="text"
                               name="opt-bank-name"
                               id="opt-bank-name"
                               class="form-control"
                               placeholder="Enter the name of the new bank"/>
                    </div>
                </div>
            </section>
            <br>
            <section class="row">
                {{-- BANK: Associate currency --}}
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <label for="opt-bank-existing-currency">Choose Existing Currency</label>
                    <select name="opt-bank-existing-currency" id="opt-bank-existing-currency" class="form-control">
                        @foreach($currencies as $currency)
                            <option value="{{$currency->id}}">{{$currency->name}} ({{$currency->symbol}})</option>
                        @endforeach
                    </select>
                </div>

                {{-- BANK CASH AMOUNT --}}
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <label for="opt-bank-cash-amount">Cash Amount</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-usd" aria-hidden="true"></span>
                        </div>
                        <input type="number"
                               name="opt-bank-cash-amount"
                               id="opt-bank-cash-amount"
                               class="form-control"
                               placeholder="Enter cash amount of the bank (Only numbers)" value="0" step="0.01"/>
                    </div>
                </div>
            </section>
            <br>
            <section class="row">

                {{-- BANK OPTION: Is Enable Overdraft --}}
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="checkbox"><label for="opt-bank-enable-overdraft">
                            <input id="opt-bank-enable-overdraft" type="checkbox"/>
                            Enable Overdraft</label></div>
                </div>

            </section>
        </section>
        <br>

    </section>

    <section class="tx-history">
        <h2>Transaction History</h2>
        <section class="row">
            <div class="col-md-12" id="tx-history-info">
            </div>
        </section>
    </section>

</div>

<div id="page-2" class="page page-inactive col-md-8">
    <section class="block">
        <h2>Transactions</h2>
        <section class="row">
            <div class="col-md-12">
                <label for="tx-type">Type</label>
                <select name="tx-type" id="tx-type" class="form-control">
                    <option disabled selected value> -- select an option --</option>
                    <option value="sell" data-type="sell">Sell</option>
                    <option value="buy" data-type="buy">Buy</option>
                    <option value="cash" data-type="cash">Cash</option>
                    <option value="bookvalue" data-type="bookvalue">Book Value</option>
                    <option value="dividend" data-type="dividend">Dividend</option>
                </select>
            </div>
        </section>

        <br>


        <section id="tx-form-container">

        </section>
        <div class="row">
            <div class="col-md-12" style="text-align: center">
                <button type="button" class="btn btn-default" id="btn-save">Save</button>
            </div>
        </div>
    </section>
</div>

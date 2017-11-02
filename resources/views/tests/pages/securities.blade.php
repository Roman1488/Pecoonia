<div id="page-3" class="page page-inactive col-md-8">
    <section class="block">
        <h2>Select Security</h2>
        <section class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <label for="existing-security">Choose a Security</label>
                <select name="existing-security" id="existing-security" class="form-control">
                    <option value="" selected>-- select an option --</option>
                    <optgroup label="Find a Security by Symbol (Yahoo Finance)">
                        <option value="setup-security">Setup a Security by Symbol</option>
                    </optgroup>
                    <optgroup label="Use an existing Security">
                        @foreach($securities as $security)
                            <option value="{{$security->id}}"
                                    class="existing-security"
                                    data-security-id="{{$security->id}}">{{ strtoupper($security->symbol) }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </section>
    </section>
    <section name="setup-security">
        <section class="block security-properties" hidden>
            <label for="security-symbol">Find or Create a Security by Symbol</label>
            <input type="text" class="form-control" id="security-symbol" name="security-symbol"
                   placeholder="Enter Security symbol and press «Enter»">
            <script type="text/javascript">
                $(function () {
                    $('#security-symbol').keypress(function (event) {
                        if (event.which != 13)
                            return;

                        var security = $(this).val();
                        if (!security || security.length == 0) {
                            alert("Please input a security symbol");
                            return;
                        }

                        var spinner = $('<img>').attr('src', '/spinner-square.gif').css({width: '100%'});
                        $('#security-result').text('').html('').find('*').remove().end().append(spinner).show();

                        $.ajax({
                            url: 'test/security/find/' + security,
                            type: 'get',
                            success: function (response) {
                                $('#security-result').text('').html('').find('*').remove().end().html(response).show();
                                if (response.indexOf("not found") == -1) {
                                    if ($('#existing-security').find('option:contains(' + security.toUpperCase() + ')').length == 0) {
                                        var option = $("<option>").attr('value', security.toLowerCase()).text(security.toUpperCase());
                                        $('#existing-security optgroup:eq(1)').append(option);
                                    }
                                }
                            }
                        })
                    });
                });
            </script>
        </section>

        <section class="block security-result" id="security-result">
        </section>
    </section>

    <div class="preload">
        <img src="/spinner-square.gif" alt="spinner" />
    </div>
</div>

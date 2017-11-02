<div class="row">
    <div class="col-md-12">
        <h4>Found security with symbol {{ $symbol }}</h4>
        <em>This security will be used for your transactions</em>
        <br>
        <br>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Symbol</th>
                    <td>{{ strtoupper($item['symbol']) }}</td>
                </tr>
                <tr>
                    <th>Currency</th>
                    <td>{{ strtoupper($item['currency']['symbol']) }}</td>
                </tr>
                @if (count($item['data']))
                    @foreach($item['data'][0] as $key => $value)
                        <tr>
                            <th>{{ ucfirst($key) }}</th>
                            <td>{{ strtoupper($value) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

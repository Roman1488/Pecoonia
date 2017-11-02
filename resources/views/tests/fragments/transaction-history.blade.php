<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Currency</th>
            <th>Security</th>
            <th>Quantity</th>
            <th>Trade Value</th>
            <th>Book Value</th>
            <th>Dividend</th>
        </tr>
    </thead>
    <tbody>
        @if ($transactions->count())
            @foreach ($transactions as $tx)
                <tr>
                    <td>{{ date('Y-m-d', strtotime($tx->date)) }}</td>
                    @if ($tx->transaction_type != "cash")
                        <td>{{ $tx->transaction_type }}</td>
                    @else
                        <td>{{ $tx->transaction_type }} / {{ $tx->activity }}</td>
                    @endif
                    <td>
                        @if ($tx->security_id)
                            <?php $currency = $tx->security->currency; ?>
                        @elseif ($tx->portfolio_id)
                            <?php $currency = $tx->portfolio->currency; ?>
                        @endif
                        {{ $currency ? $currency->symbol : "N/A" }}
                    </td>
                    <td>
                        @if ($tx->security_id)
                            {{ strtoupper($tx->security->symbol) }}
                        @else
                            {{ 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $tx->quantity }}</td>
                    <td>{{ sprintf("%.2f", $tx->trade_value ?: 0) }}</td>
                    <td>{{ sprintf("%.2f", $tx->book_value ?: 0) }}</td>
                    <td>{{ sprintf("%.2f", $tx->dividend ?: 0) }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="8" style="font-size: 14px;;">
                    No transaction history yet
                </td>
            </tr>
        @endif
    </tbody>
</table>

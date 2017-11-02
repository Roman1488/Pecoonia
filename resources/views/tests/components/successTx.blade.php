<div class="modal fade" id="success-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Transaction Completed Successfully</h4>
            </div>
            <div class="modal-body">
                <table id="last-tx" border="0" class="table">
                    @foreach($item as $key => $value)
                        <tr>
                            <th>{{ str_replace("_", " ", ucfirst( preg_replace('/^c_/', '', $key))) }}</th>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function() {
            $('#success-modal').modal('show');
        });
    </script>
</div>

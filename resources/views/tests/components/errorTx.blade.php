<div class="modal fade" id="error-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Transaction Completed Successfully</h4>
            </div>
            <div class="modal-body">
                <h2>Error! </h2><p>{{$error}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $('#error-modal').modal('show');
        });
    </script>
</div>

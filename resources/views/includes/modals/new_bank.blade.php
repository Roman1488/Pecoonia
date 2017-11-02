<div class="modal fade newBank-modal-md" tabindex="-1" role="dialog" aria-labelledby="newBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal_ng_windows">
            <div>
                <a class="close" style="position:relative;top:30px;right:30px" ng-click="reset('bank')" aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
                <div class="modal-header text-center">
                    <h2 class="m_ng_header">CREATE NEW BANK</h2>
                </div>
                <div class="modal-body">
                    @include('includes.forms.bank')
                </div>
            </div>
        </div>
    </div>
</div>
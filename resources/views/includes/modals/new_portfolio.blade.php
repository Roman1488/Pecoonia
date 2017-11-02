<div class="modal fade newPortfolio-modal-md" tabindex="-1" role="dialog" aria-labelledby="newPortfolioModalLabel" aria-hidden="true" ng-controller="CreateController">
    <div class="modal-dialog modal-md">
        <div class="modal_ng_windows">
            <div>
                <a class="close" style="position:relative;top:30px;right:30px" ng-click="reset('portfolio')" aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
                <div class="modal-header">
                    <h2 class="m_ng_header">CREATE PORTFOLIO</h2>
                </div>
                <div class="modal-body">
                    @include('includes.forms.portfolio')
                </div>
            </div>
        </div>
    </div>
</div>
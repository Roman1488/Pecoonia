<div class="col_full">
    <div class="col_full">
        <button type="button" ui-sref="panel.bank" class="btn btn-default btn-lg btn-block">CREATE NEW BANK</button>
    </div>
    <div class="col_full">
        <div class="fancy-title title-dotted-border">
            <h3>Banks</h3>
        </div>
    </div>
    <div class="col_one_fourth" ng-repeat="bank in userBanks" ng-class="{ 'col_last' : ($index + 1) % 4 == 0 }">
        <button ui-sref="panel.show.bank({id: bank.id})" type="button" class="btn btn-default btn-lg btn-block btn-create">@{{ bank.name }}</button>
    </div>
</div>

<?php
namespace App\Pecoonia\Calculator\Type;

class DailyReturnPurchaseValue extends Base
{
    
    public function calculateLocal()
    {
        $AverageDaysOwned = $this->calculator()->AverageDaysOwned->getValue();
        if (!$AverageDaysOwned)
            $AverageDaysOwned = 1;
        
        $PurchaseValueLocal = $this->calculator()->PurchaseValue->getLocal();
        if (!$PurchaseValueLocal)
            $PurchaseValueLocal = 1;
        
        $this->setLocal((($this->calculator()->ProfitLossPurchaseValue->getLocal() / $AverageDaysOwned) / $PurchaseValueLocal) * 100);
    }
    
    public function calculateBase()
    {
        $AverageDaysOwned = $this->calculator()->AverageDaysOwned->getValue();
        if (!$AverageDaysOwned)
            $AverageDaysOwned = 1;
        
        $PurchaseValueBase = $this->calculator()->PurchaseValue->getBase();
        if (!$PurchaseValueBase)
            $PurchaseValueBase = 1;
        
        $this->setBase((($this->calculator()->ProfitLossPurchaseValue->getBase() / $AverageDaysOwned) / $PurchaseValueBase) * 100);
    }
    
}

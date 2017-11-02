<?php
namespace App\Pecoonia\Calculator\Type;

class DailyProfitLossPurchaseValue extends Base
{

    public function calculateLocal()
    {
        $AverageDaysOwnedValue = $this->calculator()->AverageDaysOwned->getValue();
        if ($AverageDaysOwnedValue == 0)
            $this->setLocal($this->calculator()->ProfitLossPurchaseValue->getLocal() / 1);
        else
            $this->setLocal($this->calculator()->ProfitLossPurchaseValue->getLocal() / $AverageDaysOwnedValue);
    }

    public function calculateBase()
    {
        $AverageDaysOwnedValue = $this->calculator()->AverageDaysOwned->getValue();
        if ($AverageDaysOwnedValue == 0)
            $this->setBase($this->calculator()->ProfitLossPurchaseValue->getBase() / 1);
        else
            $this->setBase($this->calculator()->ProfitLossPurchaseValue->getBase() / $AverageDaysOwnedValue);
    }
}

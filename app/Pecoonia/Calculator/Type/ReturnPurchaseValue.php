<?php
	namespace App\Pecoonia\Calculator\Type;
	
	class ReturnPurchaseValue extends Base {
		
		public function calculateLocal()
		{
            $purchaseValueLocal = $this->calculator()->PurchaseValue->getLocal();
            $tradeValueLocal = $this->calculator()->TradeValue->getLocal();
            if ($purchaseValueLocal == 0) {
                $this->setLocal(0);
                return;
            }
            $this->setLocal( ( ( $tradeValueLocal - $purchaseValueLocal) / $purchaseValueLocal) * 100 );
		}

		public function calculateBase()
		{
            $purchaseValueBase = $this->calculator()->PurchaseValue->getBase();
            $tradeValueBase = $this->calculator()->TradeValue->getBase();
            if ($purchaseValueBase == 0) {
                $this->setBase(0);
                return;
            }
            $this->setBase( ( ( $tradeValueBase - $purchaseValueBase) / $purchaseValueBase) * 100 );
		}

	}

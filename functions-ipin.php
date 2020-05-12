<?php 
	function doTranReversal($payment,$xml,$db)
	{
		$ProductGroupCode  = $payment->ProductGroupCode;
		$PaymentLogId  = $payment->PaymentLogId;
		$OriginalPaymentReference  = $payment->OriginalPaymentReference;
		$OriginalPaymentLogId  = $payment->OriginalPaymentLogId;
		$CustReference  = $payment->CustReference;
		$AlternateCustReference  = $payment->AlternateCustReference;
		$Amount  = $payment->Amount;
		$PaymentStatus  = $payment->PaymentStatus;
		$PaymentMethod  = $payment->PaymentMethod;
		$PaymentReference = $payment->PaymentReference;
		$TerminalId = $payment->TerminalId;
		$ChannelName = $payment->ChannelName;
		$Location = $payment->Location;
		$IsReversal  = $payment->IsReversal;
		$PaymentDate  = $payment->PaymentDate;
		$SettlementDate  = $payment->SettlementDate;
		$InstitutionId  = $payment->InstitutionId;
		$InstitutionName = $payment->InstitutionName;
		$BranchName = $payment->BranchName;
		$BankName = $payment->BankName;
		$FeeName = $payment->FeeName;
		$CustomerName = $payment->CustomerName;
		$OtherCustomerInfo = $payment->OtherCustomerInfo;
		$ReceiptNo = $payment->ReceiptNo;
		$CollectionsAccount = $payment->CollectionsAccount;
		$ThirdPartyCode = $payment->ThirdPartyCode;
		$BankCode = $payment->BankCode;
		$CustomerAddress = $payment->CustomerAddress;
		$CustomerPhoneNumber = $payment->CustomerPhoneNumber;
		$DepositorName = $payment->DepositorName;
		$DepositSlipNumber = $payment->DepositSlipNumber;
		$PaymentCurrency = $payment->PaymentCurrency;
		
		$responseText = ""; 
		
		if(($IsReversal == "True" or $IsReversal == "true") and intval($Amount)<=0)
		{
			$arr = array($PaymentLogId."*"."0");
			$responseText = response($arr,$xml); 
			$Amount = "-".abs(intval($Amount));
			
			//THIS IS THE PART WHERE YOU PROCESS YOUR REVERSAL PAYMENT. DONT KNOW HOW YOU GUYS WANT TO DO YOUR OWN, BUT THIS APP SAVES REVERSALS IN A DIFFERENT TABLE.
			//
			debugPay($ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference ,$Amount ,$PaymentStatus ,$PaymentMethod ,$PaymentReference, $TerminalId, $ChannelName, 
			$Location, $IsReversal ,$PaymentDate ,$SettlementDate ,$InstitutionId , $InstitutionName,$BranchName,$BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,
			$CollectionsAccount, $ThirdPartyCode,$BankCode,$CustomerAddress,$CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,$PaymentCurrency,"Reversal of Payment",$payment,
			$OriginalPaymentLogId, $OriginalPaymentReference,$db); 
		}
		else
		{
			$arr = array($PaymentLogId."*"."1");
			$responseText = response($arr,$xml);	 
		}
		
		return $responseText;
	}
	
	
	
	
	function doTranNotification($payment,$xml,$db)
	{
		
		$ProductGroupCode  = $payment->ProductGroupCode;
		$PaymentLogId  = $payment->PaymentLogId;
		$OriginalPaymentReference  = $payment->OriginalPaymentReference;
		$OriginalPaymentLogId  = $payment->OriginalPaymentLogId;
		$CustReference  = $payment->CustReference;
		$AlternateCustReference  = $payment->AlternateCustReference;
		$Amount  = $payment->Amount;
		$PaymentStatus  = $payment->PaymentStatus;
		$PaymentMethod  = $payment->PaymentMethod;
		$PaymentReference = $payment->PaymentReference;
		$TerminalId = $payment->TerminalId;
		$ChannelName = $payment->ChannelName;
		$Location = $payment->Location;
		$IsReversal  = $payment->IsReversal;
		$PaymentDate  = $payment->PaymentDate;
		$SettlementDate  = $payment->SettlementDate;
		$InstitutionId  = $payment->InstitutionId;
		$InstitutionName = $payment->InstitutionName;
		$BranchName = $payment->BranchName;
		$BankName = $payment->BankName;
		$FeeName = $payment->FeeName;
		$CustomerName = $payment->CustomerName;
		$OtherCustomerInfo = $payment->OtherCustomerInfo;
		$ReceiptNo = $payment->ReceiptNo;
		$CollectionsAccount = $payment->CollectionsAccount;
		$ThirdPartyCode = $payment->ThirdPartyCode;
		$BankCode = $payment->BankCode;
		$CustomerAddress = $payment->CustomerAddress;
		$CustomerPhoneNumber = $payment->CustomerPhoneNumber;
		$DepositorName = $payment->DepositorName;
		$DepositSlipNumber = $payment->DepositSlipNumber;
		$PaymentCurrency = $payment->PaymentCurrency;
		
		//$db = new Database();
		
		$responseText  = "";
		
			
		if(recordExists($CustReference,$db))
		{				
			if(!transactionExists($CustReference,$PaymentLogId,$PaymentDate,$Amount,$db))
			{
				
				$rowNum  = processPayments( $ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference ,$Amount ,$PaymentStatus ,$PaymentMethod , $PaymentReference, $TerminalId, $ChannelName, $Location, $IsReversal ,$PaymentDate ,$SettlementDate ,$InstitutionId , $InstitutionName,$BranchName,$BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,$CollectionsAccount, $ThirdPartyCode,$BankCode,$CustomerAddress,$CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,$PaymentCurrency,$OriginalPaymentLogId,$OriginalPaymentReference,$db
							);	 
				if( $rowNum >= 1)
				{	
					$payNum = 0 ; 
					$items = $payment->PaymentItems; 
					foreach($items->PaymentItem as $paymentItem)
					{
						$ItemName = $paymentItem->ItemName;
						$ItemCode = $paymentItem->ItemCode;
						$ItemAmount = $paymentItem->ItemAmount;
						$LeadBankCode = $paymentItem->LeadBankCode;
						$LeadBankCbnCode = $paymentItem->LeadBankCbnCode;
						$LeadBankName = $paymentItem->LeadBankName;
						
						$payNum += processItems($PaymentLogId, $ItemName , $ItemCode, $ItemAmount, $LeadBankCode, $LeadBankCbnCode, $LeadBankName,$PaymentDate,$db);
					}		
					
					if($payNum >= 1)
					{
						$arr = array($PaymentLogId."*"."0");
						$responseText = response($arr,$xml);										
					}
					else
					{
						$arr = array($PaymentLogId."*"."1");
						$responseText = response($arr,$xml);	
					}
				}
				else
				{
					$arr = array($PaymentLogId."*"."1");
					$responseText = response($arr,$xml);						
				}	
			}
			else
			{
				//IF PAYMENT ALREADY EXISTS THEN JUST SEND OK CODE TO INTERSWITCH, TO TELL THEM NOT TO RESEND THE TRANSACTION AGAIN
				$arr = array($PaymentLogId."*"."0");
				$responseText = response($arr,$xml);  
			}
		}//end of if check for Customer Existence check
		else
		{
			//IF CUSTOMER VALIDATION FAILS, THEN LOG IN A DIFFERENT TABLE
			$arr = array($PaymentLogId."*"."0");
			$responseText = response($arr,$xml);	
			debugPay($ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference ,$Amount ,$PaymentStatus ,$PaymentMethod ,$PaymentReference, $TerminalId, $ChannelName, $Location, $IsReversal ,$PaymentDate ,$SettlementDate ,$InstitutionId , $InstitutionName,$BranchName,$BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,$CollectionsAccount, $ThirdPartyCode,$BankCode,$CustomerAddress,$CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,$PaymentCurrency,"Invalid I-PIN number",$payment,$OriginalPaymentLogId,$OriginalPaymentReference,$db);
				
		}
		
		return $responseText ;
		
	}
	
	
	function processCustomerInfoXML($result,$xml,$CustReference,$db)
	{
		$responseText = "";
		if(recordExists($CustReference,$db))
		{	 						
			$sql = "select customer_id,alternate_customerid,NAME_COMPANY,EMAIL_ADDRESS,PHONE_NUMBER from CUSTOMERS where customer_id = ? ";					

			$result = $db->retrieve_prepare($sql , array("$CustReference"));
			if(!$result)
			{
				throw new Exception("Error !! All Result set came back with errors: ".
										$db->formatError(sqlsrv_errors()));	
			}
			
			if(sqlsrv_has_rows($result))
			{ 
				$responseText = responseCust($result,$xml,$CustReference);
			}
			else
			{
				$responseText = responseCust($result,$xml,$CustReference);
			}
			
		}//end of if check for Customers Existence check
		else
		{
			//echo "does not exist";
			$responseText = responseCust($result,$xml,$CustReference);									
		}	
		
		return $responseText;
	}
	
	
	function transactionExists($CustReference,$PaymentLogId,$PaymentDate,$Amount,$db)
	{
			
			$sql = "select * from PAYMENT_TRANSACTION  where 
			CustReference = ? and PaymentLogId = ? 
			and PaymentDate = ? ";//and Amount='$Amount'";
			
			$result = $db->retrieve_prepare($sql , array("$CustReference" , "$PaymentLogId" , "$PaymentDate"));
			if(!$result)
			{
				throw new Exception("Error !! Result set came back with errors: ".
											$db->formatError(sqlsrv_errors()));
			}
			if(sqlsrv_has_rows($result))
			{
			  return true;
			}
			else
			{
			  return false;
			}
	}
	
	
	
	function recordExists($CustReference,$db)
	{
	  $sql = "select customer_id from CUSTOMERS where customer_id = ?";
	  
	  $result = $db->retrieve_prepare($sql , array("$CustReference"));
	  if(!$result)
	  {
			throw new Exception("Error !! Result set came back with errors: ". $db->formatError(sqlsrv_errors()));
	  }
	  if(sqlsrv_has_rows($result))
	  {
		  return true;
	  }
	  else
	  {
		  return false;
	  }
	}
	 
	 
	
	
	function processPayments($ProductGroupCode ,$PaymentLogId ,$CustReference ,
							$AlternateCustReference ,$Amount ,$PaymentStatus ,$PaymentMethod ,
							$PaymentReference, $TerminalId, $ChannelName, $Location, $IsReversal ,
							$PaymentDate ,$SettlementDate ,$InstitutionId ,$InstitutionName, 
							$BranchName, $BankName, $FeeName, $CustomerName,$OtherCustomerInfo,
							$ReceiptNo,$CollectionsAccount,$ThirdPartyCode,$BankCode,
							 $CustomerAddress, $CustomerPhoneNumber,$DepositorName,
							 $DepositSlipNumber, $PaymentCurrency,$OriginalPaymentLogId,
							 $OriginalPaymentReference,$db
	)
	{
			 
			$sql = "insert into PAYMENT_TRANSACTION
					(ProductGroupCode
					   ,PaymentLogId
					   ,CustReference
					   ,AlternateCustReference
					   ,Amount
					   ,PaymentStatus
					   ,PaymentMethod
					   ,PaymentReference
					   ,TerminalId
					   ,ChannelName
					   ,Location
					   ,IsReversal
					   ,PaymentDate
					   ,SettlementDate
					   ,InstitutionId
					   ,InstitutionName
					   ,BranchName
					   ,BankName
					   ,FeeName
					   ,CustomerName
					   ,OtherCustomerInfo
					   ,ReceiptNo
					   ,CollectionsAccount
					   ,ThirdPartyCode
					   ,BankCode
					   ,CustomerAddress
					   ,CustomerPhoneNumber
					   ,DepositorName
					   ,DepositSlipNumber
					   ,PaymentCurrency
					   ,SYNCED_TO_NET
					   ,SYNCED_TO_LOCAL
					   ,OriginalPaymentLogId
					   ,OriginalPaymentReference) 
					   values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			
			$inserted = $db->insert_prepare($sql , array("$ProductGroupCode", "$PaymentLogId", "$CustReference", "$AlternateCustReference", "$Amount", "$PaymentStatus", "$PaymentMethod", "$PaymentReference", "$TerminalId", "$ChannelName", "$Location", "$IsReversal", "$PaymentDate", "$SettlementDate", "$InstitutionId", "$InstitutionName", "$BranchName", "$BankName", "$FeeName", "$CustomerName", "$OtherCustomerInfo", "$ReceiptNo", "$CollectionsAccount", "$ThirdPartyCode", "$BankCode", "$CustomerAddress", "$CustomerPhoneNumber", "$DepositorName", "$DepositSlipNumber", "$PaymentCurrency", "NO", "YES","$OriginalPaymentLogId","$OriginalPaymentReference"));
			 
			return $inserted;
	}
	
	function processItems($PaymentLogId, $ItemName, $ItemCode, $ItemAmount, $LeadBankCode, $LeadBankCbnCode, $LeadBankName,$PaymentDate,$db)
	{
			 
			$sql = "insert into Payment_Items
									(
									PaymentLogId, 
									ItemName, 
									ItemCode, 
									ItemAmount, 
									LeadBankCode, 
									LeadBankCbnCode, 
									LeadBankName,
									SYNCED_TO_NET,
									SYNCED_TO_LOCAL,
									PaymentDate) 
							values (?,?,?,?,?,?,?,?,?,?)";
			
			$inserted = $db->insert_prepare($sql, array("$PaymentLogId", "$ItemName", "$ItemCode", "$ItemAmount", "$LeadBankCode", "$LeadBankCbnCode", "$LeadBankName", "NO", "YES","$PaymentDate"));
			 
			return $inserted;
	}
	
	
	
	function processItemsFailure($PaymentLogId, $ItemName, $ItemCode, $ItemAmount, $LeadBankCode, $LeadBankCbnCode, $LeadBankName,$PaymentDate,$db)
	{
			 
			$sql = "insert into Payment_Items_Failure
											(
											PaymentLogId, 
											ItemName, 
											ItemCode, 
											ItemAmount, 
											LeadBankCode, 
											LeadBankCbnCode, 
											LeadBankName,
											SYNCED_TO_NET,
											SYNCED_TO_LOCAL,
											PaymentDate)  
								values (?,?,?,?,?,?,?,?,?,?)";
			
			$inserted = $db->insert_prepare($sql, array("$PaymentLogId", "$ItemName", "$ItemCode", "$ItemAmount", "$LeadBankCode", "$LeadBankCbnCode", "$LeadBankName", "NO", "YES","$PaymentDate"));
			 
			return $inserted;
	}
	
	function processItemsLog($PaymentLogId, $ItemName, $ItemCode, $ItemAmount, $LeadBankCode, $LeadBankCbnCode, $LeadBankName,$PaymentDate,$db)
	{ 
			
			$sql = "insert into Payment_Items_Log
										(
										PaymentLogId, 
										ItemName, 
										ItemCode, 
										ItemAmount, 
										LeadBankCode, 
										LeadBankCbnCode, 
										LeadBankName,
										SYNCED_TO_NET,
										SYNCED_TO_LOCAL,
										PaymentDate)   
								values (?,?,?,?,?,?,?,?,?,?)";
			
			$inserted = $db->insert_prepare($sql, array("$PaymentLogId", "$ItemName", "$ItemCode", "$ItemAmount", "$LeadBankCode", "$LeadBankCbnCode", "$LeadBankName", "NO", "YES","$PaymentDate"));
			 
			return $inserted;
	}
	
	function response($arr,$xml)
	{ 
		$payment = $xml->createElement("Payment");
		
		foreach($arr as $key => $value)
		{ 
			$values = explode("*",$value); 
			$paylog = $xml->createElement("PaymentLogId",$values[0]);
			$status = $xml->createElement("Status",$values[1]);
			$payment->appendChild($paylog);
			$payment->appendChild($status); 
		} 
		return $payment;
	}
	
?>
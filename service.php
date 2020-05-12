<?php

	function getXMLData(DomDocument $dom)
	{
		foreach($dom->childNodes as $node)
		{
			$result .= $dom->saveXML($node)."\n";
		}
		return $result;
	}

	@session_start();
	global $MerchantReference ;
	global $keyValid;
	global $postData;
	$MerchantReference = "";
	$postData = getRequest();
	@session_write_close();
	//echo $postData;
	header("Content-Type: text/xml; charset=utf-8");
	
	
	require('db/Database.php');
	include('functions-ipin.php');
	
	if(!verifyKey())
	{		
		echo "";
		exit;
	}
	else
	{
		$keyValid = true;	
	}
	
	if(isset($postData) and $postData !="")
	{
		$xml = simplexml_load_string($postData);
		
		//echo $postData;
		if($xml->Payments)
		{
			//echo "in payments";
			$xml2 = new DomDocument('1.0', 'utf-8');
			$xml2->xmlStandalone = false;
			$xml2->formatOutput = true;
			$xml2->encoding = 'utf-8';
			$paynode = $xml2->createElement("PaymentNotificationResponse");
			$payments = $xml2->createElement("Payments");
			foreach($xml->Payments->Payment as $payment)
			{
				$pay = processPaymentNotification($payment,$xml2);
				$payments->appendChild($pay);
			}
			$paynode->appendChild($payments);
			$xml2->appendChild($paynode);
			header("Content-Length: ".strlen(trim($xml2->saveXML())));
			echo trim($xml2->saveXML()); 
			
		}
		else if($xml->CustReference)
		{
			$xml2 = new DomDocument('1.0', 'utf-8'); 
			$xml2->encoding = 'utf-8';
			$cust = processCustomerInfo($xml,$xml2);
			$CIR = $xml2->createElement("CustomerInformationResponse");
			$merch = $xml2->createElement("MerchantReference",$MerchantReference);
			$customers = $xml2->createElement("Customers");
						
				 
			$customers->appendChild($cust);
			
			$CIR->appendChild($merch);
			$CIR->appendChild($customers);
			$xml2->appendChild($CIR);
			header("Content-Length: ".strlen(trim($xml2->saveXML())));
			echo trim($xml2->saveXML()); 
		}
		else
		{
			echo "0";
		}
	}
	
	

	function processPaymentNotification($payment,$xml)
	{
		global $keyValid;
		//$keyValid = true;
		/*if($payment == null)
		{
			return 1;
		}*/
		
		global $postData;
		
		$ProductGroupCode  = "";
		$PaymentLogId  = "";
		$OriginalPaymentReference  = "";
		$OriginalPaymentLogId  = "";
		$CustReference  = "";
		$AlternateCustReference  = "";
		$Amount  = "";
		$PaymentStatus  = "";
		$PaymentMethod  = "";
		$PaymentReference = "";
		$TerminalId = "";
		$ChannelName = "";
		$Location = "";
		$IsReversal  = "";
		$PaymentDate  = "";
		$SettlementDate  = "";
		$InstitutionId  = "";
		$InstitutionName = "";
		$BranchName = "";
		$BankName = "";
		$FeeName = "";
		$CustomerName = "";
		$OtherCustomerInfo = "";
		$ReceiptNo = "";
		$CollectionsAccount = "";
		$ThirdPartyCode = "";
		$BankCode = "";
		$CustomerAddress = "";
		$CustomerPhoneNumber = "";
		$DepositorName = "";
		$DepositSlipNumber = "";
		$PaymentCurrency = "";
		$key = "";
		$responseText = "";
		
		try
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
			
			$db = new Database();
			
				if($db->connectDB())
				{		
					//	LOG THE PAYMENT NOTIFICATION BEFORE PROCESSING IT.
					logPay($ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference ,$Amount ,$PaymentStatus ,$PaymentMethod ,								   					$PaymentReference, $TerminalId, $ChannelName, $Location, $IsReversal ,$PaymentDate ,$SettlementDate ,$InstitutionId ,   							 $InstitutionName,$BranchName,$BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,$CollectionsAccount,       						 $ThirdPartyCode,$BankCode,$CustomerAddress,$CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,$PaymentCurrency,$postData,$payment,$OriginalPaymentLogId,$OriginalPaymentReference,$db);
					
					// MAKE SURE THE REQUEST IS COMING FROM THE INTERSWITCH SERVER ONLY	
					if($keyValid)
					{	 
						//MAKE SURE TRANSACTION IS VALID BEFORE YOU PROCESS
						if($CustReference != "" and $PaymentLogId != "" and $PaymentMethod != "" and $ChannelName != "" and 
							($Amount != "" and intval($Amount) >0) and $PaymentDate != "" and $BankName!= "")
						{	 
							$responseText = doTranNotification($payment,$xml,$db); 
						}//end of if check for transaction ready status
						else
						{ 
							//process REVERSAL
							$responseText = doTranReversal($payment,$xml,$db); 
						}
					}
					else
					{
						//echo "Invalid Exchange Key";
						$arr = array($PaymentLogId."*"."1");
						$responseText = response($arr,$xml); 
					}
				}
				else
				{
					//could not connect to database server
					$arr = array($PaymentLogId."*"."1");
					//echo "DB not connected";
					$responseText = response($arr,$xml);	
					throw new Exception("Error !! Unable to connect to database. Reason: ".$db->formatError(sqlsrv_errors()));	
				}
				$db->closeConnection();
			
		}
		catch(Exception $ed)
		{
			//IF ANY ERRORS OCCUR WHILE PROCESSING JUST LOG IN A DIFFERENT TABLE AND SEND THE RIGHT CODE TO INTERSWITCH
			$arr = array($PaymentLogId."*"."1");
			$responseText = response($arr,$xml);				
			debugPay($ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference ,$Amount ,$PaymentStatus ,$PaymentMethod ,								   					$PaymentReference, $TerminalId, $ChannelName, $Location, $IsReversal ,$PaymentDate ,$SettlementDate ,$InstitutionId ,   							 $InstitutionName,$BranchName,$BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,$CollectionsAccount,       						 $ThirdPartyCode,$BankCode,$CustomerAddress,$CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,$PaymentCurrency,$ed->getMessage(),$payment,$OriginalPaymentLogId,$OriginalPaymentReference,$db);			
		}
		
		//RESPONSE TEXT IS THE RESULTANT XML FROM THIS TRANSACTION
		return $responseText;	
		
	}
	
	function setMerchRef($ref)
	{
		global $MerchantReference;
		$MerchantReference = $ref;
		
	}
	
	function processCustomerInfo($custInfo,$xml)
	{ 
		
		global $keyValid; 
		
		$MerchantReference  = "";
		$CustReference  = "";
		$PaymentItemCode  = "";
		$ServiceUsername  = "";
		$ServicePassword  = "";
		$FtpUsername  = "";
		$FtpPassword  = "";
		$key = "";
		$responseText = "";
		$result = "";
		$MerchantReference  = $custInfo->MerchantReference;
		$CustReference  = $custInfo->CustReference;
		$PaymentItemCode  = $custInfo->PaymentItemCode;
		$ServiceUsername  = $custInfo->ServiceUsername;
		$ServicePassword  = $custInfo->ServicePassword;
		$FtpUsername  = $custInfo->FtpUsername;
		$FtpPassword  = $custInfo->FtpPassword;
		setMerchRef($MerchantReference);
		try
		{
				
				$db = new Database();
				if($db->connectDB())
				{
				
					if($keyValid) 
					{  
		
						if($MerchantReference != "" and $CustReference != "" )
						{	
						
							if(getID() == $MerchantReference)
							{  
								//process CUSTOMER VALIDATION
								$responseText = processCustomerInfoXML($result,$xml,$CustReference,$db);  
							}
							else
							{
								// "merchant id is invalid";
								$responseText = responseCust($result,$xml,$CustReference);	
							}
							
							//do the rest here		
						}//end of if check for transaction ready status
						else
						{
							// "invalid transaction";
							$responseText = responseCust($result,$xml,$CustReference);							
						}
					}
					else
					{
						//"Invalid Exchange Key";
						$responseText = responseCust($result,$xml,$CustReference);							
					}
				}
				else
				{
					//"No Connection";
					$responseText = responseCust($result,$xml,$CustReference);	
					throw new Exception("Error !! Unable to connect to database. 
					Reason: ".$db->formatError(sqlsrv_errors()));	
				}
				$db->closeConnection();			
		}
		catch(Exception $ed)
		{
			//IF ANY ERRORS OCCUR WHILE PROCESSING JUST SEND THE RIGHT CODE TO INTERSWITCH, THAT CUSTOMER DOESNT EXIST
			$responseText = responseCust($result,$xml,$CustReference);						
		}		
		return $responseText;	
	}

	
	
	
	
	
	
	function responseCust($result,$xml,$CustReference)
	{
		$response = new DomDocument();
		
		//BUILD THE XML FROM VALUES GOTTEN FROM THE DATABASE
		if(@sqlsrv_has_rows($result))
		{
					
			$customer = $xml->createElement("Customer");
			
			sqlsrv_fetch($result);
			
			$CustReference = trim(sqlsrv_get_field($result,0));
			$CustReference = (strlen($CustReference) < 2)? "" : $CustReference ;
			
			$CustomerReferenceAlternate = trim(sqlsrv_get_field($result,1)) ;
			$CustomerReferenceAlternate = (strlen($CustomerReferenceAlternate) < 2)? "" : $CustomerReferenceAlternate ;
			
			$FirstName = trim(sqlsrv_get_field($result,2)) ;
			$FirstName = (strlen($FirstName) < 2)? "" : $FirstName ;
			
			$Email = trim(sqlsrv_get_field($result,3)) ;
			$Email = (strlen($Email) < 2)? "" : $Email ;
			
			$Phone = trim(sqlsrv_get_field($result,4)) ;
			$Phone = (strlen($Phone) < 2)? "" : $Phone ; 
			
			$status = $xml->createElement("Status",0);
			$cref = $xml->createElement("CustReference",$CustReference);
			$crefAlt = $xml->createElement("CustomerReferenceAlternate", $CustomerReferenceAlternate);
			$crEfDesc = $xml->createElement("CustomerReferenceDescription","Your Reference Description");
			$first = $xml->createElement("FirstName", $FirstName);
			$last = $xml->createElement("LastName","");
			$other = $xml->createElement("OtherName","");
			$email = $xml->createElement("Email", $Email);
			$phone = $xml->createElement("Phone", $Phone);
			$third = $xml->createElement("ThirdPartyCode","");
			$amt = $xml->createElement("Amount",0);
			
			$customer->appendChild($status);
			$customer->appendChild($cref);
			$customer->appendChild($crefAlt);
			$customer->appendChild($crEfDesc);
			$customer->appendChild($first);
			$customer->appendChild($last);
			$customer->appendChild($other);
			$customer->appendChild($email);
			$customer->appendChild($phone);
			$customer->appendChild($third);
			$customer->appendChild($amt);
			$response =  $customer;
		}
		else
		{
			$customer = $xml->createElement("Customer"); 
			
			$status = $xml->createElement("Status",1);
			$cref = $xml->createElement("CustReference",$CustReference);
			$crefAlt = $xml->createElement("CustomerReferenceAlternate","");
			$crEfDesc = $xml->createElement("CustomerReferenceDescription","Your Reference Description");
			$first = $xml->createElement("FirstName","");
			$last = $xml->createElement("LastName","");
			$other = $xml->createElement("OtherName","");
			$email = $xml->createElement("Email","");
			$phone = $xml->createElement("Phone","");
			$third = $xml->createElement("ThirdPartyCode","");
			$amt = $xml->createElement("Amount",0);
			
			$customer->appendChild($status);
			$customer->appendChild($cref);
			$customer->appendChild($crefAlt);
			$customer->appendChild($crEfDesc);
			$customer->appendChild($first);
			$customer->appendChild($last);
			$customer->appendChild($other);
			$customer->appendChild($email);
			$customer->appendChild($phone);
			$customer->appendChild($third);
			$customer->appendChild($amt);
			$response =  $customer;
		}
		
		return $response;
			
	}
	
	
	function debugPay($ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference 
							,$Amount ,$PaymentStatus ,$PaymentMethod ,$PaymentReference, 
							$TerminalId, $ChannelName, $Location, $IsReversal ,$PaymentDate ,
							$SettlementDate ,$InstitutionId ,$InstitutionName, $BranchName,
							 $BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,
							 $CollectionsAccount,$ThirdPartyCode,$BankCode,$CustomerAddress,
							 $CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,
							 $PaymentCurrency,$reason,$payment,$OriginalPaymentLogId,
							 $OriginalPaymentReference,$db)
	{
		 
		$reason = (strlen($reason) >= 100)? substr($reason,100,99) : $reason;
			$done = false;
			$sql = "INSERT INTO PAYMENT_TRANSACTION_FAILURE
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
						   ,REASON
						   ,TREATED
						   ,SYNCED_TO_NET
						   ,SYNCED_TO_LOCAL
						   ,OriginalPaymentLogId
					   	   ,OriginalPaymentReference)  
						   values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
						   
						   //echo $sql;
			
			if(!transExistsNoID($CustReference,$PaymentLogId,$PaymentDate,$Amount,$db))
			{
				$inserted = $db->insert_prepare($sql, array("$ProductGroupCode", "$PaymentLogId", "$CustReference", "$AlternateCustReference", "$Amount", "$PaymentStatus", "$PaymentMethod", "$PaymentReference", "$TerminalId", "$ChannelName", "$Location", "$IsReversal", "$PaymentDate", "$SettlementDate", "$InstitutionId", "$InstitutionName", "$BranchName", "$BankName", "$FeeName", "$CustomerName", "$OtherCustomerInfo", "$ReceiptNo", "$CollectionsAccount", "$ThirdPartyCode", "$BankCode", "$CustomerAddress", "$CustomerPhoneNumber", "$DepositorName", "$DepositSlipNumber", "$PaymentCurrency","$reason","NO", "NO","YES","$OriginalPaymentLogId","$OriginalPaymentReference"));
				 
				if($inserted >= 1)
				{				
					$items = $payment->PaymentItems;
					//echo "in payment $items";
					$payNum = 0;
					foreach($items->PaymentItem as $paymentItem)
					{
						$ItemName = $paymentItem->ItemName;
						$ItemCode = $paymentItem->ItemCode;
						$ItemAmount = $paymentItem->ItemAmount;
						$LeadBankCode = $paymentItem->LeadBankCode;
						$LeadBankCbnCode = $paymentItem->LeadBankCbnCode;
						$LeadBankName = $paymentItem->LeadBankName;
						
						$payNum += processItemsFailure($PaymentLogId, $ItemName , $ItemCode, $Amount, $LeadBankCode, $LeadBankCbnCode, $LeadBankName,$PaymentDate,$db);
					}
					
					if($payNum >= 1)
					{
						$done =  true;
					}
					else
					{
						$done =  false;
					}			  	
				}
				else
				{
				  $done =  false;
				}
				
			}
			else
			{
				$done = true;	
			}
			
			return $done;
			
	}
	
	function logPay($ProductGroupCode ,$PaymentLogId ,$CustReference ,$AlternateCustReference 
							,$Amount ,$PaymentStatus ,$PaymentMethod ,$PaymentReference, 
							$TerminalId, $ChannelName, $Location, $IsReversal ,$PaymentDate ,
							$SettlementDate ,$InstitutionId ,$InstitutionName, $BranchName,
							 $BankName,$FeeName,$CustomerName,$OtherCustomerInfo,$ReceiptNo,
							 $CollectionsAccount,$ThirdPartyCode,$BankCode,$CustomerAddress,
							 $CustomerPhoneNumber,$DepositorName,$DepositSlipNumber,
							 $PaymentCurrency,$reason,$payment,$OriginalPaymentLogId,
							 $OriginalPaymentReference,$db)
	{
		try
		{ 
			$done = false;
			$sql = "INSERT INTO PAYMENT_TRANSACTION_LOG
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
						   ,REASON
						   ,TREATED
						   ,SYNCED_TO_NET
						   ,SYNCED_TO_LOCAL
						   ,OriginalPaymentLogId
					   	   ,OriginalPaymentReference)  
						   values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			
			
			$inserted = $db->insert_prepare($sql ,  array("$ProductGroupCode", "$PaymentLogId", "$CustReference", "$AlternateCustReference", "$Amount", "$PaymentStatus", "$PaymentMethod", "$PaymentReference", "$TerminalId", "$ChannelName", "$Location", "$IsReversal", "$PaymentDate", "$SettlementDate", "$InstitutionId", "$InstitutionName", "$BranchName", "$BankName", "$FeeName", "$CustomerName", "$OtherCustomerInfo", "$ReceiptNo", "$CollectionsAccount", "$ThirdPartyCode", "$BankCode", "$CustomerAddress", "$CustomerPhoneNumber", "$DepositorName", "$DepositSlipNumber", "$PaymentCurrency","$reason","NO", "NO","YES","$OriginalPaymentLogId","$OriginalPaymentReference") );
			
			if($inserted >= 1)
			{				
				$items = $payment->PaymentItems;
				//echo "in payment $items";
				$payNum = 0;
				foreach($items->PaymentItem as $paymentItem)
				{
					$ItemName = $paymentItem->ItemName;
					$ItemCode = $paymentItem->ItemCode;
					$ItemAmount = $paymentItem->ItemAmount;
					$LeadBankCode = $paymentItem->LeadBankCode;
					$LeadBankCbnCode = $paymentItem->LeadBankCbnCode;
					$LeadBankName = $paymentItem->LeadBankName;
					
					$payNum += processItemsLog($PaymentLogId, $ItemName , $ItemCode, $ItemAmount, $LeadBankCode, $LeadBankCbnCode, $LeadBankName,$PaymentDate,$db);
				}
				
				if($payNum >= 1)
				{
					$done =  true;
				}
				else
				{
					$done =  false;
				}			  	
			}
			else
			{
			  $done =  false;
			}
		}
		catch(Exception $e)
		{
			//echo "Error Occured".$e->getMessage();
		}
			
		return $done;
			
	}
	
	function transExistsNoID($CustReference,$PaymentLogId,$PaymentDate,$Amount,$db)
	{
			//$db = new Database();

			$sql = "select * from PAYMENT_TRANSACTION_FAILURE  where CustReference=? and PaymentLogId=? and PaymentDate=? and Amount=?";
			
			$result = $db->retrieve_prepare($sql , array("$CustReference" ,"$PaymentLogId" ,"$PaymentDate" , "$Amount" ));
			if(!$result)
			{
				throw new Exception("Error !! Result set came back with errors: ".$db->formatError(sqlsrv_errors()));
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
	
	
	
	function verifyKey()
	{
		$clientIP = "";
		$ip = "41.223.145.174";
		$ip2 = "154.72.34.174";
		
		// interswitch test ip address 41.223.145.177 and 41.223.145.179
		// interswitch Go Live ip address  41.223.145.174 and 
		// interswitch Go Live ip address 154.72.34.174
		
		if(isset($_SERVER['REMOTE_ADDR']))
		{
			$clientIP = trim($_SERVER['REMOTE_ADDR']);			
		}
		/*else
		{
			$clientIP = "41.223.145.177";
		}*/
		
		if(trim($clientIP) == trim($ip) or trim($clientIP) == trim($ip2))
		{
			return true;
			
		}
		else
		{
			//return false; // please uncomment later when u migrate to the live server
			return true;			
		}
	}
	
	function getID()
	{	
		return "CID12345";
	}

	function getRequest()
	{
		//@session_start();
		$post = file_get_contents('php://input');
		
		if($post != "")
		{
			return $post;				
		}
		else
		{
			return "";
			
		}
	}
	
?>

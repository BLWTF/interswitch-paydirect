<?php

	class Database
	{
		
		private $connect;		
		private $result;
		private $dbHostname;
		private $dbUsername;
		private $dbPassword;
		private $dbName;
		 
		
		public function __construct($dbN="MYDATABASE",$host="(local)",$user="sa", 
											$pass="MYPASSWORD")
		{
			 
			$this->dbHostname = $host;
			$this->dbUsername = $user;
			$this->dbPassword = $pass;
			if($dbN === "" or $dbN === null or !$dbN)
			{				
				
				$this->dbName = "MYDATABASE";
			}
			else
			{
				$this->dbName = $dbN;
			}
						
		}
		
		public function __destruct()
		{
			$this->dbHostname = "";
			$this->dbUsername = "";
			$this->dbPassword = "";
			$this->dbName = "";
			$this->connect = null;
			$this->result = null;
			
		}
		
		function formatError($errors)
		{
			$err = "";
			foreach($errors as $key => $error)
			{
				$err .= $error['message'] . "<br>";
			}
			
			return $err;
			
		}
		
		function connectDB()
		{
			$connected = false; 
			
			try
			{
				$connectionInfo = array("UID" => $this->dbUsername, "PWD" => $this->dbPassword, 
											"Database"=>$this->dbName);
				//  Make connection to database								      
				//  If no connection made, display error Message       
				$this->connect = sqlsrv_connect($this->dbHostname, $connectionInfo);
				if(!$this->connect)
				{
					throw new Exception("Error !! Unable to connect to database. Reason: ".self::formatError(sqlsrv_errors()));								
				}
				
				$connected = true; 
			}
			catch(Exception $e)
			{
				//echo $e->getMessage(); 
			}
			return $connected;
		}
		
		function retrieve($sql)
		{
			$this->result = sqlsrv_query($this->connect,$sql,array(),array("Scrollable"=>"static"));
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to connect to database. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			return $this->result;
			
		}		
		
		function retrieve_prepare($sql,$parameters)
		{
			$this->result = sqlsrv_query($this->connect,$sql,$parameters,array("Scrollable"=>"static"));
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to retrieve records. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			return $this->result;
			
		}
		
		function update($sql)
		{
			$this->result = sqlsrv_query($this->connect,$sql);
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to update records. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			$rowNum = sqlsrv_rows_affected($this->result);
			
			return $rowNum;
			
		}
		
		function update_prepare($sql,$parameters)
		{
			$this->result = sqlsrv_query($this->connect,$sql,$parameters);
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to update records. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			$rowNum = sqlsrv_rows_affected($this->result);
			
			return $rowNum;
			
		}
		
		function insert($sql)
		{
			$this->result = sqlsrv_query($this->connect,$sql);
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to insert Reords. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			$rowNum = sqlsrv_rows_affected($this->result);
			
			return $rowNum;
			
		}
		
		function insert_prepare($sql,$parameters)
		{
			$this->result = sqlsrv_query($this->connect,$sql,$parameters);
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to insert Reords. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			$rowNum = sqlsrv_rows_affected($this->result);
			
			return $rowNum;
			
		}
		
		function delete($sql)
		{
			$this->result = sqlsrv_query($this->connect,$sql);
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to delete Record. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			$rowNum = sqlsrv_rows_affected($this->result);
			
			return $rowNum;
			
		}
		
		function delete_prepare($sql,$parameters)
		{
			$this->result = sqlsrv_query($this->connect,$sql,$parameters);
			
			if(!$this->result)
			{
				throw new Exception("Error !! Unable to delete Record. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			$rowNum = sqlsrv_rows_affected($this->result);
			
			return $rowNum;
			
		}
		
		function getConnection()
		{
			return $this->connect;			
		}
		
		function closeConnection()
		{	
			try
			{
				
				//close the connection and resultset
				if($this->result){				sqlsrv_free_stmt( $this->result);		}
				if($this->connect){				sqlsrv_close( $this->connect);			}
				
				//if connection and resultset is still active, then throw exception
				if($this->result){				throw new Exception('Statement was not closed Successfully. Reason: '.formatError(sqlsrv_errors()));			}
				if($this->connect){				throw new Exception('Connection was not closed Successfully. Reason: '.formatError(sqlsrv_errors()));			};
			}
			catch(Exception $e)
			{
				//echo $e->getMessage();
			}
			
		}
		
		
		static function doDeCrypt($str)
		{ }
		
		static function doEnCrypt($str)
		{ }
    
		
		static function getNextChar($val)
		{ }


		static function getPreviousChar($val)
		{ }
		
		
		
		function getValue($sql)
		{
			$res = sqlsrv_query($this->connect,$sql);
			
			$value = "-";
			
			if(!$res)
			{
				throw new Exception("Error !! Unable to connect to database. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			
			if(sqlsrv_has_rows($res))
			{
				$arr = sqlsrv_fetch_array($res);
				$value = trim($arr[0]);
			}
			
			return $value;
			
		}
		
		function getValues($sql)
		{
			$res = sqlsrv_query($this->connect,$sql);
			
			$row = array();
			
			if(!$res)
			{
				throw new Exception("Error !! Unable to connect to database. Reason: ".$this->formatError(sqlsrv_errors()));								
			}
			//echo "row num | ".sqlsrv_has_rows($res);
			if(sqlsrv_has_rows($res))
			{
				$row = sqlsrv_fetch_array($res);										
			}
			
			return $row;			
		}
		
		function recordExists($sql)
		{
		  
		  $resu = sqlsrv_query($this->connect,$sql);
		  if(!$resu)
		  {
				throw new Exception("Error !! Result set (REC. EXST) came back with errors: ".$this->formatError(sqlsrv_errors()));
		  }
		  if(sqlsrv_has_rows($resu))
		  {
			  return true;
		  }
		  else
		  {
			  return false;
		  }
		}
		  
		
		
		
		
		
		
	}
	
	/*$data = new Database();	
	$conn = $data->connectDB();
	$data->closeConnection();
	if($conn)
	{
		echo "Database connected";
	}*/

?>
<?php
class Mysql 
{
	function Mysql($host = "localhost", $user = 'root', $pass = 'ak47', $bd = 't') 
	{
		mysql_pconnect($host,$user,$pass) or die(mysql_error());
		mysql_selectdb($bd)or die(mysql_error());
	}
	
	function query($query) 
	{
		$result = mysql_query ( $query )  ;
		if (strtoupper(substr(trim($query), 0, 6))==='SELECT') 
		{
			return fetch($result);
		}

	}
	
	function fetch($result,$rollback_on_error = false)
	{
		if ($result) 
		{
			$return = array ();
			while ( $row = mysql_fetch_assoc ( $result ) ) 
			{
				$return [] = $row;
			}
			//return (empty($return))?null:$return;
			return (! empty ( $return )) ? ((count ( $return ) == 1) ? ((count ( $return [0] ) == 1) ? $return [0] [key1 ( $return [0] )] : $return [0]) : $return) : null;
		}
		else
		{
			$e=mysql_error();
			if(!empty($e))
			{
				pr($e);
				if ($rollback_on_error)
				{
					query("ROLLBACK");
					exit(0);
				}
			}
			return false;
		}
	}

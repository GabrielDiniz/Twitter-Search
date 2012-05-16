<?php
class Mysql
{
	function Mysql($host = "localhost", $user = 'root', $pass = 'ak47', $bd = 't')
	{
		mysql_pconnect($host,$user,$pass) or die(mysql_error());
		mysql_selectdb($bd)or die(mysql_error());
	}

	function query($query, $fetch = true)
	{
		$result = mysql_query ( $query )  ;
		if (strtoupper(substr(trim($query), 0, 6))==='SELECT' && $fetch)
		{
			return $this->fetch($result);
		}
		else
		{
			return $result;
		}

	}

	function fetch($result,$rollback_on_error = false)
	{
		if ($result)
		{
			$return = array ();
			while ( $row = mysql_fetch_row ( $result ) )
			{
				$return [] = $row;
			}
			return (! empty ( $return )) ? ((count ( $return ) == 1) ? ((count ( $return [0] ) == 1) ? $return [0] [$this->key1( $return [0] )] : $return [0]) : $return) : null;
		}
		else
		{
			$e=mysql_error();
			if(!empty($e))
			{
				$this->pr($e);
				if ($rollback_on_error)
				{
					$this->query("ROLLBACK");
				}
				exit(0);
			}
			return false;
		}
	}
	
	function pr($param) {
		print_r($param."\n");
	}
	
	function key1($param) 
	{
		$keys = array_keys ( $param );
		return $keys [0];
	}
}
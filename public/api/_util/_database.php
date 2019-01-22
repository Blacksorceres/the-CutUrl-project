<?php
require_once __DIR__ . '/settings.nogit.php';

class utildbInit extends mysqlAuth
{

	public function mysqli()
	{
		$conn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
		if (mysqli_connect_errno())
		{
		    die("Failed to connect to database: ".mysqli_connect_error()."<br/> Inform the administrator");
		}
		mysqli_query($conn, "SET NAMES utf8");
		mysqli_set_charset($conn,"utf8");
		return $conn;
	}

	public function oopmysqli()
	{
		$conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
		if (mysqli_connect_errno())
		{
		    die("Failed to connect to database: ".mysqli_connect_error()."<br/> Inform the administrator");
		}
		$conn->query("SET NAMES utf8");
		$conn->set_charset("utf8");
		return $conn;
	}

	public function pdo()
	{
		$dsn = "mysql:dbname=$this->dbname;host=$this->dbhost;charset=utf8";
		try
		{
		    $pdo = new PDO($dsn, $this->dbuser, $this->dbpass);
		    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $pdo;
		} catch (PDOException $e)
		{
		    die("Failed to connect to the database: ".$e."<br/> Inform an administrator");
		    exit();
		}
	}
}

class dbManip extends utildbInit
{
	private $pdo;
	private $stmt;

	/**
	 * Insert new data into the database
	 * @param  string 	$table  The name of the table being queried
	 * @param  array 	$datArr	The array of data being inserted
	 * @return bool         	TRUE of FALSE if the action succeeded or failed
	 */
	public function insert($table=null, $datArr=null)
	{
		// If any data is not correctly provided, the function will return false
		if (
			$datArr	==	null 	||
			!is_array($datArr)	||
			$table	==	null	||
			!is_string($table)
		)
		{
			return false;
		}

		// Create arrays with field names and their values
		$fields = [];
		$values = [];
		$placeholders = rtrim(str_repeat('?,',count($datArr)),',');
		foreach ($datArr as $key => $value)
		{
			array_push($fields, $key);
			if (is_bool($value))
			{
				// If the value is boolean we replace "true/false" with "1/0" since MySql has bool values stored as INT(1)
				array_push($values, ( $value==true ? 1 : 0 ));
			} else {
				array_push($values, $value);
			}
		}

		// Create the SQL statement as string
		$sql = sprintf(
		    'INSERT INTO `%s` (%s) VALUES (%s);',
		    $table,
		    implode(', ', $fields),
		    $placeholders
		);
		$pdo = $this->pdo();

		if ($stmt = $pdo->prepare($sql))
		{
			if ($stmt->execute($values))
			{
				if ($stmt->rowCount() == 1)
				{
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

}

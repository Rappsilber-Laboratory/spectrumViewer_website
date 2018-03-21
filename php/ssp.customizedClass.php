<?php
/*
* Lars Kolbowski: Adopted to work with SQLite3
* Helper functions for building a DataTables server-side processing SQL query
*
* The static functions in this class are just helper functions to help build
* the SQL used in the DataTables demo server-side processing scripts. These
* functions obviously do not represent all that can be done with server-side
* processing, they are intentionally simple to show how it works. More complex
* server-side processing operations will likely require a custom script.
*
* See http://datatables.net/usage/server-side for full details on the server-
* side processing requirements of DataTables.
*
* @license MIT - http://datatables.net/license_mit
*
* Customized By emranulhadi@gmail.com | http://emranulhadi.wordpress.com/
*/
// REMOVE THIS BLOCK - used for DataTables test environment only!
//$file = $_SERVER['DOCUMENT_ROOT'].'/datatables/mysql.php';
//if ( is_file( $file ) ) {
// include( $file );
//}
class SSP {

	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public static function interpolateQuery($query, $params) {
		$keys = array();

		foreach ($params as $param) {
				$query = preg_replace('/'.$param["key"].'/', "'".$param["val"]."'", $query, 1, $count);

		}

		return $query;
	}

/**
* Create the data output array for the DataTables rows
*
* @param array $columns Column information array
* @param array $data Data from the SQL get
* @param bool $isJoin Determine the query is complex or simple one
*
* @return array Formatted data in a row based format
*/
static function data_output ( $columns, $data, $isJoin = false )
{
$out = array();
for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
$row = array();
for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
$column = $columns[$j];
// Is there a formatter?
if ( isset( $column['formatter'] ) ) {
$row[ $column['dt'] ] = ($isJoin) ? $column['formatter']( $data[$i][ $column['field'] ], $data[$i] ) : $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
}
else {
$row[ $column['dt'] ] = ($isJoin) ? $data[$i][ $columns[$j]['field'] ] : $data[$i][ $columns[$j]['db'] ];
}
}
$out[] = $row;
}
return $out;
}
/**
* Paging
*
* Construct the LIMIT clause for server-side processing SQL query
*
* @param array $request Data sent to server by DataTables
* @param array $columns Column information array
* @return string SQL limit clause
*/
static function limit ( $request, $columns )
{
$limit = '';
if ( isset($request['start']) && $request['length'] != -1 ) {
$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
}
return $limit;
}
/**
* Ordering
*
* Construct the ORDER BY clause for server-side processing SQL query
*
* @param array $request Data sent to server by DataTables
* @param array $columns Column information array
* @param bool $isJoin Determine the query is complex or simple one
*
* @return string SQL order by clause
*/
static function order ( $request, $columns, $isJoin = false )
{
$order = '';
if ( isset($request['order']) && count($request['order']) ) {
$orderBy = array();
$dtColumns = SSP::pluck( $columns, 'dt' );
for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
// Convert the column index into the column data property
$columnIdx = intval($request['order'][$i]['column']);
$requestColumn = $request['columns'][$columnIdx];
$columnIdx = array_search( $requestColumn['data'], $dtColumns );
$column = $columns[ $columnIdx ];
if ( $requestColumn['orderable'] == 'true' ) {
$dir = $request['order'][$i]['dir'] === 'asc' ?
'ASC' :
'DESC';
$orderBy[] = ($isJoin) ? $column['db'].' '.$dir : ''.$column['db'].' '.$dir;
}
}
$order = 'ORDER BY '.implode(', ', $orderBy);
}
return $order;
}
/**
* Searching / Filtering
*
* Construct the WHERE clause for server-side processing SQL query.
*
* NOTE this does not match the built-in DataTables filtering which does it
* word by word on any field. It's possible to do here performance on large
* databases would be very poor
*
* @param array $request Data sent to server by DataTables
* @param array $columns Column information array
* @param array $bindings Array of values for PDO bindings, used in the sql_exec() function
* @param bool $isJoin Determine the the query is complex or simple one
*
* @return string SQL where clause
*/
static function filter ( $request, $columns, &$bindings, $isJoin = false )
{
$globalSearch = array();
$columnSearch = array();
$dtColumns = SSP::pluck( $columns, 'dt' );
if ( isset($request['search']) && $request['search']['value'] != '' ) {
$str = $request['search']['value'];
for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
$requestColumn = $request['columns'][$i];
$columnIdx = array_search( $requestColumn['data'], $dtColumns );
$column = $columns[ $columnIdx ];
if ( $requestColumn['searchable'] == 'true' ) {
$binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
$globalSearch[] = ($isJoin) ? $column['db']." LIKE ".$binding : "`".$column['db']."` LIKE ".$binding;
}
}
}
// Individual column filtering
for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
$requestColumn = $request['columns'][$i];
$columnIdx = array_search( $requestColumn['data'], $dtColumns );
$column = $columns[ $columnIdx ];
$str = $requestColumn['search']['value'];
if ( $requestColumn['searchable'] == 'true' &&
$str != '' ) {
	// var_dump($requestColumn['search']['regex']);
	if($requestColumn['search']['regex'] == "true"){
		$binding = SSP::bind( $bindings, $str, PDO::PARAM_STR );
		// does not work because regexp is not defined -- userdefined function
		$columnSearch[] = ($isJoin) ? $column['db']." regexp ".$binding : "`".$column['db']."` regexp ".$binding;
		//hacky workaroung for pep2
		// $columnSearch[] = ($isJoin) ? $column['db']." ".$binding." != ''" : "`".$column['db']."` LIKE ".$binding." != ''";
	}
	else{
		$binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
		$columnSearch[] = ($isJoin) ? $column['db']." LIKE ".$binding : "`".$column['db']."` LIKE ".$binding;
	}
}
}
// Combine the filters into a single string
$where = '';
if ( count( $globalSearch ) ) {
$where = '('.implode(' OR ', $globalSearch).')';
}
if ( count( $columnSearch ) ) {
$where = $where === '' ?
implode(' AND ', $columnSearch) :
$where .' AND '. implode(' AND ', $columnSearch);
}
if ( $where !== '' ) {
$where = 'WHERE '.$where;
}
return $where;
}
/**
* Perform the SQL queries needed for an server-side processing requested,
* utilising the helper functions of this class, limit(), order() and
* filter() among others. The returned array is ready to be encoded as JSON
* in response to an SSP request, or can be modified if needed before
* sending back to the client.
*
* @param array $request Data sent to server by DataTables
* @param array $sql_details SQL connection details - see sql_connect()
* @param string $table SQL table to query
* @param string $primaryKey Primary key of the table
* @param array $columns Column information array
* @param array $joinQuery Join query String
* @param string $extraWhere Where query String
*
* @return array Server-side processing response array
*
*/
static function simple ( $request, $sql_details, $table, $primaryKey, $columns, $joinQuery = NULL, $extraWhere = '', $groupBy = '', $selectModify = '', $jsonCol = NULL)
{
$bindings = array();
$db = SSP::sql_connect( $sql_details );

$limit = SSP::limit( $request, $columns );
$order = SSP::order( $request, $columns, $joinQuery );
$where = SSP::filter( $request, $columns, $bindings, $joinQuery );
// IF Extra where set then set and prepare query
if($extraWhere){
$extraWhere = ($where) ? ' AND '.$extraWhere : ' WHERE '.$extraWhere;
}
// Main query to actually get the data
if($joinQuery){
$col = SSP::pluck($columns, 'db', $joinQuery);
$query = "SELECT ".$selectModify." ".implode(", ", $col)."
$joinQuery
$where
$extraWhere
$groupBy
$order";
// echo $query;
// die();
}else{
	// jsonCol could be changed to a joinQuery...
	if ($jsonCol){
		$fromModify = ', json_each('.$table.'.'.$jsonCol.')';
	}
	else {
		$fromModify = '';
	}
$query = "SELECT ".$selectModify." ".implode(", ", SSP::pluck($columns, 'db'))."
FROM `$table`".$fromModify."
$where
$extraWhere
$groupBy
$order";
// echo $query;
}
// var_dump(SSP::interpolateQuery($query, $bindings));

$data = SSP::sql_exec( $db, $bindings, $query);
// var_dump($data);
// Data set length after filtering
// $resFilterLength = SSP::sql_exec( $db, $bindings, $filterQuery);
// var_dump($resFilterLength);
// $recordsFiltered = $resFilterLength[0][0];
$recordsFiltered = sizeof($data);
// var_dump($recordsFiltered);
// Total data set length
$resTotalLength = SSP::sql_exec( $db,
"SELECT DISTINCT `{$primaryKey}`
FROM `$table`"
);
$recordsTotal = sizeof($resTotalLength);
// var_dump($recordsTotal);
/*
* Output
*/
$start = intval($request['start']);
$length = intval($request['length']);

$returnData = array_slice($data, $start, $length);

return array(
"draw" => intval( $request['draw'] ),
"recordsTotal" => intval( $recordsTotal ),
"recordsFiltered" => intval( $recordsFiltered ),
"data" => SSP::data_output( $columns, $returnData, $joinQuery )
);
}
/**
* Connect to the database
*
* @param array $sql_details SQL server connection details array, with the
* properties:
* * host - host name
* * db - database name
* * user - user name
* * pass - user password
* @return resource Database connection handle
*/
static function sql_connect ( $sql_details )
{
try {
$db = @new PDO($sql_details);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->sqliteCreateFunction('regexp', function($y, $x) { return preg_match('/' . addcslashes($y, '/') . '/', $x); }, 2);
// "mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
// $sql_details['user'],
// $sql_details['pass'],
// array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION , PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'" )
// );
}
catch (PDOException $e) {
SSP::fatal(
"An error occurred while connecting to the database. ".
"The error reported by the server was: ".$e->getMessage()
);
}
return $db;
}
/**
* Execute an SQL query on the database
*
* @param resource $db Database handler
* @param array $bindings Array of PDO binding values from bind() to be
* used for safely escaping strings. Note that this can be given as the
* SQL query string if no bindings are required.
* @param string $sql SQL query to execute.
* @return array Result from the query (all rows)
*/
static function sql_exec ( $db, $bindings, $sql=null )
{
// Argument shifting
if ( $sql === null ) {
$sql = $bindings;
}
echo $sql;
$stmt = $db->prepare( $sql );

// echo $sql;
// Bind parameters
if ( is_array( $bindings ) ) {
for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
$binding = $bindings[$i];
$stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
}
}
// Execute
try {
$stmt->execute();
}
catch (PDOException $e) {
SSP::fatal( "An SQL error occurred: ".$e->getMessage() );
}
// Return all
return $stmt->fetchAll();
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* Internal methods
*/
/**
* Throw a fatal error.
*
* This writes out an error message in a JSON string which DataTables will
* see and show to the user in the browser.
*
* @param string $msg Message to send to the client
*/
static function fatal ( $msg )
{
echo json_encode( array(
"error" => $msg
) );
exit(0);
}
/**
* Create a PDO binding key which can be used for escaping variables safely
* when executing a query with sql_exec()
*
* @param array &$a Array of bindings
* @param * $val Value to bind
* @param int $type PDO field type
* @return string Bound key to be used in the SQL where this parameter
* would be used.
*/
static function bind ( &$a, $val, $type )
{
$key = ':binding_'.count( $a );
$a[] = array(
'key' => $key,
'val' => $val,
'type' => $type
);
return $key;
}
/**
* Pull a particular property from each assoc. array in a numeric array,
* returning and array of the property values from each item.
*
* @param array $a Array to get data from
* @param string $prop Property to read
* @param bool $isJoin Determine the the JOIN/complex query or simple one
* @return array Array of property values
*/
static function pluck ( $a, $prop, $isJoin = false )
{
$out = array();
for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
$out[] = ($isJoin && isset($a[$i]['as'])) ? $a[$i][$prop]. ' AS '.$a[$i]['as'] : $a[$i][$prop];
}
return $out;
}
}

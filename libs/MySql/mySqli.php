<?php

/**
 * Class mySqliDB
 * @author Sujeet Kumar<sujeet0303@yahoo.com>
 */

class mySqliDB
{

    private $host = 'localhost';                // default host
    private $user = 'root';                        // default user name
    private $pass = '';                            // default password
    private $strDBName;                            // DATABASE name

    private $errNo;
    private $result;                            // internal result storage variable
    private $objMySqli;                            // mysqli object

    public $tableName;                            // table currently in use
    public $arrfield;                            // fields array in insert , select , and update
    public $whereCondition = '';                        // Where condition
    public $arrOrder;                            // order by
    public $strLimit;                            // limit
    public $strQuery;                            // Query String

    public $strErrMsg;
    public $insertedId;                            // stores insert Id

    /**
     * Constructor sets default value of host,user,Password From Config file
     *
     */
    public function __construct()
    {
        if (defined('DB_HOST')) $this->host = DB_HOST;
        if (defined('DB_USERNAME')) $this->user = DB_USERNAME;
        if (defined('DB_PASSWORD')) $this->pass = DB_PASSWORD;
        if (defined('DB_DATABASE')) $this->strDBName = DB_DATABASE;
    }

    /**
     * Connect to DataBase with optional fields as userName,password,hostName
     *
     * @param String $strDBName
     * @param String $strHost
     * @param String $strUser
     * @param String $strPass
     * @throws Exception
     */
    public function connect($strDBName = '', $strHost = '', $strUser = '', $strPass = '')
    {
        if ($strDBName) $this->strDBName = $strDBName;
        if ($strHost) $this->host = $strHost;
        if ($strUser) $this->user = $strUser;
        if ($strPass) $this->pass = $strPass;

        if (class_exists('mysqli'))
            $this->objMySqli = new mysqli($this->host, $this->user, $this->pass);
        else
            throw new Exception(__METHOD__ . "(): MySQLi Class not available ,Please enable it through php.ini file");

        if (!$this->objMySqli) {
            $this->setError(mysqli_connect_errNo(), mysqli_connect_error());
            throw new Exception(__METHOD__ . "(): Unable to connect with database " . $this->objMySqli->errno . ' ' . $this->objMySqli->error);
        } elseif (!empty($strDBName)) {
            if (!@$this->objMySqli->select_db($this->strDBName)) {
                $this->setError($this->objMySqli->errno, $this->objMySqli->error);
                throw new Exception(__METHOD__ . "(): Unable to select database " . $this->objMySqli->errno . ' ' . $this->objMySqli->error);
            }
        }

    }

    /**
     * Select a database or change current working database
     *
     * @param String $strDbname
     * @throws Exception
     */
    function selectDB($strDbname = '')
    {
        if (empty($this->strDBName))
            $this->strDBName = $strDbname;

        if (!$this->objMySqli->select_db($this->strDBName)) {
            $this->setError($this->objMySqli->errno, $this->objMySqli->error);
            throw new Exception(__METHOD__ . " : Unable to change database " . $this->objMySqli->errno . ' ' . $this->objMySqli->error);
        }


    }

    /**
     * This function is used to disconnect the database server link
     */
    function close()
    {
        if ($this->objMySqli)
            $this->objMySqli->close();
    }

    /**
     * This function is used to execute the query
     *
     * @param String $strquery
     * @throws Exception
     * @return resultSet
     */
    public function query($strquery)
    {
        $this->strQuery = $strquery;
        $this->result = $this->objMySqli->query($this->strQuery);
        if (!$this->result) {
            $this->setError($this->objMySqli->errno, $this->objMySqli->error);
            throw new Exception(__METHOD__ . " : Unable to execute Query: <br><font color=#0f00ff>" . nl2br($this->strQuery) . "</font><br> Database engine says: <br><font color=#ff000f>" . $this->getError() . "</font><br>");
        }

        return $this->result;
    }

    /**
     * This function is used to execute the multi queries and should also be used for SP when there is single or more select query.
     *
     * @param String $strQuery
     * @throws Exception
     * @return ResultSet
     */
    public function multiQuery($strQuery)
    {
        $this->query = $strQuery;
        if (!$this->objMySqli->multi_query($this->query)) {
            $this->setError($this->objMySqli->errno, $this->objMySqli->error);
            throw new Exception(__METHOD__ . " : Unable to execute Query: <br><font color=#0f00ff>" . nl2br($this->query) . "</font><br> Database engine says: <br><font color=#ff000f>" . $this->getError() . "</font><br>");
            return false;
        } else return $this->result = $this->objMySqli->store_result();
    }


    /**
     * This function is used to transfer next resultset from multiquery case to class ResultSet Object
     *
     * @return returns true or false
     */
    public function nextResult()
    {
        if ($this->objMySqli->more_results()) {
            if($this->result) $this->result->free();
            $this->objMySqli->next_result();
            $this->result = $this->objMySqli->store_result();
            if ($this->result)
                return true;
        }
        return false;
    }

    /**
     * This function is used to get last insrted id,
     *
     * @return insertId or 0
     */
    public function insertedID()
    {
        return $this->insertedId = $this->objMySqli->insert_id;
    }

    /**
     * This function is used to get affected rows by a query
     *
     * @return number of effected rows
     */
    function affectedRows()
    {
        return $this->objMySqli->affected_rows;
    }

    /**
     * This function is used to free the mysql result
     */
    function result_Close()
    {
        if (is_object($this->result))
            $this->result->close();
    }

    /**
     * This function is used to count records after executing the query
     *
     * @return numbers of rows in resultSet
     */
    function result_numRows()
    {
        if (is_object($this->result))
            return $this->result->num_rows;
        else
            return 0;
    }

    /**
     * Use this method after executing "ExecuteQuery" method for fetching the records as array
     *
     * @return ResultSet as array or false;
     */
    function result_fetchArray()
    {
        $arrRecords = array();
        if ($arrRecords = $this->result->fetch_array())
            return $arrRecords;
        else return false;
    }

    /**
     * Use this method after executing "ExecuteQuery" method for fetching the records as object
     *
     * @return ResultSet as object or false;
     */
    function result_fetchObject()
    {
        if ($objRecords = $this->result->fetch_object())
            return $objRecords;
        else return false;
    }

    /**
     * Use this method after executing "ExecuteMultiQuery" method for fetching the records
     */
    public function fetchAllMultiRecords()
    {
        $arrRecords = array();
        if (is_object($this->result)) {
            $intCountRS = 0;
            do {
                if ($this->result_numRows() > 0) {
                    while ($strRec = $this->result->fetch_array()) {
                        $arrRecords[$intCountRS][] = $strRec;
                    }
                } else {
                    $arrRecords[$intCountRS] = array();
                }
                $intCountRS++;
            } while ($this->nextResult());
        }
        return $arrRecords;

    }

    /**
     * Use this method after executing "ExecuteMultiQuery" method for fetching the records as object
     *
     * @return array of resultset as object
     */
    public function fetchAllMultiRecordsAsObject()
    {
        $arrRecords = array();
        if (is_object($this->result)) {
            $intCountRS = 0;
            do {
                if ($this->result_numRows() > 0) {
                    while ($strRec = $this->result->fetch_object()) {
                        $arrRecords[$intCountRS][] = $strRec;
                    }
                } else $arrRecords[$intCountRS] = array();
                /*{
                    $res = $this->result->fetch_object();
                    if($res) {
                        $arrRecords[$intCountRS][] = $this->result->fetch_object();
                    } else {
                        $arrRecords[$intCountRS] = array();
                    }
                }*/
                $intCountRS++;
            } while ($this->nextResult());

        }
        return $arrRecords;

    }

    /**
     * Sets MySql error in class variables
     *
     * @param int $interrNo
     * @param String $strErrMsg
     */
    function setError($interrNo, $strErrMsg)
    {
        $this->errNo = $interrNo;
        $this->strErrMsg = $strErrMsg;
    }

    /**
     * Get mySQL Error No and error String
     *
     * @return mySQL Error No and error String
     */
    public function getError()
    {
        return "$this->errNo: $this->strErrMsg";
    }

    /**
     * results number of fields
     *
     * @return field count or 0
     */
    public function result_fieldCount()
    {
        if (is_object($this->result))
            return $this->result->field_count;
        else
            return 0;
    }

    /**
     * Returns Field Name
     *
     * @param results offset
     * @return string field name
     */
    public function getFieldName($intOffset)
    {
        $ostrName = '';
        if (is_object($this->result)) {
            $objFieldInfo = $this->result->fetch_field_direct($intOffset);
            if (is_object($objFieldInfo))
                $ostrName = $objFieldInfo->name;
        }
        return $ostrName;
    }

    /**
     * This function is used to get table max field value length in a result set for a given offset
     *
     * @param int $intOffset
     * @return maxlength
     */
    public function getFieldLength($intOffset)
    {
        $ovarReturn = false;
        if (is_object($this->result)) {
            $objFieldInfo = $this->result->fetch_field_direct($intOffset);
            if (is_object($objFieldInfo))
                $ovarReturn = $objFieldInfo->max_length;
        }
        return $ovarReturn;
    }

    /**
     * This function is used to get table max fields value length for a result set
     *
     * @param $strpQuery
     * @return array
     * @throws Exception
     */
    public function dbTableFieldsLength($strpQuery)
    {
        $arrFields = array();
        $this->query = $strpQuery;
        $this->result = $this->objMySqli->query($this->query);

        if (!$this->result) {
            $this->setError($this->objMySqli->errno, $this->objMySqli->error);
            throw new Exception(__METHOD__ . " : Unable to execute Query: <br><font color=#0f00ff>" .
                nl2br($this->query) . "</font><br><font color=#ff000f>" . $this->getError() . "</font><br>");
        } else {
            $intFieldsCount = $this->result->field_count;
            $objFieldInfo = false;
            for ($intOffset = 0; $intOffset < $intFieldsCount; ++$intOffset) {
                $objFieldInfo = $this->result->fetch_field_direct($intOffset);
                if (is_object($objFieldInfo)) {
                    $strKey = $objFieldInfo->name;
                    $arrFields[$strKey] = $objFieldInfo->max_length;
                }
            }

        }
        return $arrFields;
    }


    /**
     * This function is used to commit transaction
     */
    function commit()
    {
        if ($this->objMySqli)
            $this->objMySqli->commit();
    }

    /**
     * Insert values in table
     *
     * @param AssociativeArray $values
     * @param string $fields
     * @param string $tblName
     * @internal param Array $fieldName
     * @internal param String $tableName
     * @return unknown
     */
    public function insert($values, $fields = '', $tblName = '')
    {
        if ($tblName == '') $tblName = $this->tableName;

        $this->strQuery = "INSERT INTO `$tblName` ";
        $strValue = '';
        $strFields = '(';
        $strComma = '';
        $isAssocArr = 0;

        if (is_array($values)) {
            foreach ($values as $field => $value) {
                if (is_array($value)) {
                    $tempValue = "$strComma('";
                    $tempValue .= implode("','", addslashes($value));
                    $tempValue .= "')";
                    $strValue .= $tempValue;
                    $strComma = ',';
                } else {
                    if (is_int($field))

                        $strValue .= "$strComma'".addslashes($value)."'";
                    else {
                        $isAssocArr = 1;
                        $strFields .= "$strComma`$field`";
                        $strValue .= "$strComma'".addslashes($value)."'";
                    }
                    $strComma = ',';
                }
            }
            if (!(@is_array($values[0]))) $strValue = "($strValue)";

        } elseif (!empty($values))
            $strValue = "($values)";

        if ($isAssocArr) $strFields .= '';
        else $strFields .= empty($fields) ? $this->evalFieldList($this->arrfield, 1) : $this->evalFieldList($fields, 1);

        $strFields .= ")";

        if (empty($fields) && $isAssocArr != 1)
            $strFields = '';

        $this->strQuery .= " $strFields VALUES $strValue";

        return $this->result = $this->query($this->strQuery);
    }

    /**
     * Insert update values in table
     *
     * @param $values array AssociativeArray
     * @param $updates
     * @param string $fields
     * @param string $tblName
     * @internal param Array $fieldName
     * @internal param String $tableName
     * @return object
     */
    public function insertUpdate($values, $updates, $fields = '', $tblName = '')
    {
        if ($tblName == '') $tblName = $this->tableName;

        $this->strQuery = "INSERT INTO `$tblName` ";
        $strValue = '';
        $strFields = '(';
        $strComma = '';
        $isAssocArr = 0;

        if (is_array($values)) {
            foreach ($values as $field => $value) {
                if (is_array($value)) {
                    $tempValue = "$strComma('";
                    $tempValue .= implode("','", addslashes($value));
                    $tempValue .= "')";
                    $strValue .= $tempValue;
                    $strComma = ',';
                } else {
                    if (is_int($field))
                        $strValue .= "$strComma'".addslashes($value)."'";
                    else {
                        $isAssocArr = 1;
                        $strFields .= "$strComma`$field`";
                        $strValue .= "$strComma'".addslashes($value)."'";
                    }
                    $strComma = ',';
                }
            }
            if (!(@is_array($values[0]))) $strValue = "($strValue)";

        } elseif (!empty($values))
            $strValue = "($values)";

        if ($isAssocArr) $strFields .= '';
        else $strFields .= empty($fields) ? $this->evalFieldList($this->arrfield, 1) : $this->evalFieldList($fields, 1);

        $strFields .= ")";

        if (empty($fields) && $isAssocArr != 1)
            $strFields = '';

		 $strUpdates = '';
		 $strComma = '';

		 if (is_array($updates)) {
			 foreach ($updates as $field => $value) {
				 $strUpdates .= "$strComma `$field` = '".addslashes($value)."' ";
				 $strComma = ',';
			 }
		 } else
			 $strUpdates .= "$updates";

        $this->strQuery .= " $strFields VALUES $strValue ON DUPLICATE KEY UPDATE " . $strUpdates;

        return $this->result = $this->query($this->strQuery);
    }

    /**
     * Update table with values passed as parameters
     *
     * @param Associative Array or String $where
     * @param string $where
     * @param string $tblName
     * @internal param String $tableName
     * @return boolean
     */
    public function update($values, $where = '', $tblName = '')
    {
        if ($tblName == '') $tblName = $this->tableName;

        $this->strQuery = "UPDATE `$tblName` SET";
        $strValue = '';
        $strSaprator = '';

        if (is_array($values)) {
            foreach ($values as $field => $value) {
                $strValue .= "$strSaprator `$field` = '".addslashes($value)."' ";
                $strSaprator = ',';
            }
        } else
            $strValue .= "$values";

        $strWhere = empty($where) ? $this->evalWhereCondition($this->whereCondition) : $this->evalWhereCondition($where);

        $this->strQuery .= " $strValue $strWhere";

        return $this->result = $this->query($this->strQuery);
    }

    /**
     * select fields for table
     *
     * @param Array|string $fields
     * @param AssociativeArray|string $where
     * @param string $orderby
     * @param String $limit
     * @param String $tblName
     * @internal param \or $Array string $orderby
     * @return resultset
     */
    public function select($fields = '', $where = '', $orderby = '', $limit = '', $enableCount = 0 , $tblName = '')
    {
        if ($tblName == '') $tblName = $this->tableName;

        $limit = empty($limit) ? $this->strLimit : $limit;
        $strFields = empty($fields) ? $this->evalFieldList($this->arrfield, 1) : $this->evalFieldList($fields, 1);
        $strWhere = empty($where) ? $this->evalWhereCondition($this->whereCondition) : $this->evalWhereCondition($where);
        $strOrder = empty($orderby) ? $this->evalOrderByString($this->arrOrder) : $this->evalOrderByString($orderby);
        $this->strQuery = "SELECT $strFields FROM `$tblName` $strWhere $strOrder $limit";
        if($enableCount) {
            $strFields = ' SQL_CALC_FOUND_ROWS '.$strFields;
            $this->strQuery = "SELECT $strFields FROM `$tblName` $strWhere $strOrder $limit; ";
            $this->strQuery .= "SELECT FOUND_ROWS() AS total;";
            return $this->result = $this->multiQuery($this->strQuery);
        } else {
            return $this->result = $this->query($this->strQuery);
        }
    }


    /**
     * Delete a record for table
     *
     * @param Array or String $where
     * @param string $tableName
     * @return unknown
     */
    public function delete($where, $tableName = '')
    {
        $strWhere = empty($where) ? $this->evalWhereCondition($this->whereCondition) : $this->evalWhereCondition($where);

        if ($tableName == '')
            $tableName = $this->tableName;

        $this->strQuery = "DELETE FROM `$tableName` $strWhere";

        return $this->result = $this->query($this->strQuery);
    }

    function evalFieldList($fields, $forSelect = false)
    {
        if (is_array($fields))
            $strFields = '`' . implode('`,`', $fields) . '`';
        else $strFields = "$fields";

        if (empty($fields) && $forSelect) $strFields = '*';

        return $strFields;
    }

    function evalWhereCondition($where)
    {
        $strSeparator = '';
        $operator = '=';
        $strWhere = '';
        if (is_array($where)) {
            foreach ($where as $field => $value) {
                if (is_array($value)) {
                    if (!empty($value[2])) $operator = $value[2];
                    else $operator = '=';
                    $strWhere .= " $strSeparator `".addslashes($value[0])."` $operator '".addslashes($value[1])."' ";
                    $strSeparator = ' AND ';
                } elseif (is_int($field)) {
                    $strWhere .= "$strSeparator ".addslashes($value)."";
                    $strSeparator = ' AND ';
                } else {
                    $strWhere .= "$strSeparator `$field`='".addslashes($value)."'";
                    $strSeparator = ' AND ';
                }
            }
        } else $strWhere .= $where;

        return $strWhere = empty($strWhere) ? '' : " WHERE $strWhere ";
    }

    function evalOrderByString($orderby)
    {
        $strSeparator = '';
        $strOrder = '';
        if (is_array($orderby)) {
            foreach ($orderby as $field => $value) {
                if (is_int($field))
                    $strOrder .= "$strSeparator ".addslashes($value)."";
                else
                    $strOrder .= "$strSeparator $field ".addslashes($value)."";
                $strSeparator = ',';
            }
            $strOrder = " ORDER BY $strOrder ";
        } else $strOrder = $orderby;

        return $strOrder;
    }

}

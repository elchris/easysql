<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/19/14
 * Time: 3:36 PM
 */

namespace com\github\elchris\easysql;


class EasySQLBeanQuery
{
    /**
     * @param IEasySQLBean[] $beanArray
     * @param string $tableName
     * @return array
     */
    public function getInsertQueryAndPropsForBeanArrayAndTable($beanArray, $tableName = null)
    {
        //TODO Refactor this.
        $firstBean = $beanArray[0];
        $reflectionProps = $firstBean->getReflectionClass()->getProperties();
        $singleBeanInsertionString = '(';
        $singleBeanInsertionItemArray = array();
        $columnsArray = array();

        foreach ($reflectionProps as $reflectionProp) {
            if ($reflectionProp->isPublic()) {
                array_push($singleBeanInsertionItemArray, '?');
                array_push($columnsArray, $reflectionProp->getName());
            }
        }

        $singleBeanInsertionString .= implode(',', $singleBeanInsertionItemArray) . ')';
        $table = $this->getTableName($tableName, $firstBean->getReflectionClass());
        $valuesInsertArray = array();
        $values = array();

        foreach ($beanArray as $bean) {
            array_push(
                $valuesInsertArray,
                $singleBeanInsertionString
            );
            foreach ($reflectionProps as $reflectionProp) {
                if ($reflectionProp->isPublic()) {
                    array_push($values, $reflectionProp->getValue($bean));
                }
            }//loop thru properties, add its value to values
        }//loop thru passed beans

        $q = 'insert into'
            . $table
            . ' ('
            . implode(
                ',', $columnsArray
            )
            . ') values '
            . implode(
                ',', $valuesInsertArray
            )
            . ';';
        return array($values, $q);
    }//getInsertQueryAndPropsForBeanArrayAndTable

    /**
     * @param string $tableName
     * @param \ReflectionClass $reflectionBean
     * @return string
     */
    private function getTableName($tableName, $reflectionBean)
    {
        $table = ' ';
        if (!is_null($tableName)) {
            $table .= $tableName;
            return $table;
        } else {
            $table .= strtolower($reflectionBean->getShortName());
            return $table;
        }
    }//getPropertiesFromBean

    /**
     * @param IEasySQLBean $bean
     * @param string $tableName
     * @return array
     */
    public function getInsertQueryAndPropsForBeanTable(IEasySQLBean $bean, $tableName = null)
    {
        $reflectionBean = $bean->getReflectionClass();
        $table = $this->getTableName($tableName, $reflectionBean);
        $props = $this->getPropertiesFromBean($bean);
        $insertQuery = $this->getInsertQueryForPropsAndTable($props, $table);
        return array($props, $insertQuery);
    }//getInsertQueryAndPropsForBeanTable

    /**
     * @param IEasySQLBean $bean
     * @return array
     */
    private function getPropertiesFromBean(IEasySQLBean $bean)
    {
        $props = array();
        foreach ($bean->getReflectionClass()->getProperties() as $beanProp) {
            if ($beanProp->isPublic()) {
                $props[$beanProp->getName()] = $beanProp->getValue($bean);
            }
        }
        return $props;
    }//getTableName

    private function getInsertQueryForPropsAndTable($props, $table)
    {
        $columns = implode(', ', array_keys($props));
        $values = ':' . implode(', :', array_keys($props));
        $q = 'insert into'
            . $table . ' (' . $columns . ') values (' . $values . ');';
        return $q;
    }//getInsertQueryForPropsAndTable
}//EasySQLBeanQuery
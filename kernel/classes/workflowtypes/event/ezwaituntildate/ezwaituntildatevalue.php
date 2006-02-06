<?php
//
// Definition of eZWaitUntilDateValue class
//
// Created on: <14-���-2003 14:49:06 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*! \file ezwaituntildatevalue.php
*/

/*!
  \class eZWaitUntilDateValue ezwaituntildatevalue.php
  \brief The class eZWaitUntilDateValue does

*/

include_once( "kernel/classes/ezpersistentobject.php" );

class eZWaitUntilDateValue extends eZPersistentObject
{
    /*!
     Constructor
    */
    function eZWaitUntilDateValue( $row )
    {
        $this->eZPersistentObject( $row );
        $this->ClassName = null;
        $this->ClassAttributeName = null;

    }

    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "workflow_event_id" => array( 'name' => "WorkflowEventID",
                                                                       'datatype' => 'integer',
                                                                       'default' => 0,
                                                                       'required' => true ),
                                         "workflow_event_version" => array( 'name' => "WorkflowEventVersion",
                                                                            'datatype' => 'integer',
                                                                            'default' => 0,
                                                                            'required' => true ),
                                         "contentclass_id" => array( 'name' => "ContentClassID",
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true ),
                                         "contentclass_attribute_id" => array( 'name' => "ContentClassAttributeID",
                                                                               'datatype' => 'integer',
                                                                               'default' => 0,
                                                                               'required' => true ) ),
                      "keys" => array( "id", "workflow_event_id", "workflow_event_version" ),
                      "function_attributes" => array( "class_name" => "className",
                                                      "classattribute_name" => "classAttributeName" ),
                      "increment_key" => "id",
                      "sort" => array( "id" => "asc" ),
                      "class_name" => "eZWaitUntilDateValue",
                      "name" => "ezwaituntildatevalue" );
    }

    function &className()
    {
        if ( $this->ClassName === null )
        {
            $contentClass = eZContentClass::fetch( $this->attribute( 'contentclass_id' ) );
            $this->ClassName =& $contentClass->attribute( 'name' );
        }
        return $this->ClassName;
    }

    function &classAttributeName()
    {
        if ( $this->ClassAttributeName === null )
        {
            $contentClassAttribute =& eZContentClassAttribute::fetch( $this->attribute( 'contentclass_attribute_id' ) );
            $this->ClassAttributeName =& $contentClassAttribute->attribute( 'name' );
        }
        return $this->ClassAttributeName;
    }

    function &clone()
    {
        $row = array( "id" => null,
                      "workflow_event_id" => $this->attribute( 'workflow_event_id' ),
                      "workflow_event_version" => $this->attribute( 'workflow_event_version' ),
                      "contentclass_id" => $this->attribute( "contentclass_id" ),
                      "contentclass_attribute_id" => $this->attribute( 'contentclass_attribute_id' ) );
        $newWaitUntilDateValue = new eZWaitUntilDateValue( $row );
        return $newWaitUntilDateValue;
    }

    function create( $workflowEventID, $workflowEventVersion, $contentClassAttributeID, $contentClassID )
    {
        $row = array( "id" => null,
                      "workflow_event_id" => $workflowEventID,
                      "workflow_event_version" => $workflowEventVersion,
                      "contentclass_id" => $contentClassID,
                      "contentclass_attribute_id" => $contentClassAttributeID
                      );
        return new eZWaitUntilDateValue( $row );
    }

    function createCopy( $id, $workflowEventID, $workflowEventVersion,  $contentClassID , $contentClassAttributeID )
    {
        $row = array( "id" => $id,
                      "workflow_event_id" => $workflowEventID,
                      "workflow_event_version" => $workflowEventVersion,
                      "contentclass_id" => $contentClassID,
                      "contentclass_attribute_id" => $contentClassAttributeID );
        return new eZWaitUntilDateValue( $row );
    }


    function removeAllElements( $workflowEventID, $version )
    {
        eZPersistentObject::removeObject( eZWaitUntilDateValue::definition(),
                                          array( "workflow_event_id" => $workflowEventID,
                                                 "workflow_event_version" => $version) );
    }

    function remove( $id , $version )
    {
        eZPersistentObject::removeObject( eZWaitUntilDateValue::definition(),
                                          array( "id" => $id,
                                                 "workflow_event_version" => $version) );
    }

    function &fetch( $id, $version, $asObject = true )
    {
        $object = eZPersistentObject::fetchObject( eZWaitUntilDateValue::definition(),
                                                    null,
                                                    array( "id" => $id,
                                                           "workflow_event_version" => $version),
                                                    $asObject );
        return $object;
    }

    function &fetchAllElements( $workflowEventID, $version, $asObject = true )
    {
        $objectList = eZPersistentObject::fetchObjectList( eZWaitUntilDateValue::definition(),
                                                            null,
                                                            array( "workflow_event_id" => $workflowEventID,
                                                                   "workflow_event_version" => $version ),
                                                            null,
                                                            null,
                                                            $asObject );
        return $objectList;
    }

    var $ClassName;
    var $ClassAttributeName;
}

?>

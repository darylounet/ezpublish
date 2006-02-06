<?php
//
// Created on: <17-Apr-2002 11:05:08 amos>
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

$Module = array( "name" => "eZContentClass" );

$ViewList = array();
$ViewList["edit"] = array(
    "script" => "edit.php",
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "ClassID", "GroupID", "GroupName" ) );
$ViewList["view"] = array(
    "script" => "view.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "ClassID" ) );
$ViewList["copy"] = array(
    "script" => "copy.php",
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "ClassID" ) );
$ViewList["down"] = array(
    "script" => "edit.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "ClassID", "AttributeID" ) );
$ViewList["up"] = array(
    "script" => "edit.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "ClassID", "AttributeID" ) );
$ViewList["removeclass"] = array(
    "script" => "removeclass.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "GroupID" ) );
$ViewList["removegroup"] = array(
    "script" => "removegroup.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array() );
$ViewList["classlist"] = array(
    "script" => "classlist.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "GroupID" ) );
$ViewList["grouplist"] = array(
    "script" => "grouplist.php",
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array() );
$ViewList["groupedit"] = array(
    "script" => "groupedit.php",
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezsetupnavigationpart',
    "params" => array( "GroupID" ) );
?>

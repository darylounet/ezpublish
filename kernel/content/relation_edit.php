<?php
//
// Created on: <17-Apr-2002 10:34:48 bf>
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

include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'kernel/classes/ezcontentclassattribute.php' );

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentobjectversion.php' );
include_once( 'kernel/classes/ezcontentobjectattribute.php' );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
include_once( 'kernel/classes/ezcontentbrowse.php' );

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( 'lib/ezutils/classes/ezhttptool.php' );

include_once( 'kernel/common/template.php' );

function checkRelationAssignments( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage, $fromLanguage, &$validation )
{
    $http =& eZHTTPTool::instance();
    // Add object relations
//     if ( $module->isCurrentAction( 'AddRelatedObject' ) )
    if ( $module->isCurrentAction( 'AddRelatedObject' ) )
    {
        $selectedObjectIDArray = eZContentBrowse::result( 'AddRelatedObject' );
        $relatedObjects =& $object->relatedContentObjectArray( $editVersion );
        $relatedObjectIDArray = array();
        $objectID = $object->attribute( 'id' );

        foreach (  $relatedObjects as  $relatedObject )
            $relatedObjectIDArray[] = $relatedObject->attribute( 'id' );

        foreach ( $selectedObjectIDArray as $selectedObjectID )
        {
            if ( $selectedObjectID != $objectID && !in_array( $selectedObjectID, $relatedObjectIDArray ) )
                $object->addContentObjectRelation( $selectedObjectID, $editVersion );
        }
        $module->redirectToView( 'edit', array( $object->attribute( 'id' ), $editVersion, $editLanguage, $fromLanguage ),
                                 null, false, 'content-relation-items' );
        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
    }
    if ( $module->isCurrentAction( 'UploadedFileRelation' ) )
    {
        include_once( 'kernel/classes/ezcontentupload.php' );
        $relatedObjectID = eZContentUpload::result( 'RelatedObjectUpload' );
        if ( $relatedObjectID )
        {
            $object->addContentObjectRelation( $relatedObjectID, $editVersion );
        }
        // We redirect to the edit page to get the correct url,
        // also we use the anchor 'content-relation-items' to make sure the
        // browser scrolls down the relation list (if the anchor exists).
        $module->redirectToView( 'edit', array( $object->attribute( 'id' ), $editVersion, $editLanguage, $fromLanguage ),
                                 null, false, 'content-relation-items' );
        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
    }
}

function storeRelationAssignments( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage )
{
}

function checkRelationActions( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage, $fromLanguage )
{
    $http =& eZHTTPTool::instance();
    if ( $module->isCurrentAction( 'BrowseForObjects' ) )
    {
        $objectID = $object->attribute( 'id' );

        $assignedNodes =& $object->attribute( 'assigned_nodes' );
        $assignedNodesIDs = array();
        foreach ( $assignedNodes as $node )
            $assignedNodesIDs = $node->attribute( 'node_id' );
        unset( $assignedNodes );

        eZContentBrowse::browse( array( 'action_name' => 'AddRelatedObject',
                                        'description_template' => 'design:content/browse_related.tpl',
                                        'content' => array( 'object_id' => $objectID,
                                                            'object_version' => $editVersion,
                                                            'object_language' => $editLanguage ),
                                        'keys' => array( 'class' => $class->attribute( 'id' ),
                                                         'class_id' => $class->attribute( 'identifier' ),
                                                         'classgroup' => $class->attribute( 'ingroup_id_list' ),
                                                         'section' => $object->attribute( 'section_id' ) ),
                                        'ignore_nodes_select' => $assignedNodesIDs,
                                        'from_page' => $module->redirectionURI( 'content', 'edit', array( $objectID, $editVersion, $editLanguage, $fromLanguage ) ) ),
                                 $module );

        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
    }
    if ( $module->isCurrentAction( 'UploadFileRelation' ) )
    {
        $objectID = $object->attribute( 'id' );

        include_once( 'kernel/classes/ezsection.php' );
        $section = eZSection::fetch( $object->attribute( 'section_id' ) );
        $navigationPart = false;
        if ( $section )
            $navigationPart = $section->attribute( 'navigation_part_identifier' );

        include_once( 'kernel/classes/ezcontentupload.php' );
        $location = false;
        if ( $module->hasActionParameter( 'UploadRelationLocation' ) )
        {
            $location = $module->actionParameter( 'UploadRelationLocation' );
        }

        include_once( 'lib/ezutils/classes/ezhttpfile.php' );
        // We only do direct uploading if we have the uploaded HTTP file
        // if not we need to go to the content/upload page.
        if ( eZHTTPFile::canFetch( 'UploadRelationFile' ) )
        {
            $upload = new eZContentUpload();
            if ( $upload->handleUpload( $result, 'UploadRelationFile', $location, false ) )
            {
                $relatedObjectID = $result['contentobject_id'];
                if ( $relatedObjectID )
                {
                    $object->addContentObjectRelation( $relatedObjectID, $editVersion );
                }
            }
        }
        else
        {
            eZContentUpload::upload( array( 'action_name' => 'RelatedObjectUpload',
                                            'description_template' => 'design:content/upload_related.tpl',
                                            'navigation_part_identifier' => $navigationPart,
                                            'content' => array( 'object_id' => $objectID,
                                                                'object_version' => $editVersion,
                                                                'object_language' => $editLanguage ),
                                            'keys' => array( 'class' => $class->attribute( 'id' ),
                                                             'class_id' => $class->attribute( 'identifier' ),
                                                             'classgroup' => $class->attribute( 'ingroup_id_list' ),
                                                             'section' => $object->attribute( 'section_id' ) ),
                                            'result_action_name' => 'UploadedFileRelation',
                                            'ui_context' => 'edit',
                                            'result_module' => array( 'content', 'edit',
                                                                      array( $objectID, $editVersion, $editLanguage, $fromLanguage ) ) ),
                                     $module );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
    }
    if ( $module->isCurrentAction( 'DeleteRelation' ) )
    {
        $objectID = $object->attribute( 'id' );
        if ( $http->hasPostVariable( 'DeleteRelationIDArray' ) )
        {
            $relationObjectIDs = $http->postVariable( 'DeleteRelationIDArray' );
        }

        $db =& eZDB::instance();
        $db->begin();
        foreach ( $relationObjectIDs as $relationObjectID )
        {
            $object->removeContentObjectRelation( $relationObjectID, $editVersion );
        }
        $db->commit();

    }
    if ( $module->isCurrentAction( 'NewObject' ) )
    {
        if ( $http->hasPostVariable( 'ClassID' ) )
        {
            include_once( 'kernel/classes/ezcontentobjectassignmenthandler.php' );
            $user =& eZUser::currentUser();
            $userID =& $user->attribute( 'contentobject_id' );
            if ( $http->hasPostVariable( 'SectionID' ) )
            {
                $sectionID = $http->postVariable( 'SectionID' );
            }
            else
            {
                $sectionID = 0; /* Will be changed later */
            }
            $contentClassID = $http->postVariable( 'ClassID' );
            $class = eZContentClass::fetch( $contentClassID );
            $relatedContentObject =& $class->instantiate( $userID, $sectionID );
            $newObjectID = $relatedContentObject->attribute( 'id' );
            $relatedContentVersion =& $relatedContentObject->attribute( 'current' );

            if ( $relatedContentObject->attribute( 'can_edit' ) )
            {
                $assignmentHandler = new eZContentObjectAssignmentHandler( $relatedContentObject, $relatedContentVersion );
                $sectionID = (int) $assignmentHandler->setupAssignments( array( 'group-name' => 'RelationAssignmentSettings',
                                                                   'default-variable-name' => 'DefaultAssignment',
                                                                   'specific-variable-name' => 'ClassSpecificAssignment',
                                                                   'section-id-wanted' => true,
                                                                   'fallback-node-id' => $object->attribute( 'main_node_id' ) ) );

                $http->setSessionVariable( 'ParentObject', array( $object->attribute( 'id' ), $editVersion, $editLanguage ) );
                $http->setSessionVariable( 'NewObjectID', $newObjectID );

                /* Change section ID to the same one as the main node placement */
                $db =& eZDB::instance();
                $db->query("UPDATE ezcontentobject SET section_id = {$sectionID} WHERE id = {$newObjectID}");

                $module->redirectToView( 'edit', array( $relatedContentObject->attribute( 'id' ),
                                                        $relatedContentObject->attribute( 'current_version' ),
                                                        false ) );
            }
            else
            {
                $relatedContentObject->purge();
            }

            return;
        }
    }
}

function handleRelationTemplate( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage, &$tpl )
{
    $relatedObjects =& $object->relatedContentObjectArray( $editVersion );
    $tpl->setVariable( 'related_contentobjects', $relatedObjects );

    $ini =& eZINI::instance( 'content.ini' );

    $groups = $ini->variable( 'RelationGroupSettings', 'Groups' );
    $defaultGroup = $ini->variable( 'RelationGroupSettings', 'DefaultGroup' );

    $groupedRelatedObjects = array();
    $groupClassLists = array();
    $classGroupMap = array();
    foreach ( $groups as $groupName )
    {
        $groupedRelatedObjects[$groupName] = array();
        $setting = strtoupper( $groupName[0] ) . substr( $groupName, 1 ) . 'ClassList';
        $groupClassLists[$groupName] = $ini->variable( 'RelationGroupSettings', $setting );
        foreach ( $groupClassLists[$groupName] as $classIdentifier )
        {
            $classGroupMap[$classIdentifier] = $groupName;
        }
    }
    $groupedRelatedObjects[$defaultGroup] = array();

    foreach ( $relatedObjects as $relatedObjectKey => $relatedObject )
    {
        $classIdentifier = $relatedObject->attribute( 'class_identifier' );
        if ( isset( $classGroupMap[$classIdentifier] ) )
        {
            $groupName = $classGroupMap[$classIdentifier];
            $groupedRelatedObjects[$groupName][] =& $relatedObjects[$relatedObjectKey];
        }
        else
        {
            $groupedRelatedObjects[$defaultGroup][] =& $relatedObjects[$relatedObjectKey];
        }
    }
    $tpl->setVariable( 'related_contentobjects', $relatedObjects );
    $tpl->setVariable( 'grouped_related_contentobjects', $groupedRelatedObjects );
}

function initializeRelationEdit( &$module )
{
    $module->addHook( 'post_fetch', 'checkRelationAssignments' );
    $module->addHook( 'pre_commit', 'storeRelationAssignments' );
    $module->addHook( 'action_check', 'checkRelationActions' );
    $module->addHook( 'pre_template', 'handleRelationTemplate' );
}

?>

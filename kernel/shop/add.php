<?php
//
// Created on: <04-Mar-2005 13:45:19 jhe>
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

include_once( "kernel/classes/ezbasket.php" );
include_once( 'lib/ezutils/classes/ezoperationhandler.php' );

$http = eZHttpTool::instance();
$basket = eZBasket::currentBasket();
$module =& $Params["Module"];

// Verify the ObjectID input
if ( !is_numeric( $ObjectID ) )
    return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

// Check if the object exists on disc
if ( !eZContentObject::exists( $ObjectID ) )
    return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

// Check if the user can read the object
$object =& eZContentObject::fetch( $ObjectID );
if ( !$object->canRead() )
    return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array( 'AccessList' => $object->accessList( 'read' ) ) );

// Check if the object has a price datatype, if not it cannot be used in the basket
include_once( 'kernel/shop/classes/ezshopfunctions.php' );

if ( !eZShopFunctions::isProductObject( $object ) )
{
    include_once( 'kernel/shop/errors.php' );
    return $Module->handleError( EZ_ERROR_SHOP_NOT_A_PRODUCT, 'shop' );
}


$OptionList = $http->sessionVariable( "AddToBasket_OptionList_" . $ObjectID );

$operationResult = eZOperationHandler::execute( 'shop', 'addtobasket', array( 'basket_id' => $basket->attribute( 'id' ),
                                                                              'object_id' => $ObjectID,
                                                                              'option_list' => $OptionList ) );

switch( $operationResult['status'] )
{
    case EZ_MODULE_OPERATION_HALTED:
    {
        if ( isset( $operationResult['redirect_url'] ) )
        {
            $module->redirectTo( $operationResult['redirect_url'] );
            return;
        }
        else if ( isset( $operationResult['result'] ) )
        {
            $result =& $operationResult['result'];
            $resultContent = false;
            if ( is_array( $result ) )
            {
                if ( isset( $result['content'] ) )
                    $resultContent = $result['content'];
                if ( isset( $result['path'] ) )
                    $Result['path'] = $result['path'];
            }
            else
                $resultContent =& $result;
            $Result['content'] =& $resultContent;
            return $Result;
       }
    }break;
}


$ini = eZINI::instance();
if ( $ini->variable( 'ShopSettings', 'RedirectAfterAddToBasket' ) == 'reload' )
    $module->redirectTo( $http->sessionVariable( "FromPage" ) );
else
    $module->redirectTo( "/shop/basket/" );

?>

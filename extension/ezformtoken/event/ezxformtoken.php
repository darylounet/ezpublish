<?php
/**
 * File containing the ezxFormToken class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package ezformtoken
 */

/**
 * This class listens to interal kernel events in eZ Publish to validate forms using pr session tokens
 *
 * @See settings/site.ini.append.php for events used.
 * @See doc/Readme.rst for info about extension and about how to modify your ajax code to work with it.
 *
 * @internal
 * @since 4.5.0
 * @version //autogentag//
 * @package ezformtoken
 */
class ezxFormToken
{
    const SESSION_KEY = __CLASS__;

    const FORM_FIELD = 'ezxform_token';

    const REPLACE_KEY = '@$ezxFormToken@';

    /**
     * request/input event listener
     * Checks if form token is valid if user is logged in.
     *
     * @param eZURI $uri
     */
    static public function input( eZURI $uri )
    {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' && empty( $_POST ) )
        {
            eZDebugSetting::writeDebug( 'ezformtoken', 'Input not protected (not POST)', __METHOD__ );
            return null;
        }

        if ( !self::shouldProtectUser() )
        {
            eZDebugSetting::writeDebug( 'ezformtoken', 'Input not protected (not logged in user)', __METHOD__ );
            return null;
        }

        /* Not a safe assumtion, just kept for reference
        if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] )
          && trim( strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) === 'xmlhttprequest' )
        {
            eZDebugSetting::writeDebug( 'ezformtoken', 'Input not protected (ajax request)', __METHOD__ );
            return null;
        }*/

        if ( !empty( $_POST[self::FORM_FIELD] ) )
        {
            $token = $_POST[self::FORM_FIELD];
        }
        // allow ajax calls using POST with other formats than forms (such as
        // json or xml) to still validate using a custom http header
        else if ( !empty( $_SERVER['HTTP_X_CSRF_TOKEN'] ) )
        {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        else
        {
            throw new Exception( 'Missing form token from Request', 404 );
        }

        if ( $token !== self::getToken() )
            throw new Exception( 'Wrong form token found in Request!', 404 );

        eZDebugSetting::writeDebug( 'ezformtoken', 'Input validated, token verified and was correct', __METHOD__ );
    }

    /**
     * response/output event filter
     * Appends tokens to  POST forms if user is logged in.
     *
     * @param string $templateResult ByRef
     */
    static public function output( $templateResult )
    {
        if ( !self::shouldProtectUser() )
        {
            eZDebugSetting::writeDebug( 'ezformtoken', 'Output not protected (not logged in user)', __METHOD__ );
            return $templateResult;
        }

        // We only rewrite pages served with an html/xhtml content type
        $sentHeaders = headers_list();
        foreach ( $sentHeaders as $header )
        {
            // Search for a content-type header that is NOT HTML
            // Note the Content-Type header will not be included in
            // headers_list() unless it has been explicitly set from PHP.
            if (stripos( $header, 'Content-Type:' ) === 0 &&
                strpos( $header, 'text/html' ) === false &&
                strpos( $header, 'application/xhtml+xml' ) === false   )
           {
               eZDebugSetting::writeDebug( 'ezformtoken', 'Output not protected (Content-Type is not html/xhtml)', __METHOD__ );
               return $templateResult;
            }
        }

        $token = self::getToken();
        $field = self::FORM_FIELD;
        $replaceKey = self::REPLACE_KEY;

        eZDebugSetting::writeDebug( 'ezformtoken', 'Output protected (all forms will be modified)', __METHOD__ );

        // If document has head tag, insert in a html5 valid and semi standard way
        if ( strpos( $templateResult, '<head>' ) !== false )
        {
            $templateResult = str_replace(
                '<head>',
                "<head>\n"
                . "<meta name=\"csrf-param\" content=\"{$field}\" />\n"
                . "<meta name=\"csrf-token\" id=\"{$field}_js\" title=\"{$token}\" content=\"{$token}\" />\n",
                $templateResult
            );
        }
        // else fallback to hidden span inside body
        else
        {
            $templateResult = preg_replace(
                '/(<body[^>]*>)/i',
                '\\1' . "\n<span style='display:none;' id=\"{$field}_js\" title=\"{$token}\"></span>\n",
                $templateResult
            );
        }

        $templateResult = preg_replace(
            '/(<form\W[^>]*\bmethod=(\'|"|)POST(\'|"|)\b[^>]*>)/i',
            '\\1' . "\n<input type=\"hidden\" name=\"{$field}\" value=\"{$token}\" />\n",
            $templateResult
        );

        return str_replace( $replaceKey, $token, $templateResult );
    }

    /**
     * session/regenerate event handler, clears form token when users
     * logs out / in.
     */
    static public function reset()
    {
        eZDebugSetting::writeDebug( 'ezformtoken', 'Reset form token', __METHOD__ );
        eZSession::unsetkey( self::SESSION_KEY, false );
    }

    /**
     * Gets the user token from session if it exists or create+store
     * it in session.
     *
     * @return string|null
     */
    static public function getToken()
    {
        if ( eZSession::issetkey( self::SESSION_KEY ) )
            return eZSession::get( self::SESSION_KEY );

        $token = md5( uniqid( self::SESSION_KEY, true ) );
        eZSession::set( self::SESSION_KEY, $token );
        return $token;
    }

    /**
     * Figures out if current user should be protected or not
     * based on if (s)he has a session and is logged in.
     *
     * @return bool
     */
    static protected function shouldProtectUser()
    {
        if ( !eZSession::hasStarted() )
            return false;

        if ( !eZUser::currentUser()->isLoggedIn() )
            return false;

        return true;
    }
}

?>

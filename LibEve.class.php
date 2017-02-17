<?php

/**
 * LICENSE:
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibEve
 * @file       LibEve.lib.php
 * @author     Justin Belec
 * @copyright  2014 Justin Belec
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU General Public License v.3
 * @version    0.3.0 [ALPHA]
 * @link       https://sourceforge.net/p/evetools/
 * @abstract
 *
 * @todo
 *  - Split class into multiple inherited subclasses.
 */
abstract class LibEve {

    /*
     * Class variables
     */

    // API Key Vars
    private $keyID;
    private $vCode;

    /*
     * Class constants
     */
    const SERVER_ROOT = "https://api.eveonline.com";

    const SERVER_DOMAIN = "api.eveonline.com";

    const SERVER_PORT = "443";

    const CONNECTION_TIMEOUT = 2;

    const QUERY_TIMEOUT = 4;

    const DEBUG = true;

    /*
     * Class constructors
     */

    /**
     * Constructor
     *
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final function __construct() {

        self::setKeyID( 0 );
        self::setVCode( "" );
    }

    /*
     * Class helpers
     */

    /**
     * Initialise api key.
     *
     * @param integer $keyID
     * @param string $vCode
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final public function setupAPI( $keyID, $vCode ) {

        self::setKeyID( $keyID );
        self::setVCode( $vCode );
    }

    /*
     * Class Observers
     */

    /**
     * Returns keyID.
     *
     * @return integer
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final public function getKeyID() {

        return $this->keyID;
    }

    /**
     * Returns vCode.
     *
     * @return string
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final public function getVCode() {

        return $this->vCode;
    }

    /*
     * Class Mutators
     */

    /**
     * Sets keyID.
     *
     * @param integer $keyID
     * @throws LibEveException
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final public function setKeyID( $keyID ) {

        if( is_int( $keyID ) === true )
            $this->keyID = $keyID;
        else
            throw new LibEveException( "Type missmatch. Must be integer.", 1001 );
    }

    /**
     * Sets vCode.
     *
     * @param string $vCode
     * @throws LibEveException
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final public function setVCode( $vCode ) {

        if( is_string( $vCode ) === true )
            $this->vCode = $vCode;
        else
            throw new LibEveException( "Type missmatch. Must be string.", 1001 );
    }

    /*
     * Class Methods
     */

    /**
     * XML request method
     *
     * @param string $path
     *            Path to API element. Ex: /eve/CharacterID.xml.aspx
     * @param array $vars
     *            Array of post variables. ("key" => "value")
     * @throws LibEveException
     * @return SimpleXMLElement
     * @final
     *
     *
     *
     *
     *
     *
     *
     */
    final public function xmlRequest( $path, $vars = array() ) {

        // Instance variables
        $params = array();
        $paramsString = "";
        $result;

        libxml_use_internal_errors( true );

        // Load API key parameters
        // Will use credentials if instantiated with them
        if( $this->getKeyID() !== 0 || $this->getVCode() !== "" ) {
            $params = array(
                    "keyID" => $this->getKeyID(),
                    "vCode" => $this->getVCode() );
        }

        // Append the $vars array to the $params array if it exists
        $params = ( isset( $vars ) ) ? ( ( is_array( $vars ) ) ? $params + $vars : $params ) : $params;

        // Turn the array of params into a string and encode the values
        foreach( $params as $key => $value ) {
            $paramsString .= $key . "=" . urlencode( $value ) . "&";
        }

        // Remove any pesky ampersands at the end of the string
        rtrim( $paramsString, "&" );

        // Initialise curl
        $ch = curl_init();

        // Set curl options
        curl_setopt( $ch, CURLOPT_URL, self::SERVER_ROOT . $path );
        curl_setopt( $ch, CURLOPT_POST, count( $params ) );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $paramsString );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::QUERY_TIMEOUT );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        // Execute query
        $curlResult = curl_exec( $ch );
        $curlError = curl_error( $ch );

        // Close curl
        curl_close( $ch );

        if( $curlResult !== false ) {
            try {
                // Turn the result text into a simpleXML object
                $resultXML = new SimpleXMLElement( $curlResult );
            } catch( Exception $e ) {
                foreach( libxml_get_errors() as $error ) {
                    $details[] = $error->message;
                }
                $message = "SimpleXML parse error!\n";
                if( self::DEBUG === true ) $message .= print_r( $details, true ) . print_r( $e->getTrace(), true ) . "\n" . $curlResult;
                throw new LibEveException( $message, 1004 );
            }

            // Check to make sure our result wasn't an error
            foreach( $resultXML->children() as $row ) {
                if( $row->getName() == "error" ) {
                    // API returned an error
                    $message = "API Error: " . $row . "\n";
                    if( self::DEBUG === true ) $message .= $curlResult;
                    throw new LibEveException( $message, 1003 );
                }
            }
            $result = $resultXML;
        } else {
            // Some error happened where the request was not recieved
            throw new LibEveException( "cURL error: " . $curlError, 1005 );
        }

        // Return the object
        return $result;
    }
}
?>
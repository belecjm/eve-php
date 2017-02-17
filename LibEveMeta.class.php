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
 * @file       LibEveMeta.lib.php
 * @author     Justin Belec
 * @copyright  2014 Justin Belec
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU General Public License v.3
 * @version    0.3.0 [ALPHA]
 * @link       https://sourceforge.net/p/evetools/
 *
 * @todo
 *  - Fix eveGetIDByName()
 *  - Fix eveGetNameByID()
 *  - Fix eveGetChatInfo()
 *  - Implement AllianceList
 *  - Implement CeertificateTree
 *  - Implement ConquerableStationList
 *  - Implement ErrorList
 *  - Implement FactionalWarfareStatistics
 *  - Implement FactionalWarfareTopStats
 *  - Implement ReferenceTypes
 *  - Implement SkillTree
 *  - Implement Portraits
 *  - Implement ServerStatus
 */
class LibEveMeta extends LibEve {

    /**
     * @todo stub
     *      - Implement me!
     *
     * eveGetIDByName
     *      Entity Lookup By Name
     *
     * @return multitype:multitype:string
     */
    public function eveGetIDByName() {

        /* Instance variables */
        $result = array();
        $argList = func_get_args();
        // Comma seperates list of character / corp / alliance names
        $paramList = implode( ",", $argList );
        $params = array(
                "names" => $paramList );
        $response = parent::xmlRequest( "/eve/CharacterID.xml.aspx", $params );

        // Builds output array
        foreach( $response->result->rowset->row as $row ) {
            $result[] = array(
                    "name" => (string) $row["name"],
                    "characterID" => (string) $row["characterID"] );
        }

        // Return output
        return $result;
    }

    /**
     * @todo stub
     *      - Implement me!
     *
     * eveGetNameByID
     *      Entity Lookup By ID
     *
     * @param integer $ids
     *      Multi-args list of ids.
     * @return array
     */
    public function eveGetNameByID() {

        /* Instance variables */
        $result = array();
        $argList = func_get_args();
        // Comma seperates list of character / corp / alliance ids
        $paramList = implode( ",", $argList );
        $params = array(
                "IDs" => $paramList );
        $response = parent::xmlRequest( "/eve/CharacterName.xml.aspx", $params, true );

        // Builds output array
        foreach( $response->result->rowset->row as $row ) {
            $result[] = array(
                    "name" => (string) $row["name"],
                    "characterID" => (string) $row["characterID"] );
        }

        // Return output
        return $result;
    }

    // Character Detailed Information Lookup
    public function eveGetCharInfo( $charID ) {

        $output = null;
        $result = null;
        $params = array(
                'characterID' => $charID );
        $APIResponse = self::xmlRequest( '/eve/CharacterInfo.xml.aspx', $params, true );
        $response = ( isset( $APIResponse->output ) ) ? $APIResponse->output : false;
        $err = ( isset( $APIResponse->err ) ) ? $APIResponse->err : false;

        if( $response == false ) { // Something failed
                                   // API request returned an error
            $err[] = array(
                    'errlevel' => 'E_WARNING',
                    'errstr' => 'API request failure. Check other possible errors for more information.',
                    'errcode' => 1004 );
            $result = false;
        } else {
            $result[] = array( // Builds output array
                    'characterID' => (string) $response->result->characterID,
                    'characterName' => (string) $response->result->characterName,
                    'race' => (string) $response->result->race,
                    'bloodline' => (string) $response->result->bloodline,
                    'corporationID' => (string) $response->result->corporationID,
                    'corporation' => (string) $response->result->corporation,
                    'corporationDate' => (string) $response->result->corporationDate,
                    'allianceID' => (string) $response->result->allianceID,
                    'alliance' => (string) $response->result->alliance,
                    'allianceDate' => (string) $response->result->allianceDate,
                    'securityStatus' => (string) $response->result->securityStatus );
        }

        // Returns output
        $output->output = ( isset( $result ) ) ? $result : false;
        $output->err = ( isset( $err ) ) ? $err : false;
        return $output; // Returns whatever made it through if the query was
                            // successful
    }

    // IGB headers
    public function igbGetHeader( $query ) {

        $header = '';
        $output = null;
        $result = null;
        $err = null;

        switch($query) {
            case 'trusted' :
                $header = $_SERVER['HTTP_EVE_TRUSTED'];
                if( isset( $header ) ) {
                    $result = ( ( $header == 'Yes' ) ? true : false );
                } else {
                    $result = 'NO_IGB';
                }
                break;
            case 'serverip' :
                $header = $_SERVER['HTTP_EVE_SERVERIP'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'charname' :
                $header = $_SERVER['HTTP_EVE_CHARNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'charid' :
                $header = $_SERVER['HTTP_EVE_CHARID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'corpname' :
                $header = $_SERVER['HTTP_EVE_CORPNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'corpid' :
                $header = $_SERVER['HTTP_EVE_CORPID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'alliancename' :
                $header = $_SERVER['HTTP_EVE_ALLIANCENAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) || $header == 'None' ) ? false : $header;
                    break;
                }
            case 'allianceid' :
                $header = $_SERVER['HTTP_EVE_ALLIANCEID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) || $header == 'None' ) ? false : $header;
                    break;
                }
            case 'regionname' :
                $header = $_SERVER['HTTP_EVE_REGIONNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'constellationname' :
                $header = $_SERVER['HTTP_EVE_CONSTELLATIONNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'solarsystemname' :
                $header = $_SERVER['HTTP_EVE_SOLARSYSTEMNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'stationname' :
                $header = $_SERVER['HTTP_EVE_STATIONNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) || $header == 'None' ) ? false : $header;
                    break;
                }
            case 'corprole' :
                $header = $_SERVER['HTTP_EVE_CORPROLE'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) || $header == '0' ) ? false : $header;
                    break;
                }
            case 'solarsystemid' :
                $header = $_SERVER['HTTP_EVE_SOLARSYSTEMID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'warfactionid' :
                $header = $_SERVER['HTTP_EVE_WARFACTIONID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) || $header == 'None' ) ? false : $header;
                    break;
                }
            case 'shipid' :
                $header = $_SERVER['HTTP_EVE_SHIPID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'shipname' :
                $header = $_SERVER['HTTP_EVE_SHIPNAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'shiptypeid' :
                $header = $_SERVER['HTTP_EVE_SHIPTYPEID'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            case 'shiptypename' :
                $header = $_SERVER['HTTP_EVE_SHIPTYPENAME'];
                if( isset( $header ) ) {
                    $result = ( empty( $header ) ) ? false : $header;
                    break;
                }
            default :
                $result = false;
        }
        $output->output = ( isset( $result ) ) ? $result : false;
        $output->err = ( isset( $err ) ) ? $err : false;
        return $output; // Returns whatever made it through if the query was
                            // successful
    }
}
?>
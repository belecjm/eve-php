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
 * @file       LibEveAccount.php
 * @author     Justin Belec
 * @copyright  2014 Justin Belec
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU General Public License v.3
 * @version    0.3.0 [ALPHA]
 * @link       https://sourceforge.net/p/evetools/
 *
 * @todo
 */
class LibEveAccount extends LibEve {

    /**
     * Bitmask comparison - FIXME
     *
     * @param integer $bitmask
     * @return Array
     */
    public function apiAccessMask( $bitmask ) {

        /* Instance variables */
        $output = array();
        $result = array();

        // Query API Server
        $response = parent::xmlRequest( "/api/callList.xml.aspx" );

        $ii = 0;

        // Seperate the groups of rows
        foreach( $response->result->rowset as $cRow ) {
            // Query returns two groups of rows. Seperate them to get values.
            if( $cRow["name"] == "callGroups" ) {
                // Group types of bitmasks
                foreach( $cRow as $value ) {
                    $result["callGroups"][] = array(
                            "groupID" => (integer) $value["groupID"],
                            "name" => (string) $value["name"],
                            "description" => (string) $value["description"] );
                }
            } else if( $cRow["name"] == "calls" ) {
                // Bitmasks
                $i = 0;
                foreach( $cRow as $value ) {
                    $result["calls"][] = array(
                            "accessMask" => (integer) $value["accessMask"],
                            "type" => (string) $value["type"],
                            "name" => (string) $value["name"],
                            "groupID" => (integer) $value["groupID"],
                            "description" => (string) $value["description"] );
                }
            }
            $ii++;
        }

        /*
         * If the user supplied a bitmask check it against the character
         * bitmasks since there are more character bitmasks than corp. Thus if a
         * corp bitmask is supplied it will still work.
         */
        if( $bitmask !== 0 ) {
            foreach( $result["calls"] as $key => $value ) {
                // Limit it to character so we don't get double results
                if( $value["type"] == "Character" ) {
                    // Reduce both inputs to their binary equivilant and check
                    // them against eachother for set bits
                    if( ( intval( $bitmask ) & intval( $value["accessMask"] ) ) != false ) {
                        // Bitmask does match
                        $result["state"][$value["accessMask"]] = (boolean) true;
                    } else {
                        $result["state"][$value["accessMask"]] = (boolean) false;
                    }
                }
            }
            // Include the input bitmask if available
            $result["bitmask"] = $bitmask;
        }

        // Return output
        return $result;
    }

    /**
     * Account Characters - FIXME
     *
     * @return Array
     */
    public function acctChars() {

        /* Instance variables */
        $result = array();

        // Query API server
        $response = parent::xmlRequest( "/account/Characters.xml.aspx" );

        // Builds the output array
        foreach( $response->result->rowset->row as $value ) {
            $result[] = array(
                    "name" => (string) $value["name"],
                    "characterID" => (integer) $value["characterID"],
                    "corporationName" => (string) $value["corporationName"],
                    "corporationID" => (integer) $value["corporationID"] );
        }

        // Return output
        return $result;
    }

    /**
     * Account Status - FIXME - stub
     *
     * @param string $charID
     * @return Array
     */
    public function acctStatus( $charID = false ) {

        /* Instance variables */
        $params = array();
        $result = array();
        $characters = array();

        if( $charID === false ) {
            $characters = $this->acctChars();
            foreach( $characters as $value ) {
                $result = array_merge( $result, $this->acctStatus( $value["characterID"] ) );
            }
        } else {

            $params = array(
                    "characterID" => $charID );
            $response = parent::xmlRequest( "/account/AccountStatus.xml.aspx", $params );

            // Builds output array
            $result[] = array(
                    "characterID" => (integer) $charID,
                    "paidUntil" => (integer) strtotime( $response->result->paidUntil ),
                    "createDate" => (integer) strtotime( $response->result->createDate ),
                    "logonCount" => (integer) $response->result->logonCount,
                    "logonMinutes" => (integer) $response->result->logonMinutes );
        }

        // Return output
        return $result;
    }

    /**
     * Account API Key Info - FIXME
     *
     * @return Array
     */
    public function acctAPIKeyInfo() {

        /* Instance variables */
        $result = array();
        $response = parent::xmlRequest( "/account/apikeyinfo.xml.aspx" );

        $result["apiKey"] = array(
                "accessMask" => (integer) $response->result->key["accessMask"],
                "type" => (string) $response->result->key["type"],
                "expires" => (integer) strtotime( $response->result->key["expires"] ) );

        foreach( $response->result->key->rowset->row as $row ) {
            $result["characters"][] = array( // Builds the output array
                    "characterName" => (string) $row["characterName"],
                    "characterID" => (integer) $row["characterID"],
                    "corporationName" => (string) $row["corporationName"],
                    "corporationID" => (integer) $row["corporationID"] );
        }

        // Return output
        return $result;
    }
}
?>
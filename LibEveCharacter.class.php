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
 * @file       LibEveCharacter.php
 * @author     Justin Belec
 * @copyright  2014 Justin Belec
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU General Public License v.3
 * @version    0.3.0 [ALPHA]
 * @link       https://sourceforge.net/p/evetools/
 *
 * @todo
 *  - Implement AssetList
 *  - Implement CalendarEventAttendees
 *  - Implement ContactList
 *  - Implement ContactNotifications
 *  - Implement FactionalWarfareStatistics
 *  - Implement IndustryJobs
 *  - Implement KillLog
 *  - Implement MarketOrders
 *  - Implement Medals
 *  - Implement NotificationTexts
 *  - Implement Notifications
 *  - Implement NPCStandings
 *  - Implement Research
 *  - Implement SkillInTraining
 *  - Implement SkillQueue
 *  - Implement UpcomingCalendarEvents
 *  - Implement WalletJournal
 *  - Implement WalletTransactions
 */
class LibEveCharacter extends LibEveAccount {

    /**
     *
     * @todo stub
     *
     *       charAcctBalance
     *       Character wallet balance
     *
     * @param string $charID
     * @return array
     */
    public function charAcctBalance( $charID = false ) {

        /* Instance variables */
        $params = array();
        $result = array();
        $characters = array();

        if( $charID === false ) {

            $characters = parent::acctChars();
            foreach( $characters as $value ) {
                $result = array_merge( $result, $this->charAcctBalance( $value["characterID"] ) );
            }
        } else {
            $params = array(
                    "characterID" => $charID );
            $response = parent::xmlRequest( "/char/AccountBalance.xml.aspx", $params );

            $result[] = array( // Builds output array
                    "characterID" => (string) $charID,
                    "accountID" => (integer) $response->result->rowset->row["accountID"],
                    "accountKey" => (integer) $response->result->rowset->row["accountKey"],
                    "balance" => (double) $response->result->rowset->row["balance"] );
        }

        return $result;
    }

    /**
     *
     * @todo stub
     *
     * charMailMessages
     *       Mail Messages
     *
     * @param integer $charID
     * @return array:
     */
    public function charMailMessages( $charID ) {

        /* Instance variables */
        $params = array(
                "characterID" => $charID );
        $result = array();
        $response = parent::xmlRequest( "/char/MailMessages.xml.aspx", $params );
        $toCharacterIDs = array();
        $toListID = array();
        $toCorpOrAllianceID = array();

        // Builds the output array
        foreach( $response->result->rowset->row as $row ) {
            $toCharacterIDs = array();
            $toListID = array();
            $toCorpOrAllianceID = array();

            if( !empty( $row["toCorpOrAllianceID"] ) ) $toCorpOrAllianceID = explode( ",", (string) $row["toCorpOrAllianceID"] );
            if( !empty( $row["toCharacterIDs"] ) ) $toCharacterIDs = explode( ",", (string) $row["toCharacterIDs"] );
            if( !empty( $row["toListID"] ) ) $toListID = explode( ",", (string) $row["toListID"] );

            $result[] = array(
                    'messageID' => (integer) $row['messageID'],
                    'senderID' => (integer) $row['senderID'],
                    'sentDate' => (integer) strtotime( $row['sentDate'] ),
                    'title' => (string) $row['title'],
                    'toCorpOrAllianceID' => $toCorpOrAllianceID,
                    'toCharacterIDs' => $toCharacterIDs,
                    'toListID' => $toListID );
        }

        // Return output
        return $result;
    }

    /**
     *
     * @todo stub
     *       - Implement me
     *
     * charMailBodies
     *       Mail Message Bodies
     *
     * @param integer $charID
     * @param integer $mailID
     * @return array
     */
    public function charMailBodies( $charID, $messageID ) {

        /* Instance variables */
        $params = array(
                "characterID" => $charID,
                "ids" => $messageID );
        $result = array();
        $response = parent::xmlRequest( "/char/MailBodies.xml.aspx", $params );

        // Builds the output array
        foreach( $response->result->rowset->row as $row ) {
            $result[] = array(
                    "messageID" => (string) $row["messageID"],
                    "messageText" => (string) $row );
        }

        // Return output
        return $result;
    }

    /**
     * @todo stub
     *       - Implement me
     *
     * charMailingLists
     *       Mailing lists + search
     *
     * @param integer $charID
     * @param string $search
     * @return array
     */
    public function charMailingLists( $charID, $search = false ) {

        /* Instance variables */
        $params = array(
                "characterID" => $charID );
        $result = array();
        $response = parent::xmlRequest( "/char/mailinglists.xml.aspx", $params );

        // Builds the output array
        foreach( $response->result->rowset->row as $row ) {
            $result[] = array(
                    "listID" => (integer) $row["listID"],
                    "displayName" => (string) $row["displayName"] );
        }

        if( $search !== false ) {
            foreach( $result as $value ) {
                if( intval( $value["listID"] ) == intval( $search ) ) {
                    $result = array(
                            $value["displayName"] );
                    break;
                } else {
                    $result = array(
                            "false" );
                }
            }
        }

        // Return output
        return $result;
    }

    /**
     * @todo stub
     *       - Add features such as certificates that are left out
     *
     * charCharacterSheet
     *       Returns a character's character sheet.
     *
     * @param integer $charID
     * @return array
     */
    public function charCharacterSheet( $charID ) {

        /* Instance variables */
        $params = array(
                "characterID" => $charID );
        $result = array();
        $response = parent::xmlRequest( "/char/CharacterSheet.xml.aspx", $params );
        $skills = array();
        $certs = array();
        $corpRoles = array();

        // Loop through skills
        foreach( $response->result->rowset as $rowSet ) {

            if( $rowSet["name"] == "skills" ) {
                foreach( $rowSet->row as $rowSkills ) {
                    $skills[] = array(
                            "typeID" => (integer) $rowSkills["typeID"],
                            "skillpoints" => (integer) $rowSkills["skillpoints"],
                            "level" => (integer) $rowSkills["level"],
                            "published" => (boolean) $rowSkills["published"] );
                }
            }
        }

        $result = array(
                "characterID" => (integer) $response->result->characterID,
                "name" => (string) $response->result->name,
                "DoB" => (integer) strtotime( $response->result->DoB ),
                "race" => (string) $response->result->race,
                "bloodline" => (string) $response->result->bloodline,
                "ancestry" => (string) $response->result->ancestry,
                "gender" => (string) $response->result->gender,
                "corporationName" => (string) $response->result->corporationName,
                "corporationID" => (integer) $response->result->corporationID,
                // "factionName" => (string) $response->result->factionName,
                // "factionID" => (integer) $response->result->factionID,
                "cloneName" => (string) $response->result->cloneName,
                "cloneSkillPoints" => (integer) $response->result->cloneSkillPoints,
                "balance" => (integer) $response->result->balance,
                "attributeEnhancers" => array(
                        "memoryBonus" => array(
                                "augmentatorName" => (string) $response->result->attributeEnhancers->memoryBonus->augmentatorName,
                                "augmentatorValue" => (integer) $response->result->attributeEnhancers->memoryBonus->augmentatorValue ),
                        "willpowerBonus" => array(
                                "augmentatorName" => (string) $response->result->attributeEnhancers->willpowerBonus->augmentatorName,
                                "augmentatorValue" => (integer) $response->result->attributeEnhancers->willpowerBonus->augmentatorValue ),
                        "perceptionBonus" => array(
                                "augmentatorName" => (string) $response->result->attributeEnhancers->perceptionBonus->augmentatorName,
                                "augmentatorValue" => (integer) $response->result->attributeEnhancers->perceptionBonus->augmentatorValue ),
                        "intelligenceBonus" => array(
                                "augmentatorName" => (string) $response->result->attributeEnhancers->intelligenceBonus->augmentatorName,
                                "augmentatorValue" => (integer) $response->result->attributeEnhancers->intelligenceBonus->augmentatorValue ),
                        "charismaBonus" => array(
                                "augmentatorName" => (string) $response->result->attributeEnhancers->charismaBonus->augmentatorName,
                                "augmentatorValue" => (integer) $response->result->attributeEnhancers->charismaBonus->augmentatorValue ) ),
                "attributes" => array(
                        "intelligence" => (integer) $response->result->attributes->intelligence,
                        "memory" => (integer) $response->result->attributes->memory,
                        "charisma" => (integer) $response->result->attributes->charisma,
                        "perception" => (integer) $response->result->attributes->perception,
                        "willpower" => (integer) $response->result->attributes->willpower ),
                "skills" => $skills );


        return $result;
    }
}
?>
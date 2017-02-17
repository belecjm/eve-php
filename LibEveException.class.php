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
 * @file       LibEveException.lib.php
 * @author     Justin Belec
 * @copyright  2014 Justin Belec
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU General Public License v.3
 * @version    0.3.0 [ALPHA]
 * @link       https://sourceforge.net/p/evetools/
 *
 * @todo
 */
class LibEveException extends Exception {

    function __construct( $message = 'General Error', $code = 1001 ) {

        parent::__construct( $message, $code );
    }
}
?>
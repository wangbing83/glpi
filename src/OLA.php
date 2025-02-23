<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

/**
 * OLA Class
 * @since 9.2
**/
class OLA extends LevelAgreement
{
    protected static $prefix            = 'ola';
    protected static $prefixticket      = 'internal_';
    protected static $levelclass        = 'OlaLevel';
    protected static $levelticketclass  = 'OlaLevel_Ticket';
    protected static $forward_entity_to = ['OlaLevel'];

    public static function getTypeName($nb = 0)
    {
       // Acronymous, no plural
        return __('OLA');
    }

    public function showFormWarning()
    {
        global $CFG_GLPI;

        echo "<img src='" . $CFG_GLPI["root_doc"] . "/pics/warning.png' alt='" . __s('Warning') . "'>";
        echo __('The internal time is recalculated when assigning the OLA');
    }

    public function getAddConfirmation()
    {
        return [__("The assignment of an OLA to a ticket causes the recalculation of the date."),
              __("Escalations defined in the OLA will be triggered under this new date.")];
    }
}

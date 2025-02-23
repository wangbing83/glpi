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
 *  Database iterator class for Mysql
**/
class QueryParam
{
    private $value;

   /**
    * Create a query param with a value
    *
    * @param string $value Query parameter value, defaults to '?'
    */
    public function __construct($value = '?')
    {
        if ($value == null || trim($value) == '') {
            $value = '?';
        }
        if ($value != '?' && !str_starts_with($value, ':')) {
            $value = ':' . $value;
        }
        $this->value = $value;
    }

   /**
    * Query parameter value
    *
    * @return string
    */
    public function getValue()
    {
        return $this->value;
    }


    public function __toString()
    {
        return $this->getValue();
    }
}

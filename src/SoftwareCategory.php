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

/// Class SoftwareCategory
class SoftwareCategory extends CommonTreeDropdown
{
    public $can_be_translated = true;


    public static function getTypeName($nb = 0)
    {
        return _n('Software category', 'Software categories', $nb);
    }


    public function cleanDBonPurge()
    {
        Rule::cleanForItemAction($this);
    }


    public function cleanRelationData()
    {

        parent::cleanRelationData();

        if ($this->isUsedAsCategoryOnSoftwareDeletion()) {
            $newval = (isset($this->input['_replace_by']) ? $this->input['_replace_by'] : 0);

            Config::setConfigurationValues(
                'core',
                [
                'softwarecategories_id_ondelete' => $newval,
                ]
            );
        }
    }


    public function isUsed()
    {

        if (parent::isUsed()) {
            return true;
        }

        return $this->isUsedAsCategoryOnSoftwareDeletion();
    }


   /**
    * Check if type is used as category for software deleted by rules.
    *
    * @return boolean
    */
    private function isUsedAsCategoryOnSoftwareDeletion()
    {

        $config_values = Config::getConfigurationValues('core', ['softwarecategories_id_ondelete']);

        return array_key_exists('softwarecategories_id_ondelete', $config_values)
         && $config_values['softwarecategories_id_ondelete'] == $this->fields['id'];
    }

    public static function getIcon()
    {
        return Software::getIcon();
    }
}

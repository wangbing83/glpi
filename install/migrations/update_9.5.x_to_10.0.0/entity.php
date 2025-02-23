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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * @var DB $DB
 * @var Migration $migration
 */

$default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

/** Create registration_number field */
if (!$DB->fieldExists("glpi_entities", "registration_number")) {
    $migration->addField(
        "glpi_entities",
        "registration_number",
        "string",
        [
         'after'     => "ancestors_cache",
        ]
    );
}
/** /Create registration_number field */

/** Replace -1 value for entities_id field */
$DB->updateOrDie('glpi_entities', ['entities_id' => '0'], ['id' => '0']); // Replace -1 value for root entity to be able to change type to unsigned
$migration->changeField('glpi_entities', 'entities_id', 'entities_id', "int {$default_key_sign} DEFAULT '0'");
$migration->migrationOneTable('glpi_entities'); // Ensure 'entities_id' is nullable.
$DB->updateOrDie('glpi_entities', ['entities_id' => 'NULL'], ['id' => '0']);
/** /Replace -1 value for entities_id field */

/** Replace negative values for config foreign keys */
$fkey_config_fields = [
    'calendars_id',
    'changetemplates_id',
    'contracts_id_default',
    'entities_id_software',
    'problemtemplates_id',
    'tickettemplates_id',
    'transfers_id',
];
$migration->migrationOneTable('glpi_entities');
foreach ($fkey_config_fields as $fkey_config_field) {
    $strategy_field = str_replace('_id', '_strategy', $fkey_config_field);
    if (!$DB->fieldExists('glpi_entities', $strategy_field)) {
        $migration->addField(
            'glpi_entities',
            str_replace('_id', '_strategy', $fkey_config_field),
            'tinyint NOT NULL DEFAULT -2'
        );
        $migration->migrationOneTable('glpi_entities'); // Ensure strategy field is created to be able to fill it

        if ($DB->fieldExists('glpi_entities', $fkey_config_field)) {
            // 'contracts_id_default' and 'transfers_id' fields will only exist if a previous dev install exists
            $DB->updateOrDie(
                'glpi_entities',
                [
                    // Put negative values (-10[never]/ -2[inherit]/ -1[auto]) in strategy field
                    // or 0 if an id was selected to indicate that value is not inherited.
                    str_replace('_id', '_strategy', $fkey_config_field) => new QueryExpression(
                        sprintf('LEAST(%s, 0)', $DB->quoteName($fkey_config_field))
                    ),
                    // Keep only positive (or 0) values in id field
                    $fkey_config_field => new QueryExpression(
                        sprintf('GREATEST(%s, 0)', $DB->quoteName($fkey_config_field))
                    ),
                ],
                ['1'] // Update all entities
            );
        }
    }

    if ($DB->fieldExists('glpi_entities', $fkey_config_field)) {
        // 'contracts_id_default' and 'transfers_id' fields will only exist if a previous dev install exists
        $migration->changeField('glpi_entities', $fkey_config_field, $fkey_config_field, "int {$default_key_sign} NOT NULL DEFAULT 0");
    }
}
/** /Replace negative values for config foreign keys */

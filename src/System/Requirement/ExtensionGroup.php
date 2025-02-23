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

namespace Glpi\System\Requirement;

/**
 * @since 10.0.0
 */
class ExtensionGroup extends AbstractRequirement
{
   /**
    * Required extensions names.
    *
    * @var string[]
    */
    protected $extensions;

   /**
    * @param string      $name         Extension group title.
    * @param string[]    $extensions   Required extensions names.
    * @param bool        $optional     Indicate if extension is optional.
    * @param string|null $description  Describe usage of the extension.
    */
    public function __construct(string $title, array $extensions, bool $optional = false, ?string $description = null)
    {
        $this->title = $title;
        $this->extensions = $extensions;
        $this->optional = $optional;
        $this->description = $description;
    }

    protected function check()
    {
        $loaded_extensions  = [];
        $missing_extensions = [];

        foreach ($this->extensions as $extension) {
            if (extension_loaded($extension)) {
                $loaded_extensions[] = $extension;
            } else {
                $missing_extensions[] = $extension;
            }
        }

        $this->validated = count($missing_extensions) === 0;

        if (count($loaded_extensions) > 0) {
            $this->validation_messages[] = sprintf(__('Following extensions are installed: %s.'), implode(', ', $loaded_extensions));
        }
        if (count($missing_extensions) > 0) {
            if ($this->optional) {
                $this->validation_messages[] = sprintf(__('Following extensions are not present: %s.'), implode(', ', $missing_extensions));
            } else {
                $this->validation_messages[] = sprintf(__('Following extensions are missing: %s.'), implode(', ', $missing_extensions));
            }
        }
    }
}

<?php
/**
 * ColorInclusivo - Personalización de colores para usuarios daltónicos
 * Copyright (C) 2026 SIT ON CLOUD - David <david@sunube.es>
 */

namespace FacturaScripts\Plugins\ColorInclusivo;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\Role;
use FacturaScripts\Dinamic\Model\RoleAccess;

final class Init extends InitClass
{
    private const ROLE_NAME = 'ColorInclusivo';

    public function init(): void
    {
        // No se cargan extensiones; el CSS se inyecta vía
        // View/MenuTemplate/CssAfter y View/MenuBghTemplate/CssAfter
    }

    public function uninstall(): void
    {
    }

    public function update(): void
    {
        $this->createRoleForPlugin();
    }

    private function createRoleForPlugin(): void
    {
        $role = new Role();
        if (false === $role->load(self::ROLE_NAME)) {
            $role->codrole = $role->descripcion = self::ROLE_NAME;
            $role->save();
        }

        $controllers = [
            'ConfigColorInclusivo',
        ];

        foreach ($controllers as $controller) {
            $roleAccess = new RoleAccess();
            $where = [
                Where::eq('codrole', self::ROLE_NAME),
                Where::eq('pagename', $controller),
            ];
            if ($roleAccess->loadWhere($where)) {
                continue;
            }

            $roleAccess->allowdelete = true;
            $roleAccess->allowupdate = true;
            $roleAccess->codrole = self::ROLE_NAME;
            $roleAccess->pagename = $controller;
            $roleAccess->onlyownerdata = false;
            $roleAccess->save();
        }
    }
}

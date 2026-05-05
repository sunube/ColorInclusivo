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
use FacturaScripts\Plugins\ColorInclusivo\Model\ColorInclusivoConfig;

final class Init extends InitClass
{
    private const ROLE_NAME = 'ColorInclusivo';

    public function init(): void
    {
        // Nada en init() para no modificar custom.css en cada request.
        // La regeneración se hace cuando el usuario guarda la configuración o
        // cuando se actualiza el plugin (ver update()).
    }

    public function uninstall(): void
    {
        // Al desinstalar, eliminar nuestro bloque del custom.css dejándolo limpio.
        try {
            $config = ColorInclusivoConfig::getConfig();
            $config->activo = false; // genera vacío al aplicar
            $config->applyToCustomCss();
        } catch (\Throwable $e) {
            // ignorar
        }
    }

    public function update(): void
    {
        $this->createRoleForPlugin();

        // Regenerar custom.css con la configuración actual.
        try {
            $config = ColorInclusivoConfig::getConfig();
            $config->applyToCustomCss();
        } catch (\Throwable $e) {
            // ignorar - puede que la tabla aún no exista al primer install
        }
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

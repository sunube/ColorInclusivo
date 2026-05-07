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
        // FacturaScripts regenera Dinamic/Assets/CSS/custom.css periódicamente
        // (al desplegar assets, activar/desactivar plugins, etc.). Si nuestro
        // bloque ha desaparecido lo restauramos al vuelo. Como solo leemos el
        // archivo y escribimos cuando falta el marcador, el coste es mínimo.
        $this->ensureCustomCss();
    }

    private function ensureCustomCss(): void
    {
        $base = defined('FS_FOLDER') ? \FS_FOLDER : '';
        if (empty($base)) {
            return;
        }

        $cssPath = $base . '/Dinamic/Assets/CSS/custom.css';
        if (!file_exists($cssPath)) {
            return;
        }

        $content = @file_get_contents($cssPath);
        if (false === $content) {
            return;
        }

        // Si nuestro marcador ya está, todo correcto.
        if (strpos($content, 'ColorInclusivo START') !== false) {
            return;
        }

        // Falta. Regenerar.
        try {
            $config = ColorInclusivoConfig::getConfig();
            // No reescribir si está desactivado y no había bloque (no hay nada que poner).
            if (!$config->activo) {
                return;
            }
            $config->applyToCustomCss();
        } catch (\Throwable $e) {
            // ignorar - puede que la tabla aún no exista al primer install
        }
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

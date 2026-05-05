<?php
/**
 * ColorInclusivo - Personalización de colores para usuarios daltónicos
 * Copyright (C) 2026 SIT ON CLOUD - David <david@sunube.es>
 */

namespace FacturaScripts\Plugins\ColorInclusivo\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\ColorInclusivo\Model\ColorInclusivoConfig;

/**
 * Pantalla de configuración del plugin ColorInclusivo.
 */
class ConfigColorInclusivo extends Controller
{
    /** @var ColorInclusivoConfig */
    public $config;

    /** @var array */
    public $presets = [];

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'colorinclusivo-config';
        $data['icon'] = 'fa-solid fa-eye-low-vision';
        $data['showonmenu'] = true;
        return $data;
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $this->presets = ColorInclusivoConfig::presets();
        $this->config = ColorInclusivoConfig::getConfig();

        $action = $this->request->get('action', '');
        if ($action === 'save') {
            $this->saveAction();
        } elseif ($action === 'apply-preset') {
            $this->applyPresetAction();
        } elseif ($action === 'reset') {
            $this->resetAction();
        }
    }

    private function saveAction(): void
    {
        if (false === $this->permissions->allowUpdate) {
            Tools::log()->warning('No tienes permisos');
            return;
        } elseif (false === $this->validateFormToken()) {
            return;
        }

        $this->config->activo = (bool) $this->request->get('activo', false);
        $this->config->preset = (string) $this->request->get('preset', 'personalizado');

        // Variables Bootstrap
        $this->config->color_danger    = (string) $this->request->get('color_danger', '#dc3545');
        $this->config->color_warning   = (string) $this->request->get('color_warning', '#ffc107');
        $this->config->color_success   = (string) $this->request->get('color_success', '#198754');
        $this->config->color_info      = (string) $this->request->get('color_info', '#0dcaf0');
        $this->config->color_primary   = (string) $this->request->get('color_primary', '#0d6efd');
        $this->config->color_secondary = (string) $this->request->get('color_secondary', '#6c757d');

        // Filas
        $this->config->row_danger_bg    = (string) $this->request->get('row_danger_bg', '#f8d7da');
        $this->config->row_danger_text  = (string) $this->request->get('row_danger_text', '#58151c');
        $this->config->row_warning_bg   = (string) $this->request->get('row_warning_bg', '#fff3cd');
        $this->config->row_warning_text = (string) $this->request->get('row_warning_text', '#664d03');
        $this->config->row_success_bg   = (string) $this->request->get('row_success_bg', '#d1e7dd');
        $this->config->row_success_text = (string) $this->request->get('row_success_text', '#0a3622');
        $this->config->row_info_bg      = (string) $this->request->get('row_info_bg', '#cff4fc');
        $this->config->row_info_text    = (string) $this->request->get('row_info_text', '#055160');

        // Texto
        $this->config->text_danger  = (string) $this->request->get('text_danger', '#dc3545');
        $this->config->text_warning = (string) $this->request->get('text_warning', '#cc9a06');
        $this->config->text_success = (string) $this->request->get('text_success', '#198754');
        $this->config->text_info    = (string) $this->request->get('text_info', '#087990');

        // Ámbitos
        $this->config->aplicar_botones = (bool) $this->request->get('aplicar_botones', false);
        $this->config->aplicar_badges  = (bool) $this->request->get('aplicar_badges', false);
        $this->config->aplicar_alertas = (bool) $this->request->get('aplicar_alertas', false);
        $this->config->aplicar_filas   = (bool) $this->request->get('aplicar_filas', false);
        $this->config->aplicar_textos  = (bool) $this->request->get('aplicar_textos', false);

        // Refuerzo
        $this->config->negrita_danger   = (bool) $this->request->get('negrita_danger', false);
        $this->config->subrayado_danger = (bool) $this->request->get('subrayado_danger', false);

        if ($this->config->save()) {
            $this->config->applyToCustomCss();
            Tools::log()->notice('Configuración guardada. Si no ves los cambios pulsa Ctrl+F5 (recarga sin caché).');
        } else {
            Tools::log()->error('Error al guardar la configuración');
        }
    }

    private function applyPresetAction(): void
    {
        if (false === $this->permissions->allowUpdate) {
            Tools::log()->warning('No tienes permisos');
            return;
        } elseif (false === $this->validateFormToken()) {
            return;
        }

        $presetKey = (string) $this->request->get('preset_key', '');
        $presets = ColorInclusivoConfig::presets();

        if (!isset($presets[$presetKey])) {
            Tools::log()->warning('Preset no válido: ' . $presetKey);
            return;
        }

        $preset = $presets[$presetKey];
        $this->config->preset = $presetKey;

        foreach ($preset['colors'] as $field => $value) {
            $this->config->{$field} = $value;
        }

        if ($this->config->save()) {
            $this->config->applyToCustomCss();
            Tools::log()->notice('Preset aplicado: ' . $preset['label'] . '. Pulsa Ctrl+F5 para refrescar la caché del navegador.');
        } else {
            Tools::log()->error('Error al aplicar el preset');
        }
    }

    private function resetAction(): void
    {
        if (false === $this->permissions->allowUpdate) {
            Tools::log()->warning('No tienes permisos');
            return;
        } elseif (false === $this->validateFormToken()) {
            return;
        }

        $this->config->clear();
        $this->config->preset = 'defecto';

        if ($this->config->save()) {
            $this->config->applyToCustomCss();
            Tools::log()->notice('Configuración restablecida a valores por defecto. Pulsa Ctrl+F5.');
        } else {
            Tools::log()->error('Error al restablecer la configuración');
        }
    }
}

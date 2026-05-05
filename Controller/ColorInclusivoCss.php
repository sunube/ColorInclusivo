<?php
/**
 * ColorInclusivo - Personalización de colores para usuarios daltónicos
 * Copyright (C) 2026 SIT ON CLOUD - David <david@sunube.es>
 */

namespace FacturaScripts\Plugins\ColorInclusivo\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Plugins\ColorInclusivo\Model\ColorInclusivoConfig;

/**
 * Endpoint público que devuelve el CSS dinámico generado a partir de la configuración.
 * Se referencia desde View/MenuTemplate/CssAfter/colorinclusivo.html.twig.
 */
class ColorInclusivoCss extends Controller
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['title'] = 'ColorInclusivo CSS';
        $data['showonmenu'] = false;
        return $data;
    }

    /**
     * Acceso sin autenticación: el CSS se sirve igual a cualquier visitante.
     */
    public function publicCore(&$response): void
    {
        parent::publicCore($response);
        $this->setTemplate(false);
        $this->serveCss();
    }

    /**
     * También accesible desde el área privada.
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);
        $this->setTemplate(false);
        $this->serveCss();
    }

    private function serveCss(): void
    {
        try {
            $config = ColorInclusivoConfig::getConfig();
            $css = $config->generateCss();
        } catch (\Throwable $e) {
            $css = "/* ColorInclusivo: error generando CSS - " . str_replace('*/', '', $e->getMessage()) . " */\n";
        }

        $this->response->headers->set('Content-Type', 'text/css; charset=utf-8');
        $this->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $this->response->setContent($css);
    }
}

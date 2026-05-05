<?php
/**
 * ColorInclusivo - Personalización de colores para usuarios daltónicos
 * Copyright (C) 2026 SIT ON CLOUD - David <david@sunube.es>
 */

namespace FacturaScripts\Plugins\ColorInclusivo\Model;

use FacturaScripts\Core\Template\ModelClass;
use FacturaScripts\Core\Template\ModelTrait;
use FacturaScripts\Core\Tools;

class ColorInclusivoConfig extends ModelClass
{
    use ModelTrait;

    /** @var int */
    public $id;

    /** @var bool */
    public $activo;

    /** @var string */
    public $preset;

    // ─────── Variables Bootstrap principales ───────
    /** @var string */
    public $color_danger;
    /** @var string */
    public $color_warning;
    /** @var string */
    public $color_success;
    /** @var string */
    public $color_info;
    /** @var string */
    public $color_primary;
    /** @var string */
    public $color_secondary;

    // ─────── Fondos de filas de tabla ───────
    /** @var string */
    public $row_danger_bg;
    /** @var string */
    public $row_danger_text;
    /** @var string */
    public $row_warning_bg;
    /** @var string */
    public $row_warning_text;
    /** @var string */
    public $row_success_bg;
    /** @var string */
    public $row_success_text;
    /** @var string */
    public $row_info_bg;
    /** @var string */
    public $row_info_text;

    // ─────── Texto coloreado en columnas ───────
    /** @var string */
    public $text_danger;
    /** @var string */
    public $text_warning;
    /** @var string */
    public $text_success;
    /** @var string */
    public $text_info;

    // ─────── Ámbito de aplicación ───────
    /** @var bool */
    public $aplicar_botones;
    /** @var bool */
    public $aplicar_badges;
    /** @var bool */
    public $aplicar_alertas;
    /** @var bool */
    public $aplicar_filas;
    /** @var bool */
    public $aplicar_textos;

    // ─────── Refuerzo de accesibilidad ───────
    /** @var bool */
    public $negrita_danger;
    /** @var bool */
    public $subrayado_danger;

    public function clear(): void
    {
        parent::clear();

        $this->activo = true;
        $this->preset = 'personalizado';

        // Defaults Bootstrap 5
        $this->color_danger    = '#dc3545';
        $this->color_warning   = '#ffc107';
        $this->color_success   = '#198754';
        $this->color_info      = '#0dcaf0';
        $this->color_primary   = '#0d6efd';
        $this->color_secondary = '#6c757d';

        // Filas de tabla
        $this->row_danger_bg    = '#f8d7da';
        $this->row_danger_text  = '#58151c';
        $this->row_warning_bg   = '#fff3cd';
        $this->row_warning_text = '#664d03';
        $this->row_success_bg   = '#d1e7dd';
        $this->row_success_text = '#0a3622';
        $this->row_info_bg      = '#cff4fc';
        $this->row_info_text    = '#055160';

        // Texto en columnas
        $this->text_danger  = '#dc3545';
        $this->text_warning = '#cc9a06';
        $this->text_success = '#198754';
        $this->text_info    = '#087990';

        // Ámbito
        $this->aplicar_botones = true;
        $this->aplicar_badges  = true;
        $this->aplicar_alertas = true;
        $this->aplicar_filas   = true;
        $this->aplicar_textos  = true;

        // Refuerzo
        $this->negrita_danger   = false;
        $this->subrayado_danger = false;
    }

    /**
     * Devuelve la configuración global única (siempre id=1).
     * Si no existe, devuelve un objeto con valores por defecto sin guardar.
     */
    public static function getConfig(): self
    {
        $config = new self();
        if ($config->loadFromCode(1)) {
            return $config;
        }

        // No existe: cualquier registro
        $all = $config->all([], [], 0, 1);
        if (!empty($all)) {
            return $all[0];
        }

        // Defecto
        $config->clear();
        return $config;
    }

    public static function tableName(): string
    {
        return 'colorinclusivo_config';
    }

    public function test(): bool
    {
        // Sanitizar campos que se inyectan en CSS
        $colorFields = [
            'color_danger', 'color_warning', 'color_success', 'color_info',
            'color_primary', 'color_secondary',
            'row_danger_bg', 'row_danger_text',
            'row_warning_bg', 'row_warning_text',
            'row_success_bg', 'row_success_text',
            'row_info_bg', 'row_info_text',
            'text_danger', 'text_warning', 'text_success', 'text_info',
        ];
        foreach ($colorFields as $field) {
            $value = (string) ($this->{$field} ?? '');
            $value = trim(Tools::noHtml($value));
            // Solo aceptar #RGB, #RRGGBB o #RRGGBBAA
            if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
                $value = '#000000';
            }
            $this->{$field} = $value;
        }

        $this->preset = Tools::noHtml($this->preset);
        if (!in_array($this->preset, ['personalizado', 'defecto', 'protanopia', 'deuteranopia', 'tritanopia', 'acromatopsia', 'altocontraste'], true)) {
            $this->preset = 'personalizado';
        }

        return parent::test();
    }

    /**
     * Genera el CSS dinámico que se servirá en todas las páginas.
     */
    public function generateCss(): string
    {
        if (!$this->activo) {
            return "/* ColorInclusivo desactivado */\n";
        }

        $css  = "/* ColorInclusivo - generado dinámicamente */\n";
        $css .= ":root {\n";
        $css .= "    --bs-danger: {$this->color_danger};\n";
        $css .= "    --bs-warning: {$this->color_warning};\n";
        $css .= "    --bs-success: {$this->color_success};\n";
        $css .= "    --bs-info: {$this->color_info};\n";
        $css .= "    --bs-primary: {$this->color_primary};\n";
        $css .= "    --bs-secondary: {$this->color_secondary};\n";
        $css .= "    --bs-danger-rgb: " . self::hexToRgb($this->color_danger) . ";\n";
        $css .= "    --bs-warning-rgb: " . self::hexToRgb($this->color_warning) . ";\n";
        $css .= "    --bs-success-rgb: " . self::hexToRgb($this->color_success) . ";\n";
        $css .= "    --bs-info-rgb: " . self::hexToRgb($this->color_info) . ";\n";
        $css .= "    --bs-primary-rgb: " . self::hexToRgb($this->color_primary) . ";\n";
        $css .= "    --bs-secondary-rgb: " . self::hexToRgb($this->color_secondary) . ";\n";
        $css .= "}\n\n";

        // Filas de tablas
        if ($this->aplicar_filas) {
            $css .= "/* Filas de tabla */\n";
            $css .= ".table-danger, .table-danger > th, .table-danger > td { background-color: {$this->row_danger_bg} !important; color: {$this->row_danger_text} !important; --bs-table-bg: {$this->row_danger_bg}; --bs-table-color: {$this->row_danger_text}; }\n";
            $css .= ".table-warning, .table-warning > th, .table-warning > td { background-color: {$this->row_warning_bg} !important; color: {$this->row_warning_text} !important; --bs-table-bg: {$this->row_warning_bg}; --bs-table-color: {$this->row_warning_text}; }\n";
            $css .= ".table-success, .table-success > th, .table-success > td { background-color: {$this->row_success_bg} !important; color: {$this->row_success_text} !important; --bs-table-bg: {$this->row_success_bg}; --bs-table-color: {$this->row_success_text}; }\n";
            $css .= ".table-info, .table-info > th, .table-info > td { background-color: {$this->row_info_bg} !important; color: {$this->row_info_text} !important; --bs-table-bg: {$this->row_info_bg}; --bs-table-color: {$this->row_info_text}; }\n";
            $css .= "tr.table-danger:hover > * { background-color: " . self::darken($this->row_danger_bg, 8) . " !important; }\n";
            $css .= "tr.table-warning:hover > * { background-color: " . self::darken($this->row_warning_bg, 8) . " !important; }\n";
            $css .= "tr.table-success:hover > * { background-color: " . self::darken($this->row_success_bg, 8) . " !important; }\n";
            $css .= "tr.table-info:hover > * { background-color: " . self::darken($this->row_info_bg, 8) . " !important; }\n\n";
        }

        // Texto coloreado en columnas
        if ($this->aplicar_textos) {
            $css .= "/* Texto coloreado en columnas */\n";
            $extra = '';
            if ($this->negrita_danger) {
                $extra .= ' font-weight: 700;';
            }
            if ($this->subrayado_danger) {
                $extra .= ' text-decoration: underline;';
            }
            $css .= ".text-danger { color: {$this->text_danger} !important;{$extra} }\n";
            $css .= ".text-warning { color: {$this->text_warning} !important; }\n";
            $css .= ".text-success { color: {$this->text_success} !important; }\n";
            $css .= ".text-info { color: {$this->text_info} !important; }\n\n";
        }

        // Botones
        if ($this->aplicar_botones) {
            $css .= "/* Botones */\n";
            $css .= ".btn-danger { background-color: {$this->color_danger} !important; border-color: {$this->color_danger} !important; }\n";
            $css .= ".btn-warning { background-color: {$this->color_warning} !important; border-color: {$this->color_warning} !important; }\n";
            $css .= ".btn-success { background-color: {$this->color_success} !important; border-color: {$this->color_success} !important; }\n";
            $css .= ".btn-info { background-color: {$this->color_info} !important; border-color: {$this->color_info} !important; }\n";
            $css .= ".btn-primary { background-color: {$this->color_primary} !important; border-color: {$this->color_primary} !important; }\n";
            $css .= ".btn-outline-danger { color: {$this->color_danger} !important; border-color: {$this->color_danger} !important; }\n";
            $css .= ".btn-outline-warning { color: {$this->color_warning} !important; border-color: {$this->color_warning} !important; }\n";
            $css .= ".btn-outline-success { color: {$this->color_success} !important; border-color: {$this->color_success} !important; }\n";
            $css .= ".btn-outline-info { color: {$this->color_info} !important; border-color: {$this->color_info} !important; }\n";
            $css .= ".btn-outline-primary { color: {$this->color_primary} !important; border-color: {$this->color_primary} !important; }\n\n";
        }

        // Badges
        if ($this->aplicar_badges) {
            $css .= "/* Badges */\n";
            $css .= ".bg-danger, .badge.bg-danger { background-color: {$this->color_danger} !important; }\n";
            $css .= ".bg-warning, .badge.bg-warning { background-color: {$this->color_warning} !important; }\n";
            $css .= ".bg-success, .badge.bg-success { background-color: {$this->color_success} !important; }\n";
            $css .= ".bg-info, .badge.bg-info { background-color: {$this->color_info} !important; }\n";
            $css .= ".bg-primary, .badge.bg-primary { background-color: {$this->color_primary} !important; }\n\n";
        }

        // Alertas
        if ($this->aplicar_alertas) {
            $css .= "/* Alertas */\n";
            $css .= ".alert-danger { background-color: {$this->row_danger_bg} !important; color: {$this->row_danger_text} !important; border-color: " . self::darken($this->row_danger_bg, 10) . " !important; }\n";
            $css .= ".alert-warning { background-color: {$this->row_warning_bg} !important; color: {$this->row_warning_text} !important; border-color: " . self::darken($this->row_warning_bg, 10) . " !important; }\n";
            $css .= ".alert-success { background-color: {$this->row_success_bg} !important; color: {$this->row_success_text} !important; border-color: " . self::darken($this->row_success_bg, 10) . " !important; }\n";
            $css .= ".alert-info { background-color: {$this->row_info_bg} !important; color: {$this->row_info_text} !important; border-color: " . self::darken($this->row_info_bg, 10) . " !important; }\n";
        }

        return $css;
    }

    /**
     * Marcadores que delimitan nuestra sección dentro de custom.css
     */
    private const MARKER_START = '/* ===== ColorInclusivo START - generado automaticamente, no editar ===== */';
    private const MARKER_END   = '/* ===== ColorInclusivo END ===== */';

    /**
     * Escribe el CSS dinámico dentro de Dinamic/Assets/CSS/custom.css.
     * Si ya hay una sección de ColorInclusivo la reemplaza; el resto del archivo
     * (CSS personalizado por el usuario) se conserva intacto.
     */
    public function applyToCustomCss(): bool
    {
        $base = defined('FS_FOLDER') ? \FS_FOLDER : '';
        if (empty($base)) {
            return false;
        }

        $candidates = [
            $base . '/Dinamic/Assets/CSS/custom.css',
            $base . '/MyFiles/Public/colorinclusivo.css',
        ];

        // Trabajamos con custom.css si existe / es escribible su carpeta.
        $cssPath = $candidates[0];
        if (!file_exists($cssPath) && !is_dir(dirname($cssPath))) {
            return false;
        }
        if (!file_exists($cssPath)) {
            @file_put_contents($cssPath, "");
        }
        if (!is_writable($cssPath)) {
            return false;
        }

        $existing = (string) @file_get_contents($cssPath);

        // Quitar la sección anterior si existía.
        $pattern = '/\s*' . preg_quote(self::MARKER_START, '/') . '.*?' . preg_quote(self::MARKER_END, '/') . '\s*/s';
        $existing = preg_replace($pattern, "\n", $existing);
        $existing = rtrim((string) $existing) . "\n";

        // Generar nuevo bloque solo si está activo.
        if ($this->activo) {
            $existing .= "\n" . self::MARKER_START . "\n"
                . $this->generateCss()
                . "\n" . self::MARKER_END . "\n";
        }

        return false !== @file_put_contents($cssPath, $existing);
    }

    /**
     * Convierte #RRGGBB a "r, g, b" para Bootstrap --bs-*-rgb
     */
    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) < 6) {
            return '0, 0, 0';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "{$r}, {$g}, {$b}";
    }

    /**
     * Oscurece un color hex en N% (devuelve hex).
     */
    public static function darken(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) < 6) {
            return '#000000';
        }
        $factor = max(0, 1 - ($percent / 100));
        $r = max(0, min(255, (int) round(hexdec(substr($hex, 0, 2)) * $factor)));
        $g = max(0, min(255, (int) round(hexdec(substr($hex, 2, 2)) * $factor)));
        $b = max(0, min(255, (int) round(hexdec(substr($hex, 4, 2)) * $factor)));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Devuelve los presets disponibles.
     * Cada preset incluye explicación, colores BS principales, fondos de fila y textos.
     */
    public static function presets(): array
    {
        return [
            'defecto' => [
                'label' => 'Por defecto (Bootstrap)',
                'desc'  => 'Colores estándar de Bootstrap 5. Útil para volver al origen.',
                'colors' => [
                    'color_danger'    => '#dc3545',
                    'color_warning'   => '#ffc107',
                    'color_success'   => '#198754',
                    'color_info'      => '#0dcaf0',
                    'color_primary'   => '#0d6efd',
                    'color_secondary' => '#6c757d',
                    'row_danger_bg'   => '#f8d7da', 'row_danger_text'  => '#58151c',
                    'row_warning_bg'  => '#fff3cd', 'row_warning_text' => '#664d03',
                    'row_success_bg'  => '#d1e7dd', 'row_success_text' => '#0a3622',
                    'row_info_bg'     => '#cff4fc', 'row_info_text'    => '#055160',
                    'text_danger'     => '#dc3545',
                    'text_warning'    => '#cc9a06',
                    'text_success'    => '#198754',
                    'text_info'       => '#087990',
                ],
            ],
            'protanopia' => [
                'label' => 'Protanopía (rojo→naranja/azul)',
                'desc'  => 'Dificultad para ver el rojo. Sustituye rojos por naranjas oscuros y reemplaza el verde por azul para distinguir mejor.',
                'colors' => [
                    'color_danger'    => '#d97706', // naranja oscuro reemplaza al rojo
                    'color_warning'   => '#fde047', // amarillo más claro
                    'color_success'   => '#1d4ed8', // azul reemplaza al verde
                    'color_info'      => '#06b6d4',
                    'color_primary'   => '#1e40af',
                    'color_secondary' => '#475569',
                    'row_danger_bg'   => '#fed7aa', 'row_danger_text'  => '#7c2d12',
                    'row_warning_bg'  => '#fef9c3', 'row_warning_text' => '#713f12',
                    'row_success_bg'  => '#dbeafe', 'row_success_text' => '#1e3a8a',
                    'row_info_bg'     => '#cffafe', 'row_info_text'    => '#155e75',
                    'text_danger'     => '#9a3412',
                    'text_warning'    => '#92400e',
                    'text_success'    => '#1e40af',
                    'text_info'       => '#0e7490',
                ],
            ],
            'deuteranopia' => [
                'label' => 'Deuteranopía (verde→azul)',
                'desc'  => 'Dificultad para ver el verde (la más común). Sustituye verdes por azules y refuerza el contraste de los rojos.',
                'colors' => [
                    'color_danger'    => '#b91c1c',
                    'color_warning'   => '#f59e0b',
                    'color_success'   => '#2563eb', // azul reemplaza al verde
                    'color_info'      => '#0891b2',
                    'color_primary'   => '#1e40af',
                    'color_secondary' => '#475569',
                    'row_danger_bg'   => '#fee2e2', 'row_danger_text'  => '#7f1d1d',
                    'row_warning_bg'  => '#fef3c7', 'row_warning_text' => '#78350f',
                    'row_success_bg'  => '#dbeafe', 'row_success_text' => '#1e3a8a',
                    'row_info_bg'     => '#cffafe', 'row_info_text'    => '#164e63',
                    'text_danger'     => '#991b1b',
                    'text_warning'    => '#92400e',
                    'text_success'    => '#1e40af',
                    'text_info'       => '#0e7490',
                ],
            ],
            'tritanopia' => [
                'label' => 'Tritanopía (azul→verde/rosa)',
                'desc'  => 'Dificultad para ver el azul/amarillo. Refuerza con tonos verdes y rosas que se distinguen mejor.',
                'colors' => [
                    'color_danger'    => '#e11d48', // rosa fuerte
                    'color_warning'   => '#f97316', // naranja saturado
                    'color_success'   => '#16a34a',
                    'color_info'      => '#a855f7', // morado en lugar de cian
                    'color_primary'   => '#7c3aed',
                    'color_secondary' => '#525252',
                    'row_danger_bg'   => '#ffe4e6', 'row_danger_text'  => '#881337',
                    'row_warning_bg'  => '#ffedd5', 'row_warning_text' => '#7c2d12',
                    'row_success_bg'  => '#dcfce7', 'row_success_text' => '#14532d',
                    'row_info_bg'     => '#f3e8ff', 'row_info_text'    => '#581c87',
                    'text_danger'     => '#be123c',
                    'text_warning'    => '#9a3412',
                    'text_success'    => '#15803d',
                    'text_info'       => '#6b21a8',
                ],
            ],
            'acromatopsia' => [
                'label' => 'Acromatopsia (escala de grises)',
                'desc'  => 'Sin percepción del color. Usa diferentes intensidades de gris para máximo contraste, complementando con negrita en los rojos.',
                'colors' => [
                    'color_danger'    => '#1f2937', // gris muy oscuro
                    'color_warning'   => '#9ca3af',
                    'color_success'   => '#4b5563',
                    'color_info'      => '#6b7280',
                    'color_primary'   => '#111827',
                    'color_secondary' => '#9ca3af',
                    'row_danger_bg'   => '#d1d5db', 'row_danger_text'  => '#000000',
                    'row_warning_bg'  => '#e5e7eb', 'row_warning_text' => '#1f2937',
                    'row_success_bg'  => '#f3f4f6', 'row_success_text' => '#374151',
                    'row_info_bg'     => '#f9fafb', 'row_info_text'    => '#4b5563',
                    'text_danger'     => '#000000',
                    'text_warning'    => '#374151',
                    'text_success'    => '#4b5563',
                    'text_info'       => '#6b7280',
                ],
            ],
            'altocontraste' => [
                'label' => 'Alto contraste',
                'desc'  => 'Colores muy saturados y oscuros sobre fondos claros para máxima legibilidad. Útil para visión reducida.',
                'colors' => [
                    'color_danger'    => '#7f1d1d',
                    'color_warning'   => '#854d0e',
                    'color_success'   => '#14532d',
                    'color_info'      => '#164e63',
                    'color_primary'   => '#1e3a8a',
                    'color_secondary' => '#1f2937',
                    'row_danger_bg'   => '#fecaca', 'row_danger_text'  => '#450a0a',
                    'row_warning_bg'  => '#fde68a', 'row_warning_text' => '#451a03',
                    'row_success_bg'  => '#bbf7d0', 'row_success_text' => '#052e16',
                    'row_info_bg'     => '#a5f3fc', 'row_info_text'    => '#083344',
                    'text_danger'     => '#7f1d1d',
                    'text_warning'    => '#713f12',
                    'text_success'    => '#14532d',
                    'text_info'       => '#155e75',
                ],
            ],
        ];
    }
}

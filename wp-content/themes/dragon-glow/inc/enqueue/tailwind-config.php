<?php
/**
 * Dragon Glow — Tailwind CDN config.
 *
 * Returns the inline Tailwind config (design tokens: colors, spacing, fonts)
 * printed after the Tailwind CDN script. Kept isolated so the token source
 * is easy to locate and edit.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Tailwind config string.
 *
 * @return string
 */
function dg_get_tailwind_config(): string {
    $config = <<<'JS'
tailwind.config = {
  theme: {
    extend: {
      colors: {
        "primary":"#735c00",
        "on-primary":"#ffffff",
        "primary-container":"#d4af37",
        "on-primary-container":"#554300",
        "inverse-primary":"#e9c349",
        "primary-fixed":"#ffe088",
        "primary-fixed-dim":"#e9c349",
        "on-primary-fixed":"#241a00",
        "on-primary-fixed-variant":"#574500",

        "secondary":"#6a5b55",
        "on-secondary":"#ffffff",
        "secondary-container":"#f0dbd3",
        "on-secondary-container":"#6f5f59",
        "secondary-fixed":"#f3ded6",
        "secondary-fixed-dim":"#d6c2bb",
        "on-secondary-fixed":"#241914",
        "on-secondary-fixed-variant":"#52443e",

        "tertiary":"#5d5f5f",
        "on-tertiary":"#ffffff",
        "tertiary-container":"#f1ca50",
        "on-tertiary-container":"#6b5500",
        "tertiary-fixed":"#e2e2e2",
        "tertiary-fixed-dim":"#c6c6c7",
        "on-tertiary-fixed":"#1a1c1c",
        "on-tertiary-fixed-variant":"#454747",

        "background":"#fcf9f8",
        "on-background":"#1c1b1b",

        "surface":"#fcf9f8",
        "surface-dim":"#dcd9d9",
        "surface-bright":"#fcf9f8",
        "surface-container-lowest":"#ffffff",
        "surface-container-low":"#f6f3f2",
        "surface-container":"#f0eded",
        "surface-container-high":"#eae7e7",
        "surface-container-highest":"#e5e2e1",
        "on-surface":"#1c1b1b",
        "on-surface-variant":"#4d4635",
        "surface-tint":"#735c00",
        "surface-variant":"#e5e2e1",

        "inverse-surface":"#313030",
        "inverse-on-surface":"#f3f0ef",

        "outline":"#7f7663",
        "outline-variant":"#d0c5af",

        "error":"#ba1a1a",
        "on-error":"#ffffff",
        "error-container":"#ffdad6",
        "on-error-container":"#93000a"
      },
      borderRadius: {
        DEFAULT: "0.125rem",
        lg:      "0.25rem",
        xl:      "0.5rem",
        full:    "0.75rem",
      },
      spacing: {
        "unit":                 "8px",
        "gutter":               "24px",
        "container-max":        "1280px",
        "container-max-width":  "1280px",
        "margin-desktop":       "64px",
        "margin-mobile":        "20px",
        "section-gap":          "120px",
      },
      fontFamily: {
        display:            ['"Playfair Display"', 'Georgia', 'serif'],
        headline:           ['"Playfair Display"', 'serif'],
        "display-lg":       ['"Playfair Display"', 'serif'],
        "headline-lg":      ['"Playfair Display"', 'serif'],
        "headline-lg-mobile": ['"Playfair Display"', 'serif'],
        "headline-md":      ['"Playfair Display"', 'serif'],
        "body-lg":          ['"Montserrat"', 'sans-serif'],
        "body-md":          ['"Montserrat"', 'sans-serif'],
        "label-sm":         ['"Montserrat"', 'sans-serif'],
        label:              ['"Montserrat"', 'sans-serif'],
        body:               ['"Montserrat"', 'sans-serif'],
        serif:              ['"Bodoni Moda"', 'Georgia', 'serif'],
      },
      fontSize: {
        "display-lg":         ["64px", { lineHeight: "1.1", fontWeight: "700", letterSpacing: "-0.02em" }],
        "headline-lg":        ["40px", { lineHeight: "1.2", fontWeight: "600" }],
        "headline-lg-mobile": ["32px", { lineHeight: "1.2", fontWeight: "600" }],
        "headline-md":        ["28px", { lineHeight: "1.3", fontWeight: "500" }],
        "body-lg":            ["18px", { lineHeight: "1.6", fontWeight: "400" }],
        "body-md":            ["16px", { lineHeight: "1.6", fontWeight: "400" }],
        "label-sm":           ["12px", { lineHeight: "1.0", fontWeight: "600", letterSpacing: "0.1em" }],
      }
    }
  }
}
JS;
    return $config;
}
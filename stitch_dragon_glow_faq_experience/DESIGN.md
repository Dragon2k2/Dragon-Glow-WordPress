---
name: Dragon Glow
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1c1b1b'
  on-surface-variant: '#444845'
  inverse-surface: '#313030'
  inverse-on-surface: '#f3f0ef'
  outline: '#747875'
  outline-variant: '#c4c7c4'
  surface-tint: '#5e5e5d'
  primary: '#5e5e5d'
  on-primary: '#ffffff'
  primary-container: '#f9f8f6'
  on-primary-container: '#717270'
  inverse-primary: '#c7c6c5'
  secondary: '#605e59'
  on-secondary: '#ffffff'
  secondary-container: '#e3dfd8'
  on-secondary-container: '#64625d'
  tertiary: '#775a19'
  on-tertiary: '#ffffff'
  tertiary-container: '#fff7ef'
  on-tertiary-container: '#8d6d2b'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e3e2e0'
  primary-fixed-dim: '#c7c6c5'
  on-primary-fixed: '#1a1c1b'
  on-primary-fixed-variant: '#464746'
  secondary-fixed: '#e6e2db'
  secondary-fixed-dim: '#cac6bf'
  on-secondary-fixed: '#1c1c17'
  on-secondary-fixed-variant: '#484742'
  tertiary-fixed: '#ffdea5'
  tertiary-fixed-dim: '#e9c176'
  on-tertiary-fixed: '#261900'
  on-tertiary-fixed-variant: '#5d4201'
  background: '#fcf9f8'
  on-background: '#1c1b1b'
  surface-variant: '#e5e2e1'
typography:
  display-lg:
    fontFamily: Geist
    fontSize: 64px
    fontWeight: '300'
    lineHeight: '1.1'
    letterSpacing: -0.04em
  display-lg-mobile:
    fontFamily: Geist
    fontSize: 40px
    fontWeight: '300'
    lineHeight: '1.1'
    letterSpacing: -0.03em
  headline-md:
    fontFamily: Geist
    fontSize: 32px
    fontWeight: '400'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-sm:
    fontFamily: Geist
    fontSize: 24px
    fontWeight: '400'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Geist
    fontSize: 18px
    fontWeight: '300'
    lineHeight: '1.6'
    letterSpacing: 0em
  body-md:
    fontFamily: Geist
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: 0em
  label-md:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1'
    letterSpacing: 0.1em
  label-sm:
    fontFamily: Geist
    fontSize: 10px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.15em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1440px
  gutter: 24px
  margin-desktop: 80px
  margin-mobile: 24px
---

## Brand & Style
The design system embodies "Quiet Luxury"—a 2026 ultra-modern aesthetic that prioritizes restraint over opulence. It targets a discerning audience seeking sophisticated, high-performance skincare and cosmetics. The UI must evoke an ethereal, luminous quality, reflecting the "Dragon Glow" brand identity through expansive whitespace and light-refracting elements.

The style is a fusion of **Minimalism** and **Glassmorphism**. It utilizes broad, uncrowded canvases, high-fidelity typography, and subtle translucent layers that mimic the clarity of premium glass packaging. Every interaction should feel intentional and serene, avoiding visual clutter to allow the product photography to serve as the primary emotional driver.

## Colors
The palette is rooted in a "Light Dominant" philosophy, utilizing varying textures of white and sand to create depth without introducing heavy pigment.

- **Primary (Pearl White):** Used for main backgrounds to create an expansive, radiant atmosphere.
- **Secondary (Soft Sand):** Applied to subtle structural elements, dividers, and secondary surfaces to provide soft definition.
- **Tertiary (Muted Gold):** Reserved exclusively for high-impact accents, interactive states, or delicate brand signifiers. It should be used sparingly to maintain its premium feel.
- **Neutral (Deep Charcoal):** Utilized for typography and iconography to ensure absolute legibility and a sharp, modern contrast against the light surfaces.

## Typography
The typography system uses **Geist** to bridge the gap between technical precision and high-fashion elegance. 

Headlines utilize light weights and tight letter-spacing to create a "silken" visual texture. Body text maintains generous line heights to ensure a relaxed, breathable reading experience. Labels are intentionally small and tracked out to mimic the sophisticated markings found on luxury cosmetic decanters. On mobile devices, display type scales down aggressively to maintain the integrity of the whitespace and prevent crowding the viewport.

## Layout & Spacing
This design system employs a **Fluid Grid** with oversized outer margins to frame content like a gallery piece. 

- **Desktop:** A 12-column grid with 80px side margins. Large components should often span 6 or 8 columns to avoid horizontal stretching, maintaining a centered, balanced verticality.
- **Mobile:** A 4-column grid with 24px margins. 
- **Rhythm:** Spacing follows an 8px base unit. Vertical rhythm is expansive; use double the standard padding between sections to reinforce the feeling of "Quiet Luxury." Content should never feel compressed.

## Elevation & Depth
Depth is achieved through **Glassmorphism** and **Tonal Layers** rather than traditional drop shadows.

- **Surfaces:** Use semi-transparent backdrops (`rgba(249, 248, 246, 0.8)`) with a high saturation and 20px blur for overlays and navigation bars.
- **Outlines:** Instead of shadows, use 1px "ghost borders" in `Soft Sand` or high-clarity white to define containers.
- **Interaction:** Hover states involve a subtle increase in background opacity or a soft, internal glow (tinted with Muted Gold) rather than a lifting motion. The interface should feel like light passing through frosted glass.

## Shapes
The shape language is "Soft," utilizing minimal corner radii to maintain a modern, architectural silhouette. 

Standard components use a 4px radius (`0.25rem`). This sharp-yet-approachable geometry reflects the precision of the brand's formulations while remaining tactile. For circular elements (like color swatches or certain icons), use full pill-rounding to create a organic contrast against the structured grid.

## Components
- **Buttons:** Primary buttons are Solid Deep Charcoal with White Geist text. Secondary buttons use a Muted Gold 1px border with no fill. Hover states should include a "liquid" fill transition or a subtle shift in opacity.
- **Input Fields:** Bottom-border only, using a 1px Soft Sand stroke. Labels float above in `label-sm` style. Error states are indicated by a shift to a muted terracotta, avoiding harsh bright reds.
- **Cards:** Borderless with a very subtle Soft Sand background. Use generous internal padding (min 32px). Imagery should use `aspect-ratio: 4/5` for a portrait, editorial feel.
- **Chips/Swatches:** For product shades, use perfect circles with a 1px Soft Sand stroke. Active states are indicated by a 2px Muted Gold outer ring with a 4px gap.
- **Lists:** Horizontal dividers should be 0.5px wide and colored in Soft Sand, stopping 24px short of the container edges to feel "floating."
- **Progressive Disclosure:** Use thin, refined icons for carousels and accordions. Transitions must be smooth and "liquid," avoiding snappy or mechanical easing.
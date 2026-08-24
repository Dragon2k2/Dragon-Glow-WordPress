# Dragon Glow WordPress Theme

A luxury skincare e-commerce theme built with WordPress and WooCommerce, featuring the "Luminous Ethereal" design system.

## Features

- **WooCommerce Integration** - Full e-commerce functionality with custom template overrides
- **Luminous Ethereal Design** - Glassmorphism effects, ethereal color palette, premium aesthetics
- **Responsive Design** - Mobile-first approach with Tailwind CSS via CDN
- **Performance Optimized** - No build tools required, CDN-based CSS/JS
- **AwardSpace Compatible** - PHP 8.x ready, no Node.js dependencies

## Requirements

- WordPress 6.4+
- PHP 8.0+
- WooCommerce 9.0+
- MySQL 5.7+

## Installation

1. Download/clone this theme folder
2. Upload to `wp-content/themes/dragon-glow/`
3. Activate the theme in WordPress admin
4. Install and activate WooCommerce
5. Create required pages (Shop, Cart, Checkout, My Account)
6. Configure menu locations and widgets

## Theme Setup

### Required Pages

After activating WooCommerce, ensure these pages are created:

| Page | Purpose |
|------|---------|
| Shop | Main product listing (slug: /shop/) |
| Cart | Shopping cart (slug: /cart/) |
| Checkout | Checkout process (slug: /checkout/) |
| My Account | Customer account area (slug: /my-account/) |

### Custom Pages

Create these pages and assign templates:

| Page | Template |
|------|----------|
| Home | Homepage — Dragon Glow |
| Our Story | About Us — Dragon Glow |
| Contact Us | Contact Us — Dragon Glow |
| Wishlist | Wishlist — Dragon Glow |

### Navigation Menus

Create menus for these locations:

- **Primary Navigation** - Main site navigation
- **Footer — Shop Links** - Links in footer column
- **Footer — Company Links** - Company info links
- **Footer — Help Links** - Customer support links

## Theme Customization

### Customizer Settings

Access via **Appearance → Customize**:

- Hero section image and text
- Story section content
- Social media links
- Footer content

### Product Categories

Create product categories matching:

- Serums
- Moisturizers
- Cleansers
- Masks
- Eye Care
- Gift Sets

### Recommended Plugins

- **WooCommerce** - Required for e-commerce
- **Really Simple SSL** - Force HTTPS
- **WP Mail SMTP** - Email configuration
- **WooCommerce Stripe Gateway** - Payment processing

## File Structure

```
dragon-glow/
├── assets/
│   ├── css/          # Custom styles
│   ├── js/           # JavaScript files
│   └── images/       # Theme images
├── inc/              # PHP includes
├── languages/        # Translation files
├── page-templates/    # Custom page templates
├── template-parts/    # Reusable template parts
└── woocommerce/      # WooCommerce overrides
```

## Development

This theme uses:

- **Tailwind CSS** via CDN (no build step)
- **Google Fonts** (Playfair Display, Plus Jakarta Sans)
- **Material Symbols** for icons
- **Vanilla JavaScript** for interactions

## AwardSpace Hosting Notes

- Upload theme via FileZilla or cPanel File Manager
- Set memory limit in `wp-config.php`: `define('WP_MEMORY_LIMIT', '256M');`
- Configure SSL via Really Simple SSL plugin
- Use WP Mail SMTP with Brevo/Gmail for transactional emails

## Support

For issues or questions, please refer to the documentation or contact support.

## License

GNU General Public License v2 or later

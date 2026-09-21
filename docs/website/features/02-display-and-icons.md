# Display and Icon Styles

The Display settings tab controls how the share buttons look: the icon style and the colors.

## Icon styles

Four visual styles are available for the sharing buttons:

- **Circle** (the default)
- **Rectangle**
- **Black & White**
- **Bar**

The chosen style is applied as a CSS class on the share dropdown container, so it changes the look of every share button at once. The value is stored in `bpas_icon_color_settings[icon_style]`.

## Custom colors

You can set three colors with the color pickers on the Display tab:

| Color | Default | Applies to |
|---|---|---|
| Background | `#667eea` | Button background |
| Icon / text | `#ffffff` | Icon and label |
| Hover | `#5a6fd8` | Button on hover |

Values are validated with `sanitize_hex_color` and saved to `bpas_icon_color_settings`.

## Responsive and RTL

The share styles ship with an RTL variant. When `is_rtl()` is true the plugin loads the RTL stylesheet automatically, so buttons flip correctly for right-to-left languages. Minified assets are used in production; return `false` from `bp_share_use_minified_assets` to load the unminified CSS and JS while debugging.

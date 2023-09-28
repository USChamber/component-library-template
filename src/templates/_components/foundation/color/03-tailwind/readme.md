# Tailwind CSS classes

These are classes to be used in HTML. Some of these classes, such as `text-primary`, `bg-primary`, `border-primary`
, `text-link-off` and `text-link-on` will vary if they're inside of a `accent-primary` or `accent-secondary` and then
change per theme selected. See themes notes.

These colour changes inside of `accent-primary` or `accent-secondary` and by theme are accomplished because the Tailwind
CSS class mapping for text, border and background are linked to CSS variables, which in turn respond to parent classes
for accent and theme.

To add a new Tailwind utility class, open `/src/css/app.config.json` and find the `color` and add your utility class
name to `borderColor`, `textColor` or `backgroundColor`. Most likely it will be a CSS variable linking to a token or
theme variable.

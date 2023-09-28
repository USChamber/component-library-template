# Responsive typography classes

Typography across the USCC is systematised and styled by one of the utility classes demonstrated here.

Each of these type styles combines font size, line height, font weight, letter spacing and any other font/type
properties except colour and spacing (which you'd control with other tailwind classes). Every instance of text on the
USCC site uses one of these type styles, there are no exceptions and no other way of defining a text size.

Each is prefixed by `f-` and used like:

```HTML
<h1 class="f-heading-1">Great Scott!</h1>
<p class="f-body-1 mt-20">Are you telling me you built a time machine out of a DeLeorean?</p>
```

If you need to have a type style that isn't one of these, first make sure none of these styles will do the job. If you
really do have some new use case, then add your new type style to the system. To do this,
open `/src/css/app.config.json` and take a look to the `typography` and `typesets` keys. Here you will see typesets
defined, their names and their values at different breakpoints.

For example, for `heading-1` (CSS class `f-heading-1`):

```JavaScript
"heading-1": {
  "sm": {
    "font-family": "var(--sans)",
    "font-feature-settings": "\"kern\", \"liga\", \"ss01\"",
    "font-weight": "700",
    "font-size": "15px",
    "letter-spacing": "-0.009em",
    "line-height": "1.4"
  },
  "lg": {
    "font-size": "16px",
    "letter-spacing": "-0.011em"
  },
  "xl": {
    "font-size": "17px",
    "letter-spacing": "-0.013em"
  }
}
```

To add a new typeset, make a new key with a unique name and set the font settings per breakpoint similar to the others.
Note that we operate smallest breakpoint up, in our case, `sm` and up. If you only specify `sm` then those styles will
be used for all breakpoints, in that sense they operate as defaults.

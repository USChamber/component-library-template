# Custom Tailwind utilities

These come from 3 places:

- plugins
    - @area17/a17-tailwind-plugins
    - custom
- `/tailwind.config.js`
- CSS files (`/src/css/`)

## Plugins

### @area17/a17-tailwind-plugins

From [@area17/a17-tailwind-plugins](https://www.npmjs.com/package/@area17/a17-tailwind-plugins)

- Setup - sets up breakpoint, container width, gutter, column count and breakpoint
- ColorTokens - sets color tokens as root CSS variables
- ApplyColorVariables - creates CSS variables from the colour tokens, applies them to border, text and background
  colours
- SpacingTokens - creates rem spacing tokens from pixel named tokens
- Spacing - creates classes for the responsive spacing scale sets
- Typography - creates classes for the responsive type sets
- Layout - creates classes to handle column layouts - most useful of which are the `cols-N` classes for making things N
  number of design columns wide
- Keyline - creates a border that sits in the gutter between elements and assumes spacing is that of var(--inner-gutter)
- GridGap - creates classes to handle responsive grid gutters
- PseudoElements - creates pseudo-elements as variants
- DevTools - creates classes for a columns layer tool

### Custom plugins

- CssVariableMapping - applies the color tokens to the themes, sorts the overrides, is theme magic
- RatioBox - generates responsive ratio classes such as: `ratio ratio-3x2`, optionally can have free ratio
  with `ratio-free` and expandable ratio boxes with `ratio-expandable`. Used in `atom:img` when a ratio is
  supplied.
- GridLine - generates grid lines between items in grids, used in `listing:grid-3` and `listing:grid-4`. Core
  to this are the following classes, applied to the grid element:

    - `grid-line-x` - draws a horizontal grid line under each item in the grid
    - `grid-line-xfull` - draws a horizontal grid line full width of the grid under each row of items
    - `grid-line-x-0` - removes row grid lines
    - `grid-line-y` - draws a vertical grid line between each item
    - `grid-line-yfull` - draws a vertical grid line between each column of items, full height of the grid
    - `grid-line-y-0` - removes column grid lines

  Grid lines default to the primary border color (Tailwind `border-primary`) and the column and row gaps default to the
  inner gutter (CSS variable `var(--inner-gutter)`).

    - `grid-line-x-transparent-2`/`grid-line-xfull-transparent-2`/`grid-line-y-transparent-2`
      /`grid-line-yfull-transparent-2` - uses CSS variable `border-transparent-2` (equivalent to TW
      class `border-transparent-2`) as the line colour

  If larger grid row gaps are used, eg. `gap-y-48` then you'll need to tell the gridlines to account for this:

    - `grid-line-x-24` - moves the grid row lines down by 24px instead of `var(--inner-gutter)`. Any spacing value set
      up in the project is valid.

## tailwind.config.js

Beyond CSS classes generated from the plugins, which are primarily used for colour, typography, spacing and grid - there
are a bunch of extended Tailwind groups using `extend`.

The interesting ones here are:

```JavaScript
width: {
    'half-inc-gutter': 'calc(50% - (var(--inner-gutter) * 0.5))',
    '1/4-inc-gutter': 'calc(25% - (var(--inner-gutter) * 0.75))',
    '3/4-inc-gutter': 'calc(75% - (var(--inner-gutter) * 0.25))',
    '1/2-inc-gutter': 'calc(50% - (var(--inner-gutter) * 0.5))',
    '1/3-inc-gutter': 'calc(33.333% - (var(--inner-gutter) * 0.666))',
    '2/3-inc-gutter': 'calc(66.666% - (var(--inner-gutter) * 0.333))',
    'container': 'calc(var(--container-width, 100%) - (2 * var(--outer-gutter, 0)))'
},
```

These generate classes like `w-1/2-inc-gutter` which allow you to divide up containers and whilst accounting for the
inner gutters of the design grid - using `w-1/2` alone would result in a box that is 50% width and not 50% of the
available design columns. So, to span 3 of 6 design columns, you would use `w-1/2-inc-gutter`.

`w-container` is the basis for an override for the default Tailwind `.container` class - which uses variables from the
config file to set sizes.

## CSS files

Firstly, `/src/css/app.css` is a list of imports to other files - this speeds up the Tailwind compilation as the whole
file doesn't need to be recompiled if changes are made to one import.

After including some `base`, `components` and `utilities` there are some custom CSS files for:

- root-variables - adds some extra root CSS variables, used by classes and read by JavaScript
- theme - theme specific overrides
- focus-handling - we have a JavaScript function, `focusDisplayHandler()` which adds `data-focus-method` attributes to
  elements on focus to split keyboard tabbing from mouse events, and then we style the focus styles for the site here (
  default focus styles are switched off, Tailwind focus styling requires adding more classes and is confusing)
- z-index - define z-index for globals for better visibility of what layers over what
- state - state handling for globals (eg: burger menu active)

### base

This currently only adds Tailwind Base styles.

### components

- container - override class for Tailwind's default container
- breakout - a class to breakout of a container to be full screen again
- btn - button styling, applies CSS colour variables to buttons
- burger-btn - styling for the animated burger menu button
- blocks - defines the spacing between the blocks inside of the blocks repeater
- article - article layout needs custom CSS, its a bit of an on standard layout and some blocks that can be within it
  need specific overrides
- colset-75-25 - similar to article, custom CSS for layout and child block overrides
- quote - custom quote block quotes "
- uscc-header - some state handling specific to the main site header and its children
- menu - the burger menu and dropdown menu styling, including their animations
- modal - modal styling, including transitions
- global-search - search drop down styling, including transitions
- mask - mask styling, including transitions. The mask is is the dark layer shown when you open the burger menu, drop
  downs, global search, modal etc.
- multi-select - multi select behavior block needs custom styles
- input - input styling, including checkbox, radio, select and form errors

### utilities

- link-underline - custom link underline styles as `text-decoration: underline` alone and the various `border-bottom`
  tricks are all a PITA
- background-fill - draws a background thats 100vw width to elements that are constrained by `.container`
- gutter-stroke - draws keylines in the column gutter to the left or right of an element
- no-scrollbar - hides an overflowing element scroll bar (but allows scroll) - used sparingly with Swiper carousels as
  in general this is an accessibility no no
- styled-scrollbar - adds some simple styles to browser scroll bars for browsers than can, again used sparingly
- slider - styles for the various sliders, powered by [Swiper JS](https://swiperjs.com/)

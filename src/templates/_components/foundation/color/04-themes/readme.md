# Themes

Themes are controlled by changing the `theme-` class on the `<body>` element to one of the named themes.

**NB.** `theme-greyscale`, `theme-pride`, `theme-white` and `theme-black` are **not** approved - they are demonstrations
of how to configure themes within `/src/css/app.config.json`/`/src/css/_theme.css`.

Within your page, to see the primary accent background, use `.accent-primary` class on your element. And similarly, to
see the secondary accent background, use `.accent-secondary` class on your element.

Both `.accent-primary` and `.accent-secondary` will override the background, text colour, button colours and link
colours of their children and so you don't need to worry about adding classes to the children to update the styles. Eg:

```HTML
<div class="accent-primary">
  <p>Great Scott!!</p>
</div>
```

The `p` won't display as dark blue text (the default `.text-primary`) colour as the `.accent-primary`
overrides `.text-primary` to be something more readable - in the case of `.theme-default`, `.accent-primary`
overrides `.text-primary` to be a light blue.

If you want to be a bit more manual about things, you can also use `.bg-accent-primary`, `.bg-accent-secondary`
, `.text-accent-primary`, `.text-accent-secondary` or , `.border-accent-primary`, `.border-accent-secondary`. But care
must be taken when doing this, as you can't guarantee that text should always be `text-accent-secondary` when
in `bg-accent-primary` as sometimes the colours need overriding. For example, during development we had a theme which
was comprised of a dark red and lighter red - both of these needed white text to get sufficient contrast for
accessibility. In this case, setting text to the secondary accent colour on the primary accent background would have
failed (as it would have displayed red on red). This is why the `accent-primary` and `accent-secondary` classes are so
useful.

You may also need to force the `text-primary` colour or `border-primary` colour to be defaults even when inside
a `accent-primary`/`accent-secondary` element. For this we have `.text-primary-force` and `.border-primary-force`.
Example usage is image captions in hero 50:50 blocks.

## Theme set up / how to add a new theme

The majority of theme set up is done within `/src/css/app.config.json` - see the `color` and `themes` keys.

The first key inside is `defaults`:

```JavaScript
"themes": {
  "defaults": {
    "primary-accent": "var(--blue-02)",
    "secondary-accent": "var(--blue-05)",
    "txt-primary": "var(--blue-01)",
    "txt-primary-force": "var(--blue-01)",
    "bg-primary": "var(--grey-01)",
    "bg-light": "var(--white)",
  ...
```

These defaults use token variables to give more usefully named CSS variables their names. You'll see that there are
text, background, border, link and button colours defined. These usefully named variables then need CSS classes, which
are set inside of `borderColor`, `textColor` and `backgroundColor` later in the config JSON. Note the difference
between "variables" and "classes". This is important.

The `borderColor`, `textColor` and `backgroundColor` classes map to variables. The variables map to other variables,
which map to tokens. The variables can be overridden by theme and accent.

Towards the bottom of `defaults` you'll see `accent-primary`:

```JavaScript
"accent-primary": {
  "bg-primary": "var(--primary-accent)",
  "txt-primary": "var(--secondary-accent)",
  "border-primary": "var(--white-20)",
  "link": {
    "off": "var(--secondary-accent)",
    "on": "var(--white)"
  },
  "btn-primary": {
    "default": {
      "bg": "var(--secondary-accent)",
      "border": "var(--secondary-accent)",
      "txt": "var(--primary-accent)"
    },
    "hover": {
      "bg": "var(--bg-light)",
      "border": "var(--bg-light)"
    },
    "active": {
      "bg": "var(--bg-light)",
      "border": "var(--secondary-accent)"
    },
    "focus": {
      "bg": "var(--bg-light)",
      "border": "var(--bg-light)",
      "txt": "var(--primary-accent)"
    }
  }
}
```

This is step 1 of the magic. Inside here are overrides for the previously defined variables. So here you
see `bg-primary` is being set to `var(--primary-accent)` instead of its initial state of `var(--grey-01)`. And then link
and button styles are also overridden inside of `accent-primary`. The same then happens inside of the `accent-secondary`
key.

Any default variable can be overridden inside of `accent-primary` and `accent-secondary`. This allows us to control all
the colours on the default light grey background, which most of the site uses, and then also control the colours of
everything on accented backgrounds without having to add lots of utility classes into the markup based on the parent
element. Its handled by CSS variables and inheritance.

### Themes

After the defaults, you see each individual theme:

```JavaScript
"theme-default": {
  "primary-accent": "var(--blue-02)",
  "secondary-accent": "var(--blue-05)"
},
"theme-1": {
  "primary-accent": "var(--red-01)",
  "secondary-accent": "var(--blue-05)"
},
"theme-2": {
  "primary-accent": "var(--blue-03)",
  "secondary-accent": "var(--red-04)"
}
```

These are simple themes, in that they assign a token to `primary-accent` and a token to `secondary-accent` and then the
inheritance of variables defined in `defaults` does the rest for you. So inside of `theme-2`, the `primary-accent`
background will be `var(--blue-03)` and the text within it will be `var(--red-04)` (the `secondary-accent`).

When adding a new theme, make a new theme key, and assign `primary-accent` and `secondary-accent` to tokens:

```JavaScript
"theme-new": {
  "primary-accent": "var(--blue-01)",
  "secondary-accent": "var(--red-02)"
}
```

You may need to add new tokens to the `tokens` key of `/src/css/app.config.json` `color` if your theme uses colours not
yet in the system.

### More complex themes

Earlier, a theme which used two shades of red was described, and this theme needed more overrides to make text have the
correct contrast ratio for accessibility. The solution was to override the text inside of `accent-primary`
and `accent-secondary`. This, fortunately, is straight forward. Just as any variable can be overridden inside
of `accent-primary` and `accent-secondary`, any variable can also be overridden inside of a theme (and then then also
inside of `accent-primary` and `accent-secondary` inside a theme). Lets look at this red theme:

```JavaScript
"theme-red": {
  "primary-accent": "var(--red-03)",
  "secondary-accent": "var(--red-01)",
  "btn-primary": {
    "hover": {
      "txt": "var(--white)"
    },
    "active": {
      "txt": "var(--white)"
    }
  },
  "accent-primary": {
    "txt-primary": "var(--white)",
    "txt-inverse": "var(--black)",
    "link": {
      "off": "var(--white)",
      "on": "var(--white)"
    },
    "btn-primary": {
      "default": {
        "txt": "var(--white)"
      },
      "hover": {
        "txt": "var(--primary-accent)"
      },
      "active": {
        "txt": "var(--primary-accent)"
      }
    }
  },
  "accent-secondary": {
    "txt-primary": "var(--white)",
    "border-primary": "var(--white-20)",
    "link": {
      "off": "var(--white)",
      "on": "var(--white)"
    },
    "btn-primary": {
      "hover": {
        "txt": "var(--primary-accent)"
      },
      "active": {
        "txt": "var(--primary-accent)"
      }
    },
    "btn-secondary": {
      "default": {
        "border": "var(--white-20)"
      }
    }
  }
}
```

Here you can see that we're assigning red tokens to `primary-accent` and `secondary-accent`. And then we're updating the
button styles. And then we're going on to further override text, border, link and button styles inside of
the `primary-accent` and `secondary-accent` of the `theme-red`.

### \_theme.css - `/src/css/_theme.css`

For complex selector overrides you may then need add some manual CSS to achieve what you need. In the case of this "
green" theme, it was required to add:

```CSS
.theme-7 .bg-accent-primary.text-accent-secondary,
.theme-7 .before\:bg-accent-primary.text-accent-secondary,
.theme-7 .bg-accent-secondary.text-accent-primary,
.theme-7 .before\:bg-accent-secondary.text-accent-primary {
  color: var(--white);
}
```

This allowed forcing of some classes to white because by default they'd be other less accessible colours.

### None approved themes

There are a few themes in the config and CSS file that are not approved - `theme-greyscale`, `theme-pride`
, `theme-white` and `theme-black`.

These hopefully show just how customisable and themeable the site is with the `theme` keys
inside `/src/css/app.config.json` and any custom CSS help inside of `/src/css/_theme.css`.

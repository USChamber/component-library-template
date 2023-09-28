# Fixed size spacing and sizing classes

```HTML
<div class="h-60 mt-20 lg:40 px-16"></div>
```

Produces something like:

```CSS
div {
  height: 3.75rem; // 60px
  margin-top: 1.25rem; // 20px;
  padding-right: 1rem; // 16px;
  padding-left: 1rem; // 16px;
}
@media (min-width: 900px) {
  div {
    margin-top: 2.5rem; // 40px;
  }
}
```

Design specifies spacing in terms of pixels, not in terms of `rems` and so spacing/sizing classes have been updated to
work on a pixel sizing scale. The same utility classes are generated, but crucially `.mt-4` will not
produce `margin-top: 1rem;`, but instead will produce `margin-top: 0.25rem;` which is equivalent to 4px.

The scale is based on the 4 times table, which aligns with
`1 rem = 16px`, which additional entries for 0, 1px and 2px.

This way we can look at design specs, see "padding top 40px" and then use `.pt-40`.

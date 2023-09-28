# Responsive spacing classes

```HTML
<div class="mt-space-6 p-space-3"></div>
```

Produces something like:

```CSS
div {
  margin-top: 2.5rem; // 40px
  padding: 0.75rem; // 12px
}
@media (min-width: 600px) {
  div {
    margin-top: 3rem; // 48px
  }
}
@media (min-width: 900px) {
  div {
    margin-top: 4rem; // 64px
    padding-top: 0.75rem; // 12px
  }
}
```

These classes are responsive, to provide consistent spacing rules, usually used between blocks as opposed to between
text items within blocks.

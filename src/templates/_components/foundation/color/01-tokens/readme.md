# Design Color tokens

For reference only.

These colors generate CSS variables which are used when creating themes and then Tailwind classes are generated using
theme variables. See tailwind and theme notes.

To add new tokens, open `/src/css/app.config.json` and find the `color` `tokens` key and add your new token.

Some tokens, such as `blue-02` have friendly names, such as `impactful`. These color to friendly name mappings are done
in the `friendlyNameMapping` object in the config file.

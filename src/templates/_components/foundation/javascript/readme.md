# JavaScript

The majority of USCC site's JavaScript is powered by [A17 Behaviors](https://github.com/area17/a17-behaviors) - which
has a public [wiki](https://github.com/area17/a17-behaviors/wiki).

Put simply, behaviors are JavaScript functions linked to DOM nodes. Behaviors are automatically initialised on page
load (and when they're inserted into pages via ajax). And behaviors talk to each other via custom events. Interestingly,
behaviors can also lazy initialise and lazy load - so that only the behaviors needed on a page are loaded and run.

There are also a few custom helpers, new for this site to go along with some existing ones (`ajax`, `checkCMSPaths`
, `swapHost`).

Everything for USCC is scoped to `window.USCC`.

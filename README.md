# B13 Umami Statistics for the TYPO3 Backend

This extension adds a backend module to the web main module.

To enable statistics for a site the corresponding umami URL has to be set in the site configuration.

To allow backend users to view the statistics, they need access to the module as well as the root pages.
If a user does not have direct access to a root page you can also allow additional root pages via a comma separated list 
using UserTS:

```
umami.allowedRootPages = rootPageId_1,rootPageId_2
```

# Contribution Guide

The Waffle Dashboard is open to contributions

- you can open issues in GitHub and
- pose pull requests (fork the repository first)

For the code, stick to [Laravel 12](https://laravel.com/docs/12.x) and [Filament 4](https://filamentphp.com/docs/4.x/introduction/overview) conventions and general software development best practices.

## Editing the Diagrams

Instructions and context information:

- the diagrams under [docs/diagrams](diagrams) are created with the free, open-source drawing program [draw.io](https://www.drawio.com/)
- the diagrams are in the .drawio.svg format - meaning they are both `drawio` files that you can edit and `svg` files that you can embed as images
- the elements used in the diagrams come from the [Architecture Decomposition Framework (ADF)](https://architecture-decomposition-framework.github.io/en/) developed by [Fraunhofer IESE](https://www.iese.fraunhofer.de/en/aboutus.html)
  - to try the elements in the online version of diagrams.net, you can use [this link](https://app.diagrams.net/?splash=0&libs=general&clibs=Uhttps%3A%2F%2Fraw.githubusercontent.com%2Farchitecture-decomposition-framework%2Fadf-diagramsnet%2Fmain%2Flibraries%2FADF_SW%40RT.xml;Uhttps%3A%2F%2Fraw.githubusercontent.com%2Farchitecture-decomposition-framework%2Fadf-diagramsnet%2Fmain%2Flibraries%2FADF_Env%40RT.xml;Uhttps%3A%2F%2Fraw.githubusercontent.com%2Farchitecture-decomposition-framework%2Fadf-diagramsnet%2Fmain%2Flibraries%2FADF_SW%40DT.xml;Uhttps%3A%2F%2Fraw.githubusercontent.com%2Farchitecture-decomposition-framework%2Fadf-diagramsnet%2Fmain%2Flibraries%2FADF_Env%40DT.xml)
- if you are using the Visual Studio Code editor, the [Draw.io Integration](https://marketplace.visualstudio.com/items?itemName=hediet.vscode-drawio) extension allows you to edit the diagrams directly inside the editor
  - go to `File -> Preferences -> Settings` in VS Code, enable the JSON view (top right, the file icon with curved error), and insert (not replace) the settings found in [diagrams/adf-elements/settingsForLinux.txt](diagrams/adf-elements/settingsForLinux.txt) or [settingsForWindows.txt](diagrams/adf-elements/settingsForWindows.txt) (path separators differ: / vs. \\).
    - **important:** Adjust the paths accordingly! (In VS Code, you can right-click the relevant file in [diagrams/adf-elements/libraries/](diagrams/adf-elements/libraries/) and choose Copy path.)
    - a complete example of a VS Code settings file can be found in [diagrams/adf-elements/settingsForLinuxFullExample.txt](diagrams/adf-elements/settingsForLinuxFullExample.json)
    - Note: If even one path is incorrect or there is another JSON error, the `Draw.io Integration` extension will not start at all
    - **Advice (long-term solution):** Move the 4 XML files to another location on your PC - otherwise, the Draw.io Integration will only work as long as you never move or delete this project folder
- in Draw.io, you can then add the elements to the left palette by clicking `+ More Shapes` > select the `undefined` checkbox under "Custom Libraries" > `Apply`
- if you're using the Draw.io Desktop program, to make the elements available, load the XML files from the [diagrams/adf-elements/libraries/](diagrams/adf-elements/libraries/) folder as libraries via `File -> Open library`

## Adding another Language

At the time of writing, the Waffle Dashboard offers two languages: English and German.<br>
The technical admin can set the default language via the `APP_LOCALE` environment variable, and users can switch between all available ones using the language switch in the header bar.

If you want to add another language (which I’d be happy to see), follow these steps.

### 1. Add the Laravel language files

> Laravel itself only ships with English translations.<br>
> To add another language, you must install the language files.

Choose a locale from the [list of available locales](https://laravel-lang.com/available-locales-list.html) of the `laravel-lang/lang` package.<br>
Then run:

```shell
php artisan lang:add <locale>
```

This installs framework-level translations such as validation messages, dates, and pagination.

### 2. Register the locale in the language switch

The language must be explicitly enabled in the Filament language switch.

Add the new locale code to the `locales()` list in the [AppServiceProvider.php](../app/Providers/AppServiceProvider.php):

````php
LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
    $switch->locales(['en', 'de', '<locale>']);
});
````

Only locales listed here will be selectable by users.

### 3. (Not necessary) Filament translations

Filament already includes translations for many languages internally.

E.g. for German I had no reason to overwrite those, but if you don't like some Filament text translations,
feel free to run the following and edit the PHP arrays in the `/lang/vendor/filament-panels/<locale>`.

```shell
php artisan vendor:publish --tag=filament-panels-translations
```

> This spawns folders for all locales, but please only include the edited one with your language in the pull request.

### 4. Add application-specific translations

All custom application strings live in the JSON translation files `lang/<locale>.json`.

To add a new language, you can simply copy all application-specific translations below the blank line e.g. from `lang/de.json` into your new language file and translate the values.

The English text is always used as the key (don't touch that - just replace the values!).

### 5. Verify

Run the application (see [Developer Docs](../DEVELOPER-DOCS.md)), switch to the new language via the language selector, and confirm that everything looks correct.

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
    - **Advice (long-term solution):** Move the 4 XML files to another location on your PC — otherwise, the Draw.io Integration will only work as long as you never move or delete this project folder
- in Draw.io, you can then add the elements to the left palette by clicking `+ More Shapes` > select the `undefined` checkbox under "Custom Libraries" > `Apply`
- if you're using the Draw.io Desktop program, to make the elements available, load the XML files from the [diagrams/adf-elements/libraries/](diagrams/adf-elements/libraries/) folder as libraries via `File -> Open library`
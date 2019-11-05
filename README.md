## What is this?
This is small application based on PHP, which can help you easily create wordpress template based on your twig template. 

## What is twig?
Twig is a modern template engine. More information you can [find here](https://twig.symfony.com).

## How to use this lib?
For good start, you can check the example template. You can find the demo template in the folder `html/demo`.

##### What should I know during the template build?
As you might know, different CMS can have different kind of logic related to template hierarchy, but mostly all of them have base skeleton:
1. header
2. content
3. footer

So, during templates build we also use these kind of blocks:
1. **header** - for header block, which will be same for each page
2. **content** - for content block, which can be different for each page
3. **footer** - for footer block, which will be same for each page

Also we have custom blocks, which help use to identify, where is the assets links or navigation parts of template.
1. **css** - for css block defining
2. **js** - for js block defining
3. **navigation** - for navigation block defining


In demo template you can find `themer.config.json` file, where you can see base configuration for template. This configuration we use during the template build.
For example, there you can specify template name, author name and etc.
Currently library supports these kind of fields:
```json
{
  "themeName": "STANLEY",
  "author": "David Creighton-Pester",
  "authorUrl": "https://dribbble.com/wanderingbert",
  "authorEmail": "john@doe.com",
  "themeVersion": "0.1",
  "themeAlias": "demo",
  "engine": "twig"
}
```

## How to compile?
Run the command: `php run.php --path={PATH_TO_YOUR_PROJECT}`. Example: `php run.php --path=html/demo/`
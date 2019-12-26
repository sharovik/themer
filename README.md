
This is an application based on PHP, which can help you easily create wordpress template based on your twig template. 

## Before start
For a good start, you can check the example of template which you can find in the folder `html/demo`

## Requirenments
PHP 7.1+

## How to install
Clone repository `git clone git@github.com:sharovik/themer.git` or download latest version of `themer.phar` bin file.

## How to run
Run the command: `./themer.phar --path={PATH_TO_YOUR_PROJECT}`. Example: `./themer.phar --path=html/demo/`

## What is twig?
Twig is a modern template engine. More information you can [find here](https://twig.symfony.com).


##### What should you know during the template build
As you might know, different CMS can have different kind of logic related to template hierarchy, but mostly all of them have base skeleton:
1. header
2. content
3. footer

During templates build we also use these kind of blocks:
1. **header** - for header block, which will be same for each page
2. **content** - for content block, which can be different for each page
3. **footer** - for footer block, which will be same for each page

[Here you can see the example](https://github.com/sharovik/themer/blob/master/html/demo/base.html.twig#L1)

Also we have custom blocks, which help use to identify, where is the assets links or navigation parts of template.
1. **css** - for css block defining
2. **js** - for js block defining
3. **navigation** - for navigation block defining

[Here you can see the example](https://github.com/sharovik/themer/blob/master/html/demo/base.html.twig#L14)

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

## Authors
* **Pavel Simzicov** - *Initial work* - [sharovik](https://github.com/sharovik)

## Dependencies
* **TemplateMag.com** - *Stanley demo template* - [TemplateMag.com](https://templatemag.com/stanley-bootstrap-freelancer-template/)
* **Twig** - *The template language* - [Twig Team](https://twig.symfony.com/documentation)

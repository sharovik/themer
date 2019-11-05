<?php
namespace Src\Compiler;

use Src\Engine\BaseEngine;

class WordpressCompiler extends BaseCompiler
{
    protected const TYPE = 'wordpress';
    private const HEADER_FILENAME = 'header.php';
    private const FOOTER_FILENAME = 'footer.php';
    private const MENU_TITLE = 'Theme menu';
    private const MENU_ALIAS = 'theme-menu';

    /**
     * @param BaseEngine $engine
     * @param string $page
     */
    protected function buildPage(BaseEngine $engine, string $page): void
    {
        $this->pages[$page] = $page;
        $this->setAssetsConfig($engine, $page);
        $this->compileHeader($engine);
        $this->compileContent($engine, $page);
        $this->compileFooter($engine);

        $this->compilePreviewFile($engine, $page);
    }

    protected function compileHeader(BaseEngine $engine): void
    {
        static $headerWasGenerated;
        if (!empty($headerWasGenerated)) {
            return;
        }

        $header = $engine->renderBlock('header', [
            'css' => '<?php wp_head(); ?>',
            'navigation' => '<?php get_template_part(\'navigation\'); ?>',
        ]);

        if (!empty($header)) {
            $header = self::parseImages($header);
        }

        file_put_contents($this->getThemePath() . DIRECTORY_SEPARATOR . self::HEADER_FILENAME, $header);

        $headerWasGenerated = true;
    }

    private function parseImages($html)
    {
        return str_replace('src="', 'src="<?php echo get_template_directory_uri(); ?>/', $html);
    }

    protected function compileContent(BaseEngine $engine, string $page): void
    {
        $content = $engine->renderBlock('content', [
            'navigation' => '<?php get_template_part(\'navigation\'); ?>',
        ]);

        if (!empty($content)) {
            $content = self::parseImages("<?php get_header(); ?>" . PHP_EOL . $content . "<?php get_footer(); ?>");
        }

        $pageName = ($page == 'index') ? 'index.php' : "page-{$page}.php";
        file_put_contents($this->getThemePath() . DIRECTORY_SEPARATOR . $pageName, $content);
    }

    protected function compileFooter(BaseEngine $engine): void
    {
        static $footerWasGenerated;
        if (!empty($footerWasGenerated)) {
            return;
        }

        $footer = $engine->renderBlock('footer', [
            'js' => '<?php wp_footer(); ?>',
            'navigation' => '<?php get_template_part(\'navigation\'); ?>',
        ]);

        if (!empty($footer)) {
            $footer = self::parseImages($footer);
        }

        file_put_contents($this->getThemePath() . DIRECTORY_SEPARATOR . self::FOOTER_FILENAME, $footer);
        $footerWasGenerated = true;
    }

    protected function compileThemeExtraFiles(): void
    {
        //generate wordpress theme functions.php file for assets connection
        $engine = $this->container['engine_extra_files']->load($this->engine->getFullFilename('functions.html'));
        $functions = $engine->render($this->engine->getFullFilename('functions.html'), [
            'assets' => $this->assetsConfig,
            'pages' => $this->pages,
            'menu_title' => self::MENU_TITLE,
            'menu_alias' => self::MENU_ALIAS,
        ]);
        file_put_contents($this->getThemePath() . DIRECTORY_SEPARATOR . 'functions.php', $functions);

        //generate navigation template part
        $engine = $this->container['engine_extra_files']->load($this->engine->getFullFilename('navigation.html'));
        $functions = $engine->render($this->engine->getFullFilename('navigation.html'), [
            'navigation' => $this->navigation,
            'menu_title' => self::MENU_TITLE,
        ]);
        file_put_contents($this->getThemePath() . DIRECTORY_SEPARATOR . 'navigation.php', $functions);

        //generate wordpress theme style.css for theme init
        $engine = $this->container['engine_extra_files']->load($this->engine->getFullFilename('style.html'));
        $style = $engine->render($this->engine->getFullFilename('style.html'), [
            'config' => $this->container['config'],
        ]);
        file_put_contents($this->getThemePath() . DIRECTORY_SEPARATOR . 'style.css', $style);
    }

    protected function compilePreviewFile(BaseEngine $engine, string $page): void
    {
        $contents = $engine->render();
        file_put_contents($this->getPreviewPath() . DIRECTORY_SEPARATOR . $page . '.html', $contents);
    }

    protected function compileNavigation(): void
    {
        $engine = $this->engine->load($this->engine->getFullFilename('base.html'));

        if ($engine->hasBlock('navigation')) {
            $contents = $engine->renderBlock('navigation');

            $this->navigation = $this->container['navigation']->buildMenu($contents, $this->pages);
        }
    }
}

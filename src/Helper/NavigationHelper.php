<?php

namespace Src\Helper;

use DOMDocument;
use DOMElement;

class NavigationHelper
{
    public function buildMenu(string $contents, array $pages = [])
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML($contents, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $links = $dom->getElementsByTagName('a');

        $startPoint = $links->item(0);

        $navigationTemplate = $this->prepareChildNavigation($dom, $startPoint);

        $parentNode = $startPoint->parentNode->parentNode;
        $parentAttributes = $this->prepareAttributes($this->getAttributes($parentNode));

        return [
            'parent' => [
                'tag' => $parentNode->tagName,
                'attributes' => $parentAttributes,
            ],
            'childHtml' => $navigationTemplate,
        ];
    }

    public function prepareChildNavigation(DOMDocument $dom, DOMElement $startPoint)
    {
        $startPointAttributes = $this->prepareAttributes($this->getAttributes($startPoint), ['href' => 'LINK_URL',]);
        $navigationTemplate = "<{$startPoint->tagName}{$startPointAttributes}>LINK_TITLE</{$startPoint->tagName}>";

        preg_match('/.+(?=\[1\])/', $startPoint->getNodePath(), $matches);
        $query = current($matches);

        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query($query);

        $foundElements = $this->recursivePrepareChildElements($elements->item(0));
        foreach (array_reverse($foundElements) as $index => $item) {
            if ($index === 0) {
                continue;
            }

            $navigationTemplate = "<{$item['tag']}{$item['attributes']}>{$navigationTemplate}</{$item['tag']}>";
        }

        return $navigationTemplate;
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    private function recursivePrepareChildElements(DOMElement $element): array
    {
        static $elements;
        $elements[] = [
            'attributes' => $this->prepareAttributes($this->getAttributes($element)),
            'tag' => $element->tagName,
            'value' => $element->nodeValue,
        ];

        if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $childNode) {
                if (!($childNode instanceof \DOMText) && $childNode->tagName == 'a') {
                    $this->recursivePrepareChildElements($childNode);
                }
            }
        }

        return $elements;
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    private function getAttributes(DOMElement $element): array
    {
        $attributes = [];
        foreach ($element->attributes as $attribute) {
            $attributes[$attribute->name] = $attribute->value;
        }
        
        return $attributes;
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function prepareAttributes(array $attributes, array $replace = []): string
    {
        $string = '';
        foreach ($attributes as $name => $value) {
            $value = $replace[$name] ?? $value;
            $string .= " {$name}='{$value}'";
        }

        return $string;
    }
}
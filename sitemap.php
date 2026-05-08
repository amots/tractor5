<?php

/**
 * @author Amots hetzroni
 * @since 2026-04-02
 */



$urls = [
    [
        'loc' => 'https://tractor.org.il//collection',
        'changefreq' => 'monthly',
        'priority' => '1.0'
    ],
    [
        'loc' => 'https://tractor.org.il//essays',
        'changefreq' => 'yearly',
        'priority' => '0.7'
    ],
    [
        'loc' => 'https://tractor.org.il//documentation',
        'changefreq' => 'yearly',
        'priority' => '0.7'
    ],
    [
        'loc' => 'https://tractor.org.il//about',
        'changefreq' => 'yearly',
        'priority' => '0.2'
    ],
    [
        'loc' => 'https://tractor.org.il//volunteers',
        'changefreq' => 'yearly',
        'priority' => '0.3'
    ],
    [
        'loc' => 'https://tractor.org.il//volunteers',
        'changefreq' => 'yearly',
        'priority' => '0.1'
    ],
    [
        'loc' => 'https://tractor.org.il//where',
        'changefreq' => 'yearly',
        'priority' => '0.1'
    ],
    [
        'loc' => 'https://tractor.org.il//contact',
        'changefreq' => 'yearly',
        'priority' => '0.1'
    ],
];

header('Content-Type: application/xml; charset=utf-8');

$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$urlset = $xml->createElement('urlset');
$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($urls as $urlData) {
    $urlData['lastmod'] = date('Y-m-d');
    $url = $xml->createElement('url');
    foreach ($urlData as $key => $value) {
        $element = $xml->createElement($key, htmlspecialchars($value));
        $url->appendChild($element);
    }

    $urlset->appendChild($url);
}

$xml->appendChild($urlset);

echo $xml->saveXML();

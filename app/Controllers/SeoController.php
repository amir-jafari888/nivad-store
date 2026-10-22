<?php

declare(strict_types=1);

final class SeoController extends Controller
{
    public function robots(): never
    {
        $sitemapUrl =
            url('/sitemap.xml');

        Response::status(200);

        Response::header(
            'Content-Type',
            'text/plain; charset=utf-8'
        );

        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /pages/account/\n";
        echo "Disallow: /pages/auth/\n";
        echo "Disallow: /pages/shop/\n";
        echo "Sitemap: " .
            $sitemapUrl .
            "\n";

        exit;
    }

    public function sitemap(): never
    {
        $products =
            (new ProductService())
            ->findActive();

        $categories =
            (new CategoryService())
            ->findActive();

        $baseUrl =
            rtrim(
                url(),
                '/'
            );

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        $xml[] =
            $this->urlNode(
                $baseUrl . '/'
            );

        foreach ($products as $product) {
            $xml[] =
                $this->urlNode(
                    $baseUrl .
                        '/products/' .
                        rawurlencode(
                            (string)$product->slug
                        )
                );
        }

        foreach ($categories as $category) {
            $xml[] =
                $this->urlNode(
                    $baseUrl .
                        '/categories/' .
                        rawurlencode(
                            (string)$category->slug
                        )
                );
        }

        $xml[] =
            '</urlset>';

        Response::status(200);

        Response::header(
            'Content-Type',
            'application/xml; charset=utf-8'
        );

        echo implode(
            "\n",
            $xml
        );

        exit;
    }

    private function urlNode(
        string $url
    ): string {
        return
            '  <url><loc>' .
            htmlspecialchars(
                $url,
                ENT_XML1 |
                    ENT_QUOTES,
                'UTF-8'
            ) .
            '</loc></url>';
    }
}

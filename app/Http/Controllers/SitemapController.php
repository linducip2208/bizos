<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', 86400, function () {
            $urls = [];

            $urls[] = $this->url(route('home'), '1.0', 'daily');
            $urls[] = $this->url(route('docs'), '0.9', 'weekly');
            $urls[] = $this->url(url('/best-hrm-software'), '0.8', 'weekly');
            $urls[] = $this->url(url('/best-accounting-software-indonesia'), '0.8', 'weekly');
            $urls[] = $this->url(url('/best-payroll-software-indonesia'), '0.8', 'weekly');
            $urls[] = $this->url(url('/best-crm-software-indonesia'), '0.8', 'weekly');
            $urls[] = $this->url(url('/best-project-management-software'), '0.8', 'weekly');
            $urls[] = $this->url(url('/compare/bizos-vs-spreadsheet'), '0.7', 'monthly');
            $urls[] = $this->url(url('/compare/bizos-vs-talenta'), '0.7', 'monthly');
            $urls[] = $this->url(url('/compare/bizos-vs-jurnal'), '0.7', 'monthly');
            $urls[] = $this->url(url('/alternatives-to-excel-for-hr'), '0.7', 'monthly');
            $urls[] = $this->url(url('/alternatives-to-talenta'), '0.7', 'monthly');

            try {
                $companies = Company::query()
                    ->where('is_active', true)
                    ->select('slug', 'updated_at')
                    ->get();

                foreach ($companies as $company) {
                    $urls[] = $this->url(
                        url("/company/{$company->slug}"),
                        '0.6',
                        'weekly',
                        $company->updated_at
                    );
                }
            } catch (\Exception $e) {
            }

            try {
                $products = Product::query()
                    ->where('is_active', true)
                    ->select('id', 'name', 'updated_at')
                    ->limit(500)
                    ->get();

                foreach ($products as $product) {
                    $urls[] = $this->url(
                        url("/product/{$product->id}"),
                        '0.5',
                        'weekly',
                        $product->updated_at
                    );
                }
            } catch (\Exception $e) {
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($urls as $url) {
                $xml .= $url;
            }
            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\$\n";
        $content .= "Allow: /docs\n";
        $content .= "Allow: /marketing/\n";
        $content .= "Allow: /best-\n";
        $content .= "Allow: /alternatives-to-\n";
        $content .= "Allow: /compare/\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /api\n";
        $content .= "Disallow: /__pair\n";
        $content .= "Disallow: /webhooks\n";
        $content .= "Sitemap: /sitemap.xml\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    protected function url(string $loc, string $priority, string $changefreq, $lastmod = null): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
        if ($lastmod) {
            $date = is_string($lastmod) ? $lastmod : $lastmod->toIso8601String();
            $xml .= "    <lastmod>" . htmlspecialchars($date, ENT_XML1, 'UTF-8') . "</lastmod>\n";
        }
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }
}

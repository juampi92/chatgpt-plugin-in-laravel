<?php

namespace App\Http\Controllers;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * @OA\Info(title="MyBrowserAPI", version="0.1")
 */
class BrowseController
{
    /**
     * @OA\Get(
     *     path="/api/browse",
     *     summary="Get Markdown content of an URL",
     *
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="URL to fetch the HTML from",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string",
     *             example="http://example.com"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Markdown content of the URL",
     *
     *         @OA\JsonContent(
     *            type="object",
     *            required={"markdown"},
     *
     *            @OA\Property(
     *              property="markdown",
     *              type="string",
     *              description="Clean content of website."
     *           )
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        ['url' => $url] = $request->validate([
            'url' => 'required|url',
        ]);

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Referer' => 'https://www.google.com',
        ])
            ->get($url);

        return new JsonResponse([
            'markdown' => $this->parseMarkdown(
                $response->body()
            ),
        ], $response->status());
    }

    private function parseMarkdown(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        // Customize this to fit your needs
        $config->set('Core.RemoveInvalidImg', true);
        $config->set('HTML.Allowed', 'p,b,i,span,ul,ol,li,img[src],h1,h2,h3,h4,h5,h6,blockquote,pre,code,hr,br'); // a[href],
        $purifier = new HTMLPurifier($config);
        $cleanHtml = $purifier->purify($html);

        $cleanHtml = preg_replace('/-{3,}/', '--', $cleanHtml);

        $cleanHtml = strip_tags($cleanHtml, '<p><b><a><i><h1><h2><h3><h4><h5><h6><ul><ol><li>');

        $converter = new HtmlConverter();

        // Configure the converter to use "#" for headers
        $converter->getConfig()->setOption('header_style', 'atx');

        $markdown = $converter->convert($cleanHtml);

        return preg_replace("/\n{2,}/", "\n", $markdown);
    }
}

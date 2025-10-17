<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Link;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyticsController extends Controller
{
    /**
     * Public: record a page view for a catalog with simple dedup (30 minutes per ip_hash).
     */
    public function visit(Request $request): JsonResponse
    {
        try {
            $catalogId = (int) $request->input('catalog_id');
            if (!$catalogId) {
                return response()->json(['success' => false, 'message' => 'catalog_id wajib'], 422);
            }

            $catalogExists = Catalog::where('id', $catalogId)->exists();
            if (!$catalogExists) {
                return response()->json(['success' => false, 'message' => 'Catalog tidak ditemukan'], 404);
            }

            $ip = (string) $request->ip();
            $ipHash = hash('sha256', config('app.key') . '|' . $ip);
            $ua = substr((string) $request->userAgent(), 0, 255);
            $ref = substr((string) $request->headers->get('referer', ''), 0, 500);
            $utmSource = substr((string) $request->input('utm_source', ''), 0, 100);
            $utmMedium = substr((string) $request->input('utm_medium', ''), 0, 100);
            $utmCampaign = substr((string) $request->input('utm_campaign', ''), 0, 100);

            // Bot filter (basic)
            if ($this->isBot($ua)) {
                return response()->json(['success' => true, 'message' => 'Bot ignored']);
            }

            $recent = DB::table('page_views')
                ->where('catalog_id', $catalogId)
                ->where('ip_hash', $ipHash)
                ->where('visited_at', '>=', Carbon::now()->subMinutes(30))
                ->exists();

            if (!$recent) {
                DB::table('page_views')->insert([
                    'catalog_id' => $catalogId,
                    'ip_hash' => $ipHash,
                    'user_agent' => $ua,
                    'referrer' => $ref,
                    'utm_source' => $utmSource,
                    'utm_medium' => $utmMedium,
                    'utm_campaign' => $utmCampaign,
                    'visited_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat kunjungan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Protected: summary views + clicks (links + products) and daily series.
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            if (!$catalog) {
                return response()->json(['success' => false, 'message' => 'Catalog tidak ditemukan'], 404);
            }

            [$start, $days] = $this->resolveRange($request->query('range', '7d'));

            // Views series
            $views = DB::table('page_views')
                ->selectRaw('DATE(visited_at) d, COUNT(*) cnt')
                ->where('catalog_id', $catalog->id)
                ->where('visited_at', '>=', $start)
                ->groupBy('d')
                ->pluck('cnt', 'd');

            // Link clicks series
            $linkClicks = DB::table('link_clicks')
                ->selectRaw('DATE(clicked_at) d, COUNT(*) cnt')
                ->where('catalog_id', $catalog->id)
                ->where('clicked_at', '>=', $start)
                ->groupBy('d')
                ->pluck('cnt', 'd');

            // Product clicks series (existing clicks table)
            $productClicks = DB::table('clicks')
                ->selectRaw('DATE(clicked_at) d, COUNT(*) cnt')
                ->whereIn('product_id', function ($q) use ($catalog) {
                    $q->select('id')->from('products')->where('catalog_id', $catalog->id);
                })
                ->where('clicked_at', '>=', $start)
                ->groupBy('d')
                ->pluck('cnt', 'd');

            // Build day labels
            $labels = [];
            $viewsSeries = [];
            $clicksSeries = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->toDateString();
                $labels[] = $date;
                $v = (int)($views[$date] ?? 0);
                $c = (int)(($linkClicks[$date] ?? 0) + ($productClicks[$date] ?? 0));
                $viewsSeries[] = $v;
                $clicksSeries[] = $c;
            }

            $totalViews = array_sum($viewsSeries);
            $totalClicks = array_sum($clicksSeries);
            $ctr = $totalViews > 0 ? ($totalClicks / $totalViews) * 100 : 0.0;

            // Breakdown sources & devices from page_views within range
            $rows = \Illuminate\Support\Facades\DB::table('page_views')
                ->select('user_agent', 'referrer', 'utm_source')
                ->where('catalog_id', $catalog->id)
                ->where('visited_at', '>=', $start)
                ->get();

            $sources = [];
            $devices = ['mobile' => 0, 'desktop' => 0];
            foreach ($rows as $r) {
                $src = $this->detectSource((string)($r->utm_source ?? ''), (string)($r->referrer ?? ''));
                $sources[$src] = ($sources[$src] ?? 0) + 1;
                $dev = $this->detectDevice((string)($r->user_agent ?? ''));
                $devices[$dev] = ($devices[$dev] ?? 0) + 1;
            }
            arsort($sources);
            $topSources = [];
            foreach ($sources as $name => $count) {
                $topSources[] = ['name' => $name, 'count' => $count];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'totals' => [
                        'views' => $totalViews,
                        'clicks' => $totalClicks,
                        'ctr' => round($ctr, 1),
                    ],
                    'series' => [
                        'labels' => $labels,
                        'views' => $viewsSeries,
                        'clicks' => $clicksSeries,
                    ],
                    'breakdown' => [
                        'sources' => $topSources,
                        'devices' => $devices,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Protected: top links by clicks.
     */
    public function topLinks(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            [$start, $days] = $this->resolveRange($request->query('range', '7d'));

            $rows = DB::table('link_clicks')
                ->selectRaw('link_id, COUNT(*) clicks')
                ->where('catalog_id', $catalog->id)
                ->where('clicked_at', '>=', $start)
                ->groupBy('link_id')
                ->orderByDesc('clicks')
                ->limit(10)
                ->get();

            $links = Link::whereIn('id', $rows->pluck('link_id'))->get()->keyBy('id');
            $data = $rows->map(function ($r) use ($links) {
                $l = $links[$r->link_id] ?? null;
                return [
                    'id' => $r->link_id,
                    'title' => $l?->title,
                    'url' => $l?->url,
                    'clicks' => (int) $r->clicks,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil top links',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Protected: top products by clicks (existing clicks table).
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $catalog = $request->user()->catalog;
            [$start, $days] = $this->resolveRange($request->query('range', '7d'));

            $rows = DB::table('clicks')
                ->selectRaw('product_id, COUNT(*) clicks')
                ->whereIn('product_id', function ($q) use ($catalog) {
                    $q->select('id')->from('products')->where('catalog_id', $catalog->id);
                })
                ->where('clicked_at', '>=', $start)
                ->groupBy('product_id')
                ->orderByDesc('clicks')
                ->limit(10)
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil top products',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    protected function isBot(string $ua): bool
    {
        $s = strtolower($ua);
        return Str::contains($s, ['bot', 'spider', 'crawler', 'httpclient']);
    }

    protected function detectDevice(string $ua): string
    {
        $s = strtolower($ua);
        if (Str::contains($s, ['mobile', 'android', 'iphone', 'ipad'])) {
            return 'mobile';
        }
        return 'desktop';
    }

    protected function detectSource(string $utmSource, string $referrer): string
    {
        $src = strtolower(trim($utmSource));
        if ($src !== '') {
            return $src;
        }
        $r = strtolower($referrer);
        if ($r === '' || $r === null) return 'direct';
        if (str_contains($r, 'instagram')) return 'instagram';
        if (str_contains($r, 'tiktok')) return 'tiktok';
        if (str_contains($r, 'facebook') || str_contains($r, 'fb.')) return 'facebook';
        if (str_contains($r, 'wa.me') || str_contains($r, 'whatsapp')) return 'whatsapp';
        if (str_contains($r, 'x.com') || str_contains($r, 'twitter')) return 'x';
        if (str_contains($r, 'youtube')) return 'youtube';
        if (str_contains($r, 'google')) return 'google';
        return 'other';
    }

    /**
     * @return array{Carbon,int}
     */
    protected function resolveRange(string $range): array
    {
        switch ($range) {
            case '30d':
                return [Carbon::today()->subDays(29)->startOfDay(), 30];
            case '7d':
            default:
                return [Carbon::today()->subDays(6)->startOfDay(), 7];
        }
    }

    /**
     * Export summary CSV (protected)
     */
    public function exportSummary(Request $request)
    {
        $summary = $this->summary($request)->getData(true);
        if (!($summary['success'] ?? false)) {
            return $this->summary($request);
        }
        $data = $summary['data'];
        $csv = [];
        $csv[] = 'metric,value';
        $csv[] = 'views,' . ($data['totals']['views'] ?? 0);
        $csv[] = 'clicks,' . ($data['totals']['clicks'] ?? 0);
        $csv[] = 'ctr,' . ($data['totals']['ctr'] ?? 0);
        $csv[] = '';
        $csv[] = 'date,views,clicks';
        $labels = $data['series']['labels'] ?? [];
        $views = $data['series']['views'] ?? [];
        $clicks = $data['series']['clicks'] ?? [];
        for ($i = 0; $i < count($labels); $i++) {
            $csv[] = ($labels[$i] ?? '') . ',' . ($views[$i] ?? 0) . ',' . ($clicks[$i] ?? 0);
        }
        $content = implode("\n", $csv);
        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="analytics_summary.csv"',
        ]);
    }

    public function exportTopLinks(Request $request)
    {
        $res = $this->topLinks($request)->getData(true);
        if (!($res['success'] ?? false)) return $this->topLinks($request);
        $rows = $res['data'] ?? [];
        $csv = ['link_id,title,url,clicks'];
        foreach ($rows as $r) {
            $title = str_replace(',', ' ', (string)($r['title'] ?? ''));
            $url = str_replace(',', ' ', (string)($r['url'] ?? ''));
            $csv[] = ($r['id'] ?? '') . ',' . $title . ',' . $url . ',' . ($r['clicks'] ?? 0);
        }
        return response(implode("\n", $csv), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="analytics_top_links.csv"',
        ]);
    }

    public function exportTopProducts(Request $request)
    {
        $res = $this->topProducts($request)->getData(true);
        if (!($res['success'] ?? false)) return $this->topProducts($request);
        $rows = $res['data'] ?? [];
        $csv = ['product_id,clicks'];
        foreach ($rows as $r) {
            $csv[] = ($r['product_id'] ?? $r->product_id ?? '') . ',' . ($r['clicks'] ?? $r->clicks ?? 0);
        }
        return response(implode("\n", $csv), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="analytics_top_products.csv"',
        ]);
    }
}

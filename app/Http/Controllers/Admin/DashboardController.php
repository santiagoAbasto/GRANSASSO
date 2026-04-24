<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ContactMessage;
use App\Models\Gearbox;
use App\Models\NewsletterSubscriber;
use App\Models\PrivateUser;
use App\Models\PrivateUserLoginEvent;
use App\Models\Product;
use App\Models\SiteVisitEvent;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $thirtyDaysAgo = now()->subDays(29)->startOfDay();
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $previousSevenDaysAgo = now()->subDays(13)->startOfDay();
        $previousSevenDaysEnd = now()->subDays(7)->endOfDay();

        $recentMessages = ContactMessage::latest()->limit(5)->get()->map(fn ($m) => [
            'id'         => $m->id,
            'name'       => $m->name,
            'email'      => $m->email,
            'company'    => $m->company,
            'is_read'    => $m->is_read,
            'created_at' => $m->created_at->format('d/m/Y H:i'),
        ]);

        $recentLogins = PrivateUserLoginEvent::with('user:id,name,lastname,email')
            ->orderByDesc('logged_in_at')
            ->limit(6)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'name' => trim(($event->user?->name ?? '') . ' ' . ($event->user?->lastname ?? '')),
                'email' => $event->user?->email,
                'ip_address' => $event->ip_address,
                'logged_in_at' => optional($event->logged_in_at)->format('d/m/Y H:i'),
            ]);

        $loginEvents = PrivateUserLoginEvent::query()
            ->where('logged_in_at', '>=', $thirtyDaysAgo)
            ->get(['private_user_id', 'logged_in_at']);

        $siteVisits = SiteVisitEvent::query()
            ->where('visited_at', '>=', $thirtyDaysAgo)
            ->get(['session_id', 'visited_at']);

        $dailyLoginsMap = $loginEvents
            ->groupBy(fn ($event) => $event->logged_in_at->format('Y-m-d'))
            ->map->count();

        $dailyUniqueVisitorsMap = $loginEvents
            ->groupBy(fn ($event) => $event->logged_in_at->format('Y-m-d'))
            ->map(fn ($events) => $events->pluck('private_user_id')->filter()->unique()->count());

        $dailyVisitsMap = $siteVisits
            ->groupBy(fn ($event) => $event->visited_at->format('Y-m-d'))
            ->map->count();

        $dailyVisitVisitorsMap = $siteVisits
            ->groupBy(fn ($event) => $event->visited_at->format('Y-m-d'))
            ->map(fn ($events) => $events->pluck('session_id')->filter()->unique()->count());

        $dailyLogins = collect(range(0, 29))->map(function ($offset) use ($thirtyDaysAgo, $dailyLoginsMap) {
            $date = $thirtyDaysAgo->copy()->addDays($offset);
            $key = $date->format('Y-m-d');

            return [
                'date' => $key,
                'label' => $date->format('d/m'),
                'value' => $dailyLoginsMap[$key] ?? 0,
            ];
        })->values();

        $dailyTraffic = collect(range(0, 29))->map(function ($offset) use ($thirtyDaysAgo, $dailyVisitsMap, $dailyVisitVisitorsMap) {
            $date = $thirtyDaysAgo->copy()->addDays($offset);
            $key = $date->format('Y-m-d');

            return [
                'date' => $key,
                'label' => $date->format('d/m'),
                'sessions' => $dailyVisitsMap[$key] ?? 0,
                'visitors' => $dailyVisitVisitorsMap[$key] ?? 0,
            ];
        })->values();

        $dailyLogins7 = $dailyLogins->where('date', '>=', $sevenDaysAgo->format('Y-m-d'))->values();

        $monthlyRegistrationsMap = PrivateUser::query()
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn ($user) => Carbon::parse($user->created_at)->format('Y-m'))
            ->map->count();

        $monthlyRegistrations = collect(range(0, 5))->map(function ($offset) use ($monthlyRegistrationsMap) {
            $date = now()->copy()->subMonths(5 - $offset)->startOfMonth();
            $key = $date->format('Y-m');

            return [
                'month' => $key,
                'label' => ucfirst($date->translatedFormat('M')),
                'value' => $monthlyRegistrationsMap[$key] ?? 0,
            ];
        })->values();

        $privateUsers = PrivateUser::query();
        $approvedClients = (clone $privateUsers)->where('status', 'approved')->count();
        $pendingClients = (clone $privateUsers)->where('status', 'pending')->count();
        $rejectedClients = (clone $privateUsers)->where('status', 'rejected')->count();

        $loginEvents7d = PrivateUserLoginEvent::query()->where('logged_in_at', '>=', now()->subDays(7))->count();
        $activeClients30d = PrivateUserLoginEvent::query()
            ->where('logged_in_at', '>=', now()->subDays(30))
            ->distinct('private_user_id')
            ->count('private_user_id');
        $visits7d = SiteVisitEvent::query()
            ->where('visited_at', '>=', $sevenDaysAgo)
            ->count();
        $visitsToday = SiteVisitEvent::query()
            ->where('visited_at', '>=', $today)
            ->count();
        $uniqueSiteVisitors7d = SiteVisitEvent::query()
            ->where('visited_at', '>=', $sevenDaysAgo)
            ->distinct('session_id')
            ->count('session_id');
        $uniqueSiteVisitorsToday = SiteVisitEvent::query()
            ->where('visited_at', '>=', $today)
            ->distinct('session_id')
            ->count('session_id');
        $uniqueSiteVisitorsPrevious7d = SiteVisitEvent::query()
            ->whereBetween('visited_at', [$previousSevenDaysAgo, $previousSevenDaysEnd])
            ->distinct('session_id')
            ->count('session_id');
        $visitsPrevious7d = SiteVisitEvent::query()
            ->whereBetween('visited_at', [$previousSevenDaysAgo, $previousSevenDaysEnd])
            ->count();
        $todayLogins = PrivateUserLoginEvent::query()
            ->where('logged_in_at', '>=', $today)
            ->count();
        $todayVisitors = PrivateUserLoginEvent::query()
            ->where('logged_in_at', '>=', $today)
            ->distinct('private_user_id')
            ->count('private_user_id');
        $uniqueVisitors7d = PrivateUserLoginEvent::query()
            ->where('logged_in_at', '>=', $sevenDaysAgo)
            ->distinct('private_user_id')
            ->count('private_user_id');
        $uniqueVisitorsPrevious7d = PrivateUserLoginEvent::query()
            ->whereBetween('logged_in_at', [$previousSevenDaysAgo, $previousSevenDaysEnd])
            ->distinct('private_user_id')
            ->count('private_user_id');
        $sessionsPrevious7d = PrivateUserLoginEvent::query()
            ->whereBetween('logged_in_at', [$previousSevenDaysAgo, $previousSevenDaysEnd])
            ->count();
        $sessions30d = $loginEvents->count();
        $siteVisits30d = $siteVisits->count();
        $averageDailyVisits30d = round($siteVisits30d / 30, 1);
        $engagementRate30d = $activeClients30d > 0
            ? round(($sessions30d / $activeClients30d), 1)
            : 0;
        $trafficTrend7d = $visitsPrevious7d > 0
            ? round((($visits7d - $visitsPrevious7d) / $visitsPrevious7d) * 100, 1)
            : null;
        $visitorTrend7d = $uniqueSiteVisitorsPrevious7d > 0
            ? round((($uniqueSiteVisitors7d - $uniqueSiteVisitorsPrevious7d) / $uniqueSiteVisitorsPrevious7d) * 100, 1)
            : null;

        $latestLoginAt = PrivateUserLoginEvent::query()->max('logged_in_at');

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'brands' => Brand::count(),
                'gearboxes' => Gearbox::count(),
                'products' => Product::count(),
                'messages' => ContactMessage::count(),
                'unread_messages' => ContactMessage::unread()->count(),
                'subscribers' => NewsletterSubscriber::count(),
                'approved_clients' => $approvedClients,
                'pending_clients' => $pendingClients,
                'rejected_clients' => $rejectedClients,
                'logins_7d' => $loginEvents7d,
                'logins_today' => $todayLogins,
                'visitors_today' => $todayVisitors,
                'visits_7d' => $visits7d,
                'visits_today' => $visitsToday,
                'unique_site_visitors_7d' => $uniqueSiteVisitors7d,
                'unique_site_visitors_today' => $uniqueSiteVisitorsToday,
                'unique_site_visitors_previous_7d' => $uniqueSiteVisitorsPrevious7d,
                'unique_visitors_7d' => $uniqueVisitors7d,
                'unique_visitors_previous_7d' => $uniqueVisitorsPrevious7d,
                'active_clients_30d' => $activeClients30d,
                'sessions_30d' => $sessions30d,
                'site_visits_30d' => $siteVisits30d,
                'avg_daily_sessions_30d' => $averageDailyVisits30d,
                'engagement_rate_30d' => $engagementRate30d,
                'traffic_trend_7d' => $trafficTrend7d,
                'visitor_trend_7d' => $visitorTrend7d,
                'latest_login_at' => $latestLoginAt ? Carbon::parse($latestLoginAt)->format('d/m/Y H:i') : null,
            ],
            'charts' => [
                'daily_logins' => $dailyLogins7,
                'daily_traffic' => $dailyTraffic,
                'monthly_registrations' => $monthlyRegistrations,
                'client_status' => [
                    ['label' => 'Aprobados', 'value' => $approvedClients, 'color' => '#25A7CA'],
                    ['label' => 'Pendientes', 'value' => $pendingClients, 'color' => '#F59E0B'],
                    ['label' => 'Rechazados', 'value' => $rejectedClients, 'color' => '#EF4444'],
                ],
            ],
            'recent_messages' => $recentMessages,
            'recent_logins' => $recentLogins,
        ]);
    }
}

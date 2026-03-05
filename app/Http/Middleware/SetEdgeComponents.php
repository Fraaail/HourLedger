<?php

namespace App\Http\Middleware;

use App\Models\EntryNotification;
use Closure;
use Illuminate\Http\Request;
use Native\Mobile\Edge\Edge;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets native EDGE UI components (BottomNav, TopBar) on every request.
 *
 * EDGE is NativePHP for Mobile's component system that renders native
 * Android/iOS UI elements outside the WebView. This middleware runs
 * before RenderEdgeComponents (which calls Edge::set()) to ensure
 * the native navigation is always present.
 */
class SetEdgeComponents
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->addBottomNav($request);
        $this->addTopBar($request);

        return $next($request);
    }

    /**
     * Add the native bottom navigation bar with three tabs.
     */
    protected function addBottomNav(Request $request): void
    {
        $unreadCount = EntryNotification::unread()->count();

        $contextIndex = Edge::startContext();

        Edge::add('bottom_nav_item', [
            'id' => 'dashboard',
            'icon' => 'dashboard',
            'url' => '/',
            'label' => 'Dashboard',
            'active' => $request->is('/') || $request->routeIs('dashboard'),
        ]);

        Edge::add('bottom_nav_item', [
            'id' => 'calendar',
            'icon' => 'calendar_month',
            'url' => '/calendar',
            'label' => 'Calendar',
            'active' => $request->is('calendar*'),
        ]);

        Edge::add('bottom_nav_item', [
            'id' => 'notifications',
            'icon' => 'notifications',
            'url' => '/notifications',
            'label' => 'Alerts',
            'active' => $request->is('notifications*'),
            'badge' => $unreadCount > 0 ? (string) $unreadCount : null,
        ]);

        Edge::endContext($contextIndex, 'bottom_nav', [
            'label_visibility' => 'labeled',
            'id' => 'bottom_nav',
        ]);
    }

    /**
     * Add the native top bar with contextual title.
     */
    protected function addTopBar(Request $request): void
    {
        $title = $this->resolveTitle($request);

        Edge::add('top_bar', array_filter([
            'title' => $title,
            'showNavigationIcon' => false,
        ]));
    }

    /**
     * Resolve the page title based on the current route.
     */
    protected function resolveTitle(Request $request): string
    {
        if ($request->routeIs('dashboard') || $request->is('/')) {
            return 'HourLedger';
        }

        if ($request->is('calendar*')) {
            return 'Calendar';
        }

        if ($request->is('notifications*')) {
            return 'Notifications';
        }

        return 'HourLedger';
    }
}

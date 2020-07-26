<?php

namespace DigitalCreative\NovaDashboard;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaDashboard extends Tool
{

    private Collection $dashboards;
    private bool $useNavigation = true;

    /**
     * Create a new element.
     *
     * @param array $dashboards
     */
    public function __construct(array $dashboards = [])
    {

        $this->dashboards = collect($dashboards)->filter(function (Dashboard $dashboard) {
            return $dashboard->authorizedToSee(request());
        });

        parent::__construct(null);

    }

    public function withoutNavigationMenu(): self
    {
        $this->useNavigation = false;

        return $this;
    }

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-dashboard', __DIR__ . '/../dist/js/tool.js');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return View|null
     */
    public function renderNavigation(): ?View
    {

        if ($this->dashboards->isNotEmpty() && $this->useNavigation) {

            return view('nova-dashboard::navigation', [ 'dashboards' => $this->dashboards ]);

        }

        return null;

    }

    public function getCurrentActiveDashboard(string $resourceUri): ?Dashboard
    {

        /**
         * @var Dashboard $dashboard
         */
        foreach ($this->dashboards as $dashboard) {

            if ($dashboard::uriKey() === $resourceUri) {

                return new $dashboard();

            }

        }

        return null;

    }

}
<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\Services;

use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\CatalogIntegration;

class DeleteLocalProductService
{
    /**
     * The mapping entry for the given local ID is automatically removed via `delete_post` hook in {@see LocalProductDeletedInterceptor}.
     */
    public function delete(int $localId) : void
    {
        // disable reads, because `wp_delete_post()` issues a `get_post()` call that we do not need to be routed to the platform
        CatalogIntegration::withoutReads(fn () => wp_delete_post($localId, true));
    }
}

<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\V2\Catalog\Providers\GoDaddy\Http\GraphQL\Queries;

use GoDaddy\WordPress\MWC\Common\Http\GraphQL\AbstractGraphQLOperation;

/**
 * Query retrieves v1 category reference to enable mapping v2 lists.
 */
class ListsReferencesOperation extends AbstractGraphQLOperation
{
    protected $operation = 'query GetLists($first: Int!, $after: String, $referenceValues: [String!]!) {
  lists(
    first: $first
    after: $after
    referenceValue: {
      in: $referenceValues
    }
  ) {
    edges {
      node {
        id
        name
        references {
          edges {
            node {
              origin
              id
              value
            }
          }
        }
      }
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}';
}

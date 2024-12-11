<?php declare(strict_types=1);

namespace Everyware\Concepts\Wordpress;

use Everyware\Concepts\Wordpress\Contracts\WpTermRepository;
use WP_Error;
use WP_Term;

/**
 * Class TermRepository
 */
class TermRepository implements WpTermRepository
{
    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_count_terms/
     *
     * @param string       $taxonomy
     * @param array|string $args
     *
     * @return array|int|WP_Error
     */
    public function countTerms($taxonomy, $args = [])
    {
        return wp_count_terms($taxonomy, $args);
    }

    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_delete_term/
     *
     * @param int          $term     Term ID.
     * @param string       $taxonomy Taxonomy Name.
     * @param array|string $args
     *
     * @return bool|int|WP_Error
     *
     */
    public function deleteTerm($term, $taxonomy, $args = [])
    {
        return wp_delete_term($term, $taxonomy, $args);
    }

    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_insert_term/
     *
     * @param string       $term
     * @param string       $taxonomy
     * @param array|string $args
     *
     * @return array|WP_Error
     */
    public function insertTerm($term, $taxonomy, $args = [])
    {
        return wp_insert_term($term, $taxonomy, $args);
    }

    /**
     * @see   https://developer.wordpress.org/reference/functions/is_wp_error/
     *
     * @param mixed $thing Check if unknown variable is a WP_Error object.
     *
     * @return bool True, if WP_Error. False, if not WP_Error.
     */
    public function isError($thing): bool
    {
        return is_wp_error($thing);
    }

    /**
     * @see   https://developer.wordpress.org/reference/functions/get_term/
     *
     * @param int|WP_Term|object $term
     * @param string             $taxonomy
     * @param string             $output
     * @param string             $filter
     *
     * @return array|WP_Term|WP_Error|null
     */
    public function getTerm($term, $taxonomy = '', $output = OBJECT, $filter = 'raw')
    {
        return get_term($term, $taxonomy, $output, $filter);
    }

    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_update_term/
     *
     * @param int          $term_id
     * @param string       $taxonomy
     * @param array|string $args
     *
     * @return array|WP_Error
     */
    public function updateTerm($term_id, $taxonomy, $args = [])
    {
        return wp_update_term($term_id, $taxonomy, $args);
    }
}

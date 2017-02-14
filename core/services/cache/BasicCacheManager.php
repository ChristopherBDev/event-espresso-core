<?php
namespace EventEspresso\core\services\cache;

use Closure;

defined('EVENT_ESPRESSO_VERSION') || exit;



/**
 * Class BasicCacheManager
 * Controls the creation and deletion of cached content
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.31
 */
class BasicCacheManager implements CacheManagerInterface
{

    /**
     * @type string
     */
    const CACHE_PREFIX = 'ee_cache_';

    /**
     * set to true to monitor when content is being served from cache or not
     *
     * @type boolean
     */
    const DEBUG = true;

    /**
     * @var CacheStorageInterface $cache_storage
     */
    private $cache_storage;



    /**
     * BasicCacheManager constructor.
     *
     * @param CacheStorageInterface $cache_storage [required]
     */
    public function __construct(CacheStorageInterface $cache_storage)
    {
        $this->cache_storage = $cache_storage;
    }



    /**
     * returns a string that will be prepended to all cache identifiers
     *
     * @return string
     */
    public function cachePrefix()
    {
        return BasicCacheManager::CACHE_PREFIX;
    }


    /**
     * @param string  $id_prefix [required] Appended to all cache IDs. Can be helpful in finding specific cache types.
     *                           May also be helpful to include an additional specific identifier,
     *                           such as a post ID as part of the $id_prefix so that individual caches
     *                           can be found and/or cleared. ex: "venue-28", or "shortcode-156".
     *                           BasicCacheManager::CACHE_PREFIX will also be appended to the cache id.
     * @param string  $cache_id  [required] Additional identifying details that make this cache unique.
     *                           It is advisable to use some of the actual data
     *                           that is used to generate the content being cached,
     *                           in order to guarantee that the cache id is unique for that content.
     *                           The cache id will be md5'd before usage to make it more db friendly,
     *                           and the entire cache id string will be truncated to 190 characters.
     * @param Closure $callback  [required] since the point of caching is to avoid generating content when not
     *                           necessary,
     *                           we wrap our content creation in a Closure so that it is not executed until needed.
     * @param int $expiration
     * @return Closure|mixed
     */
    public function get($id_prefix, $cache_id, Closure $callback, $expiration = HOUR_IN_SECONDS)
    {
        $content = '';
        // how long should we cache this shortcode's content for? 0 means no caching.
        $expiration = absint(
            apply_filters(
                'FHEE__EventEspresso_core_services_shortcodes_EspressoShortcode__shortcodeContent__cache_expiration',
                $expiration,
                $id_prefix,
                $cache_id
            )
        );
        $cache_id = substr($this->cachePrefix() . $id_prefix . '-' . md5($cache_id), 0, 182);
        // is caching enabled for this shortcode ?
        if ($expiration) {
            $content = $this->cache_storage->get($cache_id);
        }
        // any existing content ?
        if (empty($content)) {
            // nope! let's generate some new stuff
            $content = $callback();
            // save the new content if caching is enabled
            if ($expiration) {
                if (BasicCacheManager::DEBUG) {
                    \EEH_Debug_Tools::printr($cache_id, 'REFRESH CACHE', __FILE__, __LINE__);
                }
                $this->cache_storage->add($cache_id, $content, $expiration);
            }
        } else {
            if (BasicCacheManager::DEBUG) {
                \EEH_Debug_Tools::printr($cache_id, 'CACHED CONTENT', __FILE__, __LINE__);
            }
        }
        return $content;
    }



    /**
     * @param array|string $cache_id [required] Could be an ID prefix affecting many caches
     *                               or a specific ID targeting a single cache item
     * @return void
     */
    public function clear($cache_id)
    {
        // ensure incoming arg is in an array
        $cache_id = is_array($cache_id) ? $cache_id : array($cache_id);
        // delete corresponding transients for the supplied id prefix
        $this->cache_storage->deleteMany($cache_id);
    }




}
// End of file BasicCacheManager.php
// Location: core/services/cache/BasicCacheManager.php
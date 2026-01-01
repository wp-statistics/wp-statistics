<?php

namespace WP_Statistics\Testing\Simulator;

use WP_Statistics\Records\RecordFactory;

/**
 * ResourceProvisioner - Ensures WordPress has content for tracking simulation
 *
 * Checks for existing posts/pages and creates sample content if needed.
 * Also handles user provisioning for logged-in visitor simulation.
 *
 * @package WP_Statistics\Testing\Simulator
 * @since 15.0.0
 */
class ResourceProvisioner
{
    /**
     * Minimum number of posts required
     */
    private int $minPosts = 10;

    /**
     * Minimum number of pages required
     */
    private int $minPages = 5;

    /**
     * Minimum number of users for logged-in simulation
     */
    private int $minUsers = 5;

    /**
     * Logger callback
     * @var callable|null
     */
    private $logger;

    /**
     * Cached resources (posts with resource_uri_id)
     * @var array
     */
    private array $resources = [];

    /**
     * Cached users for logged-in simulation
     * @var array
     */
    private array $users = [];

    /**
     * Sample post titles for auto-generation
     */
    private const SAMPLE_POST_TITLES = [
        "Getting Started Guide",
        "Product Features Overview",
        "How to Use Our Service",
        "Pricing and Plans",
        "Customer Success Stories",
        "FAQ - Frequently Asked Questions",
        "Contact Us",
        "About Our Company",
        "Latest News and Updates",
        "Tips and Best Practices",
        "Troubleshooting Common Issues",
        "Advanced Configuration Guide",
        "API Documentation",
        "Release Notes",
        "Community Guidelines",
    ];

    /**
     * Sample page titles for auto-generation
     */
    private const SAMPLE_PAGE_TITLES = [
        "Home",
        "Services",
        "Blog",
        "Privacy Policy",
        "Terms of Service",
        "Contact",
        "About",
        "Careers",
    ];

    /**
     * Sample categories
     */
    private const SAMPLE_CATEGORIES = [
        "Tutorials",
        "News",
        "Updates",
        "Tips",
        "Guides",
    ];

    /**
     * Constructor
     *
     * @param callable|null $logger Optional logger callback
     * @param int $minPosts Minimum posts required
     * @param int $minPages Minimum pages required
     * @param int $minUsers Minimum users required
     */
    public function __construct(
        ?callable $logger = null,
        int $minPosts = 10,
        int $minPages = 5,
        int $minUsers = 5
    ) {
        $this->logger = $logger;
        $this->minPosts = $minPosts;
        $this->minPages = $minPages;
        $this->minUsers = $minUsers;
    }

    /**
     * Log a message
     *
     * @param string $message
     */
    private function log(string $message): void
    {
        if ($this->logger) {
            call_user_func($this->logger, $message);
        }
    }

    /**
     * Ensure all required resources exist
     *
     * @return array Array of trackable resources
     */
    public function ensureResources(): array
    {
        $this->log("[Resources] Checking WordPress content...");

        // Check and create posts
        $posts = $this->getExistingPosts();
        if (count($posts) < $this->minPosts) {
            $needed = $this->minPosts - count($posts);
            $this->log("[Resources] Found " . count($posts) . " posts, creating {$needed} more...");
            $this->createSamplePosts($needed);
            $posts = $this->getExistingPosts();
        }
        $this->log("[Resources] Posts: " . count($posts));

        // Check and create pages
        $pages = $this->getExistingPages();
        if (count($pages) < $this->minPages) {
            $needed = $this->minPages - count($pages);
            $this->log("[Resources] Found " . count($pages) . " pages, creating {$needed} more...");
            $this->createSamplePages($needed);
            $pages = $this->getExistingPages();
        }
        $this->log("[Resources] Pages: " . count($pages));

        // Prepare resources with resource_uri_id
        $this->prepareResources(array_merge($posts, $pages));

        return $this->resources;
    }

    /**
     * Ensure users exist for logged-in visitor simulation
     *
     * @return array Array of user IDs with roles
     */
    public function ensureUsers(): array
    {
        $this->log("[Resources] Checking WordPress users...");

        $existingUsers = $this->getExistingUsers();

        if (count($existingUsers) < $this->minUsers) {
            $needed = $this->minUsers - count($existingUsers);
            $this->log("[Resources] Found " . count($existingUsers) . " users, creating {$needed} more...");
            $this->createSampleUsers($needed);
            $existingUsers = $this->getExistingUsers();
        }

        $this->users = $existingUsers;
        $this->log("[Resources] Users: " . count($this->users));

        return $this->users;
    }

    /**
     * Get existing published posts
     *
     * @return array
     */
    private function getExistingPosts(): array
    {
        return get_posts([
            'posts_per_page' => -1,
            'post_type'      => 'post',
            'post_status'    => 'publish',
        ]);
    }

    /**
     * Get existing published pages
     *
     * @return array
     */
    private function getExistingPages(): array
    {
        return get_posts([
            'posts_per_page' => -1,
            'post_type'      => 'page',
            'post_status'    => 'publish',
        ]);
    }

    /**
     * Get existing users
     *
     * @return array Array of user data with ID and role
     */
    private function getExistingUsers(): array
    {
        $users = get_users([
            'number' => 100,
            'fields' => ['ID', 'display_name', 'user_email'],
        ]);

        $result = [];
        foreach ($users as $user) {
            $userData = get_userdata($user->ID);
            $result[] = [
                'id'    => $user->ID,
                'name'  => $user->display_name,
                'email' => $user->user_email,
                'role'  => $userData->roles[0] ?? 'subscriber',
            ];
        }

        return $result;
    }

    /**
     * Create sample posts
     *
     * @param int $count Number of posts to create
     */
    private function createSamplePosts(int $count): void
    {
        $existingTitles = array_map(
            fn($p) => $p->post_title,
            $this->getExistingPosts()
        );

        $availableTitles = array_diff(self::SAMPLE_POST_TITLES, $existingTitles);
        $titlesToUse = array_slice(array_values($availableTitles), 0, $count);

        // If we need more titles than available, generate numbered ones
        while (count($titlesToUse) < $count) {
            $num = count($titlesToUse) + 1;
            $titlesToUse[] = "Sample Post #{$num}";
        }

        // Ensure categories exist
        $this->ensureCategories();

        foreach ($titlesToUse as $title) {
            $postId = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $this->generateSampleContent($title),
                'post_status'  => 'publish',
                'post_type'    => 'post',
                'post_author'  => 1,
            ]);

            if ($postId && !is_wp_error($postId)) {
                // Assign random category
                $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
                if (!empty($categories) && !is_wp_error($categories)) {
                    $randomCat = $categories[array_rand($categories)];
                    wp_set_post_categories($postId, [$randomCat->term_id]);
                }

                $this->log("[Resources] Created post: {$title} (ID: {$postId})");
            }
        }
    }

    /**
     * Create sample pages
     *
     * @param int $count Number of pages to create
     */
    private function createSamplePages(int $count): void
    {
        $existingTitles = array_map(
            fn($p) => $p->post_title,
            $this->getExistingPages()
        );

        $availableTitles = array_diff(self::SAMPLE_PAGE_TITLES, $existingTitles);
        $titlesToUse = array_slice(array_values($availableTitles), 0, $count);

        // If we need more titles than available, generate numbered ones
        while (count($titlesToUse) < $count) {
            $num = count($titlesToUse) + 1;
            $titlesToUse[] = "Sample Page #{$num}";
        }

        foreach ($titlesToUse as $title) {
            $pageId = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $this->generateSampleContent($title),
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
            ]);

            if ($pageId && !is_wp_error($pageId)) {
                $this->log("[Resources] Created page: {$title} (ID: {$pageId})");
            }
        }
    }

    /**
     * Create sample users for logged-in simulation
     *
     * @param int $count Number of users to create
     */
    private function createSampleUsers(int $count): void
    {
        $roles = ['subscriber', 'subscriber', 'subscriber', 'author', 'editor'];

        for ($i = 0; $i < $count; $i++) {
            $num = $i + 1;
            $role = $roles[$i % count($roles)];

            $userId = wp_create_user(
                "testuser_{$num}",
                wp_generate_password(),
                "testuser{$num}@example.com"
            );

            if ($userId && !is_wp_error($userId)) {
                $user = new \WP_User($userId);
                $user->set_role($role);

                wp_update_user([
                    'ID'           => $userId,
                    'display_name' => "Test User {$num}",
                    'first_name'   => "Test",
                    'last_name'    => "User {$num}",
                ]);

                $this->log("[Resources] Created user: testuser_{$num} ({$role})");
            }
        }
    }

    /**
     * Ensure sample categories exist
     */
    private function ensureCategories(): void
    {
        foreach (self::SAMPLE_CATEGORIES as $catName) {
            if (!term_exists($catName, 'category')) {
                wp_insert_term($catName, 'category');
            }
        }
    }

    /**
     * Generate sample content for a post/page
     *
     * @param string $title Post title
     * @return string Generated content
     */
    private function generateSampleContent(string $title): string
    {
        $paragraphs = [
            "Welcome to {$title}. This is sample content generated for WP Statistics testing purposes.",
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
            "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
            "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.",
        ];

        return implode("\n\n", $paragraphs);
    }

    /**
     * Prepare resources with WP Statistics resource_uri records
     *
     * @param array $posts Array of WP_Post objects
     */
    private function prepareResources(array $posts): void
    {
        $this->resources = [];

        foreach ($posts as $post) {
            // Create or get resource record
            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => $post->post_type,
                'resource_id'   => $post->ID,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type'    => $post->post_type,
                    'resource_id'      => $post->ID,
                    'cached_title'     => $post->post_title,
                    'cached_author_id' => $post->post_author,
                    'cached_date'      => $post->post_date,
                ]);
            }

            // Get permalink path (without domain)
            $uri = str_replace(home_url(), '', get_permalink($post->ID));

            // Create or get resource_uri record
            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri'         => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri'         => $uri,
                ]);
            }

            $this->resources[] = [
                'resource_id'     => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id'         => $post->ID,
                'post_type'       => $post->post_type,
                'uri'             => $uri,
                'title'           => $post->post_title,
                'author_id'       => $post->post_author,
            ];
        }
    }

    /**
     * Get cached resources
     *
     * @return array
     */
    public function getResources(): array
    {
        if (empty($this->resources)) {
            return $this->ensureResources();
        }
        return $this->resources;
    }

    /**
     * Set resources directly (mainly for testing)
     *
     * @param array $resources Array of resource data
     */
    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * Set users directly (mainly for testing)
     *
     * @param array $users Array of user data with 'id' and 'role' keys
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    /**
     * Get cached users
     *
     * @return array
     */
    public function getUsers(): array
    {
        if (empty($this->users)) {
            return $this->ensureUsers();
        }
        return $this->users;
    }

    /**
     * Get a random resource
     *
     * @return array|null
     */
    public function getRandomResource(): ?array
    {
        $resources = $this->getResources();
        if (empty($resources)) {
            return null;
        }
        return $resources[array_rand($resources)];
    }

    /**
     * Get a random user ID for logged-in simulation
     *
     * @param array $roleWeights Optional role weights (e.g., ['administrator' => 5, 'subscriber' => 50])
     * @return int|null User ID or null for guest
     */
    public function getRandomUserId(array $roleWeights = []): ?int
    {
        $users = $this->getUsers();
        if (empty($users)) {
            return null;
        }

        if (!empty($roleWeights)) {
            // Filter users by role and apply weights
            $weightedUsers = [];
            foreach ($users as $user) {
                $weight = $roleWeights[$user['role']] ?? 1;
                for ($i = 0; $i < $weight; $i++) {
                    $weightedUsers[] = $user['id'];
                }
            }
            if (!empty($weightedUsers)) {
                return $weightedUsers[array_rand($weightedUsers)];
            }
        }

        return $users[array_rand($users)]['id'];
    }

    /**
     * Print resource summary
     */
    public function printSummary(): void
    {
        echo "[Resources] Posts: " . count($this->getResources()) . "\n";
        echo "[Resources] Users: " . count($this->getUsers()) . "\n";
    }
}

<?php

namespace WP_Statistics\Models;

class AuthorModel extends DataProvider
{
    /**
     * Calculates the average number of posts per author based on the given arguments.
     *
     * @param array $args An array of arguments to filter the count.
     * @return float|int The average number of posts per author. Returns 0 if no authors are found.
     */
    public function averagePostsPerAuthor($args = [])
    {
        $sql = "SELECT COUNT(ID) FROM {$this->db->posts} WHERE post_status = 'publish'";
        $sql .= $this->generateSqlConditions($args);

        $totalPosts   = $this->getVar($sql);
        $totalAuthors = $this->count();

        return $totalPosts ? $totalPosts / $totalAuthors : 0;
    }

    /**
     * Counts the authors based on the given arguments. 
     * By default, it will return total number of authors.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public function count($args = [])
    {
        $sql = "SELECT COUNT(DISTINCT post_author) FROM {$this->db->posts} WHERE post_status = 'publish'";
        $sql .= $this->generateSqlConditions($args);

        $result = $this->getVar($sql);

        return $result ? $result : 0;
    }

}
<?php

use WP_Statistics\Utils\Query;

class Test_QueryInList extends WP_UnitTestCase
{
    public function test_comma_separated_in_values_are_trimmed()
    {
        $spaced = Query::select('ID')->from('posts')->where('post_type', 'IN', 'post, page')->getQuery();
        $plain  = Query::select('ID')->from('posts')->where('post_type', 'IN', 'post,page')->getQuery();

        $this->assertSame($plain, $spaced);
        $this->assertStringNotContainsString("' page'", $spaced);
    }
}

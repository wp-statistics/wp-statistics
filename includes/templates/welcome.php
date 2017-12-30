<div class="wrap about-wrap full-width-layout">
    <div class="wp-statistics-welcome">
        <h1><?php printf(__('Welcome to WP-Statistics&nbsp;%s', 'wp-statistics'), WP_Statistics::$reg['version']); ?></h1>

        <p class="about-text"><?php _e('Thank you for updating to the latest version!', 'wp-statistics'); ?></p>
        <div class="wp-badge"><?php printf(__('Version %s', 'wp-statistics'), WP_Statistics::$reg['version']); ?></div>

        <h2 class="nav-tab-wrapper wp-clearfix">
            <a href="#" class="nav-tab nav-tab-active"
               data-tab="whats-news"><?php _e('What’s New', 'wp-statistics'); ?></a>
            <a href="#" class="nav-tab" data-tab="credit"><?php _e('Credits', 'wp-statistics'); ?></a>
            <a href="#" class="nav-tab" data-tab="changelog"><?php _e('Changelog', 'wp-statistics'); ?></a>
        </h2>

        <div data-content="whats-news" class="tab-content current">
            <section class="center-section">
                <h2>Welcome to the WP-Statistics!</h2>
                <h4>You can see the new changes in WP-Statistics on this page.</h4>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2>New Feature!<br>Show Hits on the single posts/pages.</h2>
                        <h4>Lorem Ipsum is also known as: Greeked text, blind text, placeholder text, dummy content,
                            filler
                            text, lipsum, and mock-content.</h4>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url('wp-statistics/assets/images/welcome/screenshots-1.png'); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="right">
                    <div class="content-padding">
                        <h2>New Feature!<br>Show Hits on the single posts/pages.</h2>
                        <h4>Lorem Ipsum is also known as: Greeked text, blind text, placeholder text, dummy content,
                            filler
                            text, lipsum, and mock-content.</h4>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url('wp-statistics/assets/images/welcome/screenshots-2.png'); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2>New Feature!<br>Show Hits on the single posts/pages.</h2>
                        <h4>Lorem Ipsum is also known as: Greeked text, blind text, placeholder text, dummy content,
                            filler
                            text, lipsum, and mock-content.</h4>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url('wp-statistics/assets/images/welcome/screenshots-3.png'); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="right">
                    <div class="content-padding">
                        <h2>New Feature!<br>Show Hits on the single posts/pages.</h2>
                        <h4>Lorem Ipsum is also known as: Greeked text, blind text, placeholder text, dummy content, filler
                            text, lipsum, and mock-content.</h4>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url('wp-statistics/assets/images/welcome/icon-question.png'); ?>"/>
                </div>
            </section>

            <section class="center-section">
                <img src="<?php echo plugins_url('wp-statistics/assets/images/welcome/icon-love.png'); ?>"/>
                <h4>You can see the new changes in WP-Statistics on this page.</h4>
            </section>
        </div>

        <div data-content="credit" class="tab-content">
            <div class="about-wrap-content">
                <p class="about-description"><?php echo sprintf(__('WP-Statistics is created by some peoples and is one of <a target="_blank" href="%s">VeronaLabs.com</a> projects.', 'wp-statistics'), 'http://veronalabs.com'); ?></p>
                <h3 class="wp-people-group"><?php _e('Project Leaders', 'wp-statistics'); ?></h3>
                <ul class="wp-people-group ">
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/mostafas1990/"
                           class="web"><?php echo get_avatar('mst404@gmail.com', 62, '', '', array('class' => 'gravatar')); ?><?php _e('Mostafa Soufi', 'wp-statistics'); ?></a>
                        <span class="title"><?php _e('Original Author', 'wp-statistics'); ?></span>
                    </li>
                </ul>
                <h3 class="wp-people-group"><?php _e('Other Contributors', 'wp-statistics'); ?></h3>
                <ul class="wp-people-group">
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/gregross/"
                           class="web"><?php echo get_avatar('greg@toolstack.com', 62, '', '', array('class' => 'gravatar')); ?><?php _e('Greg Ross', 'wp-statistics'); ?></a>
                        <span class="title"><?php _e('Core contributor', 'wp-statistics'); ?></span>
                    </li>
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/dedidata/"
                           class="web"><?php echo get_avatar('dedidata.com@gmail.com', 62, '', '', array('class' => 'gravatar')); ?><?php _e('Farhad Sakhaei', 'wp-statistics'); ?></a>
                        <span class="title"><?php _e('Core Contributor', 'wp-statistics'); ?></span>
                    </li>
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/pedromendonca/"
                           class="web"><?php echo get_avatar('ped.gaspar@gmail.com', 62, '', '', array('class' => 'gravatar')); ?><?php _e('Pedro Mendonça', 'wp-statistics'); ?></a>
                        <span class="title"><?php _e('Language Contributor', 'wp-statistics'); ?></span>
                    </li>
                </ul>

                <p class="clear"><?php echo sprintf(__('WP-Statistics is being developed on GitHub, If you’re interested in contributing to plugin, Please look at <a target="_blank" href="%s">Github page</a>.', 'wp-statistics'), 'https://github.com/wp-statistics/wp-statistics'); ?></p>
                <h3 class="wp-people-group"><?php _e('External Libraries', 'wp-statistics'); ?></h3>
                <p class="wp-credits-list">
                    <a href="http://www.maxmind.com/"><?php _e('MaxMind', 'wp-statistics'); ?></a>,
                    <a href="https://browscap.org/">Browscap</a>,
                    <a href="http://www.chartjs.org/"><?php _e('Chart.js', 'wp-statistics'); ?></a>.</p>
            </div>
        </div>

        <div data-content="changelog" class="one-col tab-content">
            <?php WP_Statistics_Welcome::show_change_log(); ?>
        </div>

        <hr>

        <div class="return-to-dashboard">
            <a href="<?php echo admin_url('admin.php?page=wps_overview_page'); ?>"><?php _e('Go to Stats → Overview', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>

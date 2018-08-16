<div class="wp-statistics-travod">
    <div class="header">
        <div class="left">
            <p><a href="<?php bloginfo( 'url' ); ?>"><?php echo $base_url; ?></a>, Go global with TRAVOD</p>
        </div>

        <div class="right">
            <p>Professional Translation Service by TRAVOD <a href="https://translate.travod.com/website" target="_blank"><img src="http://bit.ly/2Kbnm50?v=<?php echo time(); ?>"></a></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
    </div>

    <div class="column">
        <div class="columns column-1">
            <section>
                <h2>Translate your website into 4 languages and get:</h2>
                <ul>
                    <li><span class="dashicons dashicons-yes"></span> International Sales Growth</li>
                    <li><span class="dashicons dashicons-yes"></span> Increased Web Traffic</li>
                    <li><span class="dashicons dashicons-yes"></span> Improved SEO</li>
                    <li><span class="dashicons dashicons-yes"></span> Greater Brand Awareness</li>
                    <li><span class="dashicons dashicons-yes"></span> Global Online Reach</li>
                </ul>
            </section>
        </div>

        <div class="columns column-2">
            <section>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <td><span class="dashicons dashicons-arrow-down"></span> Language</td>
                        <td><span class="dashicons dashicons-arrow-down"></span> Potential Traffic</td>
                        <td><span class="dashicons dashicons-arrow-down"></span> Potential Leads</td>
                    </tr>
                    </thead>

                    <tbody>
					<?php foreach ( $this->get_suggestion() as $item ) : ?>
                        <tr>
                            <td><?php echo $item['language']; ?></td>
                            <td><span class="dashicons dashicons-arrow-up"></span> <?php echo $item['potential_traffic_percent']; ?> (<?php echo $item['potential_traffic']; ?> Visitors)</td>
                            <td><span class="dashicons dashicons-arrow-up"></span> <?php echo $item['potential_leads_percent']; ?> (<?php echo $item['potential_leads']; ?> Leads)</td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>

        <div class="columns column-3">
            <section>
                <h1>GET A FREE QUOTE</h1>
                <p>Go global. Generate 4x more sales from untapped markets. Simply complete the form and our Translation Advisory Team will be in touch with you soon.</p>
                <form class="travod-quote-form" method="post" action="">
                    <input type="text" name="mobile" class="regular-text" value="" required="required" placeholder="Your Mobile Number"/>
                    <input type="email" name="email" class="regular-text" value="<?php echo get_option( 'admin_email' ); ?>" required="required" placeholder="Your Email Address"/>
                    <input type="submit" class="button button-primary" value="GET A FREE ESTIMATE"/>
                </form>
            </section>
        </div>
    </div>
</div>
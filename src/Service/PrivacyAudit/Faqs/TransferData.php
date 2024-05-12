<?php 
namespace WP_Statistics\Service\PrivacyAudit\Faqs;

class TransferData extends AbstractFaq
{
    static public function getStatus()
    {
        return 'success';
    }
    
    static public function getStates()
    {
        return [
            'success' => [
                'status'    => 'success',
                'title'     => esc_html__('Does WP Statistics transfer data outside the EU?', 'wp-statistics'),
                'summary'   => __('<b>No</b>, WP Statistics is designed to store all analytics data directly within your WordPress database.', 'wp-statistics'),
                'notes'     => __('<p>This means that the data resides on the same server as your website, following the same data storage practices. Since WP Statistics does not transfer data outside of your database, the location of your data is determined by your web hosting service. If your hosting servers are located within the EU, your data does not leave the EU. It is essential to be aware of your hosting provider’s data center locations to understand where your website’s data, including that collected by WP Statistics, is physically stored.</p>', 'wp-statistics')
            ]
        ];
    }
    
}
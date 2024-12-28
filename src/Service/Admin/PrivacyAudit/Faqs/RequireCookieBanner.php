<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Faqs;

use WP_Statistics\Components\View;

class RequireCookieBanner extends AbstractFaq
{
    static public function getStatus()
    {
        return 'success';
    }

    static public function getStates()
    {
        return [
            'success' => [
                'icon'    => '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.2387 9.66811C19.0471 9.5151 18.8236 9.40721 18.5846 9.35244C18.3457 9.29768 18.0974 9.29744 17.8583 9.35176C17.6198 9.40568 17.3722 9.40557 17.1337 9.35142C16.8952 9.29728 16.6718 9.19048 16.4799 9.03885C16.288 8.88722 16.1324 8.69458 16.0246 8.47506C15.9167 8.25553 15.8593 8.01467 15.8566 7.7701C15.8547 7.33927 15.6827 6.92664 15.3781 6.62198C15.0734 6.31733 14.6608 6.1453 14.23 6.14333C13.9854 6.14065 13.7445 6.08329 13.525 5.97546C13.3054 5.86764 13.1128 5.71207 12.9611 5.52016C12.8095 5.32824 12.7027 5.10484 12.6485 4.86631C12.5944 4.62777 12.5943 4.38015 12.6482 4.14158C12.7019 3.90203 12.7012 3.65349 12.6462 3.41426C12.5911 3.17502 12.4831 2.95118 12.3301 2.75921C12.1771 2.56724 11.983 2.41203 11.762 2.305C11.5411 2.19798 11.299 2.14186 11.0535 2.14078C11.035 2.1407 11.0168 2.14062 10.9984 2.14062C9.24389 2.14095 7.52902 2.66219 6.07114 3.63828C4.61325 4.61437 3.47801 6.00135 2.80933 7.6234C2.14064 9.24545 1.96862 11.0295 2.31508 12.7494C2.66153 14.4694 3.51086 16.0477 4.75539 17.2844C5.99992 18.521 7.58362 19.3603 9.30571 19.6958C11.0278 20.0314 12.8107 19.848 14.4285 19.169C16.0463 18.4901 17.426 17.3461 18.3928 15.882C19.3596 14.418 19.87 12.6998 19.8592 10.9454V10.9453C19.8583 10.6995 19.802 10.457 19.6946 10.2359C19.5872 10.0148 19.4314 9.82077 19.2387 9.66811ZM15.8682 15.8765C13.2187 18.5212 8.88073 18.5502 6.19797 15.9413C5.23542 15.0058 4.56702 13.8097 4.27485 12.4997C3.98268 11.1896 4.07947 9.82281 4.55332 8.56704C5.02717 7.31127 5.85747 6.22122 6.94223 5.43078C8.02699 4.64033 9.31898 4.1839 10.6595 4.11755C10.6086 4.59228 10.6523 5.07242 10.7882 5.53014C10.9241 5.98786 11.1494 6.41408 11.4512 6.7841C11.7529 7.15413 12.1251 7.46061 12.5461 7.68579C12.9671 7.91098 13.4287 8.0504 13.9039 8.09598C13.9495 8.57126 14.089 9.03278 14.3142 9.4538C14.5394 9.87482 14.8459 10.247 15.2159 10.5487C15.5859 10.8505 16.0121 11.0758 16.4698 11.2117C16.9276 11.3476 17.4077 11.3913 17.8824 11.3404C17.8031 13.0514 17.0843 14.6702 15.8682 15.8765ZM14.2252 13.681C14.4088 13.8646 14.5338 14.0984 14.5844 14.353C14.635 14.6076 14.609 14.8715 14.5097 15.1113C14.4104 15.3512 14.2421 15.5561 14.0263 15.7004C13.8105 15.8446 13.5567 15.9215 13.2971 15.9215C13.0375 15.9215 12.7838 15.8446 12.5679 15.7004C12.3521 15.5561 12.1839 15.3512 12.0845 15.1113C11.9852 14.8715 11.9592 14.6076 12.0099 14.353C12.0605 14.0984 12.1855 13.8646 12.369 13.681C12.4909 13.5591 12.6356 13.4624 12.7948 13.3965C12.9541 13.3305 13.1248 13.2966 13.2971 13.2966C13.4695 13.2966 13.6402 13.3305 13.7994 13.3965C13.9586 13.4624 14.1033 13.5591 14.2252 13.681ZM8.9752 13.0247C9.15875 13.2083 9.28375 13.4422 9.33439 13.6968C9.38503 13.9514 9.35904 14.2153 9.2597 14.4551C9.16036 14.6949 8.99214 14.8999 8.7763 15.0441C8.56046 15.1883 8.30671 15.2653 8.04712 15.2653C7.78754 15.2653 7.53378 15.1883 7.31795 15.0441C7.10211 14.8999 6.93388 14.6949 6.83454 14.4551C6.7352 14.2153 6.70921 13.9514 6.75985 13.6968C6.81049 13.4422 6.93549 13.2083 7.11904 13.0247C7.24092 12.9029 7.3856 12.8062 7.54485 12.7402C7.70409 12.6743 7.87476 12.6403 8.04712 12.6403C8.21948 12.6403 8.39016 12.6743 8.5494 12.7402C8.70864 12.8062 8.85333 12.9029 8.9752 13.0247ZM6.46279 10.2871C6.27924 10.1036 6.15424 9.8697 6.1036 9.6151C6.05296 9.3605 6.07895 9.09661 6.17829 8.85678C6.27763 8.61696 6.44586 8.41198 6.6617 8.26776C6.87753 8.12355 7.13129 8.04657 7.39087 8.04657C7.65046 8.04657 7.90421 8.12355 8.12005 8.26776C8.33589 8.41198 8.50411 8.61696 8.60345 8.85678C8.70279 9.09661 8.72878 9.3605 8.67814 9.6151C8.6275 9.8697 8.5025 10.1036 8.31895 10.2871C8.19707 10.409 8.05239 10.5057 7.89315 10.5716C7.73391 10.6376 7.56323 10.6716 7.39087 10.6716C7.21851 10.6716 7.04784 10.6376 6.8886 10.5716C6.72936 10.5057 6.58467 10.409 6.46279 10.2871ZM12.5846 11.5996C12.401 11.7832 12.1672 11.9082 11.9126 11.9588C11.658 12.0095 11.3941 11.9835 11.1542 11.8841C10.9144 11.7848 10.7094 11.6166 10.5652 11.4007C10.421 11.1849 10.344 10.9311 10.344 10.6716C10.344 10.412 10.421 10.1582 10.5652 9.94237C10.7094 9.72654 10.9144 9.55831 11.1542 9.45897C11.3941 9.35963 11.658 9.33364 11.9126 9.38429C12.1672 9.43493 12.401 9.55994 12.5846 9.7435C12.8307 9.98963 12.969 10.3235 12.969 10.6716C12.969 11.0196 12.8307 11.3535 12.5846 11.5996Z" fill="#019939"/></svg>',
                'status'  => 'success',
                'title'   => esc_html__('Does WP Statistics require a cookie banner?', 'wp-statistics'),
                'summary' => __('No, WP Statistics does not require a cookie banner.', 'wp-statistics'),
                'notes'   => View::load('components/privacy-audit/cookie-banner', [], true),
            ]
        ];
    }

}
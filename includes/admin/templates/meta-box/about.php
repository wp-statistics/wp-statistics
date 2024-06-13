<?php
$aboutWidgetContent = apply_filters('wp_statistics_about_widget_content', false);
if ($aboutWidgetContent) {
    echo '<div class="o-wrap o-wrap--no-data">' . apply_filters('the_content', $aboutWidgetContent) . '</div>';

    return;
} ?>

<div class="o-wrap wps-about-widget">
    <div class="c-about">
        <div class="c-about__row c-about__row--logo">
            <a href="https://wp-statistics.com" target="_blank">
                <svg width="203" height="50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m55.4093 15.1174 5.5636 14.0282 5.691-15.0323 5.5004 15.0374 5.7533-14.0282h3.4781l-9.4211 22.3312-5.5047-14.8801-5.5636 14.8784-8.9752-22.3346h3.4782Zm33.4451 0c1.6162-.0814 3.2208.3125 4.6136 1.1325.8253.5698 1.4965 1.3336 1.9542 2.2234.4577.8898.6877 1.8783.6695 2.8777.02.8689-.1539 1.7313-.5093 2.5252-.3553.7939-.8831 1.4995-1.5454 2.0659-1.4587 1.1549-3.2923 1.7372-5.1536 1.6366H87.431v8.5224h-3.2244V15.1174h4.6478Zm-1.4226 9.5009h1.4525c2.7827 0 4.1103-1.3526 4.1103-3.303 0-1.1639-.4741-3.2409-4.1419-3.2409h-1.4226l.0017 6.5439Zm21.7172-5.3477c-.302-.6009-.721-1.1357-1.234-1.5729-.686-.4996-1.521-.7547-2.371-.7243-2.085 0-3.54 1.2905-3.54 3.2409-.021.3445.039.6891.176 1.0061.138.3169.348.5973.614.8184.648.5753 1.398 1.0235 2.213 1.3212l2.023.8808c1.201.4516 2.306 1.1238 3.257 1.9818.506.4939.902 1.0877 1.164 1.7433.261.6556.382 1.3583.353 2.0631 0 3.87-2.876 6.4495-6.67 6.4495-.84.0357-1.678-.0964-2.465-.3885-.788-.292-1.508-.7381-2.119-1.3119-.9901-1.0301-1.6692-2.3163-1.9601-3.7119l2.2761-.6292c.138.9984.579 1.9311 1.264 2.6731.605.5658 1.361.9461 2.178 1.0952.817.1491 1.659.0607 2.427-.2546.767-.3153 1.427-.8442 1.9-1.5231.473-.679.739-1.4794.767-2.3051.018-.4603-.066-.919-.246-1.3433-.18-.4242-.453-.8037-.797-1.1112-.761-.6124-1.615-1.1003-2.53-1.4454l-1.897-.8808c-1.033-.4056-1.986-.9915-2.813-1.7301-.482-.4294-.862-.9596-1.113-1.5524-.2508-.5928-.3669-1.2337-.3396-1.8765 0-3.2409 2.4976-5.4115 5.9116-5.4115 1.199-.0461 2.383.284 3.383.9438.868.5956 1.574 1.3956 2.055 2.3286l-1.867 1.2269Zm8.881 5.8508v10.9794h-2.276V25.1214h-1.391v-2.0762h1.391v-4.7185h2.276v4.7185h2.277v2.0762h-2.277Zm14.637-2.0761h2.276v13.0556h-2.276v-1.8245c-.522.6921-1.2 1.2521-1.98 1.6347-.78.3825-1.64.5769-2.509.5673-3.32 0-6.386-2.3278-6.386-6.9206 0-4.4992 3.035-6.827 6.386-6.827.868-.0221 1.729.1635 2.51.5412.781.3777 1.46.9364 1.979 1.6293v-1.856Zm-8.536 6.5125c0 2.7997 1.581 4.9074 4.299 4.9074 2.783 0 4.363-2.2956 4.363-4.8759 0-3.3668-2.339-4.8461-4.363-4.8461-2.24-.0026-4.299 1.6366-4.299 4.8146Zm17.609-4.4364v10.9794h-2.276V25.1214h-1.391v-2.0762h1.391v-4.7185h2.276v4.7185h2.277v2.0762h-2.277Zm5.724-8.6199c.319-.0002.631.0938.897.2701.265.1762.472.4269.594.7202.122.2933.154.616.092.9275-.062.3114-.216.5975-.441.8221-.226.2245-.513.3775-.826.4395-.313.062-.637.0303-.932-.0912-.295-.1215-.547-.3272-.724-.5912-.177-.264-.272-.5744-.272-.8919.002-.4251.172-.8323.474-1.133.302-.3006.711-.4703 1.138-.4721Zm1.139 6.5465v13.053h-2.277V23.0454l2.277.0026Zm9.858 3.0171c-.138-.3778-.386-.7058-.713-.9414-.327-.2356-.717-.3679-1.121-.3798-.204-.0181-.409.007-.603.0736-.194.0667-.371.1733-.52.3129-.15.1397-.267.3091-.346.4973-.079.1882-.117.3909-.111.5947 0 .9437.632 1.2268 2.118 1.856 1.897.7864 2.782 1.3841 3.288 2.0141.486.5926.734 1.3436.696 2.1076 0 2.7368-1.993 4.2782-4.522 4.2782-1.025.0191-2.028-.2967-2.856-.8989-.828-.6022-1.435-1.4577-1.727-2.4356l1.959-.8179c.168.5853.513 1.1049.988 1.4877.475.3829 1.058.6104 1.668.6514.281.0157.562-.0267.826-.1246.264-.0979.504-.2492.706-.4443.202-.1951.362-.4298.468-.6892.106-.2594.158-.538.15-.8181 0-1.2276-.885-1.6357-2.119-2.1714-1.193-.3975-2.283-1.0528-3.192-1.9188-.429-.5455-.641-1.2281-.598-1.9189-.022-.4986.063-.996.25-1.4591.187-.4632.47-.8813.832-1.2267.362-.3453.794-.6099 1.267-.776.473-.1661.976-.2299 1.476-.1873.77-.0188 1.528.197 2.172.6186.644.4217 1.144 1.029 1.431 1.7407l-1.867.9752Zm8.219-.9437v10.9794h-2.276V25.1214h-1.39v-2.0762h1.391v-4.7185h2.276v4.7185h2.276v2.0762h-2.277Zm5.721-8.6199c.319 0 .631.094.896.2703.266.1763.472.4268.594.72.123.2931.155.6157.093.927-.062.3114-.216.5974-.441.822-.225.2245-.513.3776-.825.4398-.313.0623-.637.0308-.932-.0903-.295-.1211-.547-.3264-.725-.59-.177-.2637-.272-.5738-.273-.8911.002-.4254.173-.8327.475-1.1337.302-.301.711-.4714 1.138-.474Zm1.138 6.5465v13.053h-2.276V23.0454l2.276.0026Zm14.731 3.6481c-.495-.61-1.121-1.1021-1.832-1.4404-.711-.3383-1.489-.5143-2.277-.515-.636-.013-1.267.1063-1.854.3502-.586.2438-1.115.6069-1.553 1.066-.437.4591-.774 1.004-.987 1.5999-.214.5959-.3 1.2296-.254 1.8606-.042.6256.047 1.2532.26 1.8431.214.5899.548 1.1295.981 1.5847.434.4552.957.8162 1.537 1.0603.581.2441 1.206.366 1.836.358.813-.0133 1.613-.2027 2.344-.5551.732-.3524 1.378-.8593 1.893-1.4854v2.7054c-1.21.8839-2.673 1.3581-4.173 1.3526-.919.041-1.837-.1064-2.697-.4329-.86-.3265-1.643-.8253-2.301-1.4654-.658-.64-1.177-1.4078-1.524-2.2556-.348-.8478-.517-1.7577-.497-2.6732-.025-.9209.141-1.8371.485-2.6922.345-.855.862-1.6306 1.52-2.2791.658-.6484 1.442-1.1559 2.304-1.4912.862-.3352 1.785-.4911 2.71-.458 1.458-.0169 2.885.4233 4.078 1.2582l.001 2.7045Zm9.135-.631c-.137-.3778-.385-.7058-.712-.9414-.327-.2356-.718-.3679-1.121-.3798-.204-.0181-.41.007-.603.0736-.194.0667-.371.1733-.521.3129-.149.1397-.267.3091-.346.4973-.078.1882-.116.3909-.111.5947 0 .9437.633 1.2268 2.118 1.856 1.898.7864 2.783 1.3841 3.289 2.0141.486.5926.734 1.3436.695 2.1076 0 2.7368-1.992 4.2782-4.521 4.2782-1.025.0191-2.029-.2967-2.856-.8989-.828-.6022-1.435-1.4577-1.728-2.4356l1.96-.8179c.168.5853.512 1.1049.988 1.4877.475.3829 1.057.6104 1.668.6514.281.0157.562-.0267.826-.1246.264-.0979.504-.2492.706-.4443.202-.1951.361-.4298.468-.6892.106-.2594.157-.538.15-.8181 0-1.2276-.885-1.6357-2.119-2.1714-1.193-.3975-2.284-1.0528-3.193-1.9188-.428-.5455-.641-1.2281-.598-1.9189-.021-.4986.064-.996.251-1.4591.186-.4632.47-.8813.832-1.2267.362-.3453.794-.6099 1.267-.776.473-.1661.976-.2299 1.475-.1873.771-.0188 1.529.197 2.173.6186.644.4217 1.143 1.029 1.431 1.7407l-1.868.9752ZM45.2893 31.4248c-1.5332 4.7069-4.6993 8.7174-8.932 11.3139-4.2327 2.5965-9.2557 3.6096-14.1703 2.858-4.9146-.7517-9.4001-3.219-12.65393-6.9605-3.25381-3.7414-5.06363-8.513-5.10563-13.4608-.042-4.9478 1.68655-9.7491 4.87639-13.5448C12.4937 7.83498 16.9366 5.29263 21.8378 4.45849c4.9011-.83415 9.9406.09432 14.2168 2.6193 4.2761 2.52497 7.5099 6.48171 9.1227 11.16221h2.3615c-1.6319-5.2993-5.1261-9.83699-9.8437-12.78334-4.7175-2.94635-10.3432-4.10447-15.8483-3.2626-5.5051.84187-10.5217 3.62748-14.13258 7.84744-3.61086 4.22-5.57465 9.5923-5.53227 15.1347.04237 5.5423 2.08809 10.8843 5.76306 15.0491C11.62 44.3902 16.6786 47.0995 22.196 47.8579c5.5173.7584 11.1246-.4848 15.7966-3.5022 4.6719-3.0175 8.0964-7.6075 9.647-12.9309h-2.3503Z"
                        fill="#0C0C0D"/>
                    <path
                        d="m45.7848 22.682-1.1961-.6164-4.6905 9.0121c-.2431-.2384-.5326-.4248-.8507-.5478-.3182-.123-.6582-.1799-.9993-.1672-.3411.0127-.6759.0946-.984.2408-.308.1462-.5828.3536-.8073.6094L31.821 25.31c-.0291-.0442-.0692-.0765-.1-.1182.0814-.243.1247-.4971.1282-.7533.0054-.366-.0695-.7289-.2197-1.0631-.1501-.3343-.3718-.6319-.6495-.872-.2777-.2401-.6048-.4169-.9584-.5182-.3536-.1012-.7251-.1243-1.0886-.0678-.3635.0565-.7102.1914-1.0159.3951-.3056.2038-.5629.4715-.7538.7845-.1909.313-.3107.6637-.3512 1.0276-.0405.3638-.0007.7321.1168 1.0791-.0257.0357-.0598.0621-.0855.0995l-3.1159 5.3452c-.4729-.4177-1.0875-.6411-1.7195-.6254-.6321.0158-1.2347.2697-1.686.7104l-4.1266-6.8432c.1012-.27.1544-.5554.1572-.8434-.0121-.6589-.2837-1.2867-.7562-1.7484-.4726-.4616-1.1083-.7203-1.7706-.7203-.6622 0-1.2979.2587-1.7705.7203-.4725.4617-.7441 1.0895-.7563 1.7484v.0221c-.0854.0739-.1657.1318-.2477.2134l-7.37238 7.4902.96288.9352 7.1135-7.2267c.1917.2768.4378.5121.7234.6917.2856.1797.6047.2999.9382.3535.3336.0536.6745.0395 1.0024-.0415.3279-.081.6359-.2272.9055-.4298l4.2685 7.0822c-.0565.3425-.0411.6929.0454 1.0291.0865.3363.2421.651.457.9244s.4844.4994.7916.6639c.3071.1645.6452.2638.9928.2917.3477.0279.6974-.0162 1.0271-.1296.3296-.1134.632-.2936.8882-.5292.2561-.2356.4604-.5214.5999-.8396.1395-.3181.2112-.6615.2105-1.0086-.0017-.2008-.0281-.4007-.0786-.5951l3.2347-5.5476c.4482.3641 1.0105.5603 1.589.5544.5785-.0059 1.1367-.2135 1.5773-.5867l4.7058 6.2617c-.0129.0845-.0215.1697-.0256.2551.0122.6588.2837 1.2866.7563 1.7483.4725.4617 1.1083.7203 1.7705.7203.6622 0 1.298-.2586 1.7705-.7203.4726-.4617.7441-1.0895.7563-1.7483-.0042-.0989-.0144-.1974-.0308-.295l5.1536-9.904Zm-31.9645 2.3177c-.3887 0-.7686-.1147-1.0917-.3296-.3232-.2149-.5751-.5203-.7238-.8776-.1487-.3573-.1876-.7505-.1118-1.1298.0758-.3793.263-.7277.5378-1.0012.2748-.2735.6249-.4597 1.0061-.5352.3812-.0754.7763-.0367 1.1354.1113.359.148.6659.3987.8819.7202.2159.3216.3311.6997.3311 1.0864 0 .2568-.0508.5111-.1495.7483-.0988.2373-.2435.4528-.426.6344-.1825.1816-.3991.3256-.6375.4239-.2384.0983-.494.1489-.752.1489Zm8.2592 9.5077c-.3887 0-.7686-.1147-1.0918-.3296-.3231-.2148-.575-.5202-.7237-.8775-.1487-.3573-.1876-.7505-.1118-1.1298.0758-.3794.263-.7278.5378-1.0013.2748-.2734.6249-.4597 1.0061-.5351.3812-.0755.7763-.0368 1.1354.1113.359.148.6659.3986.8819.7202.2159.3215.3311.6996.3311 1.0864 0 .5186-.207 1.0159-.5755 1.3827-.3685.3667-.8684.5727-1.3895.5727Zm7.2467-8.1466c-.3886 0-.7685-.1147-1.0917-.3295-.3231-.2149-.575-.5203-.7237-.8776-.1488-.3573-.1877-.7505-.1119-1.1298.0759-.3793.263-.7278.5378-1.0012.2748-.2735.625-.4597 1.0062-.5352.3812-.0755.7763-.0367 1.1353.1113.3591.148.666.3986.8819.7202.2159.3216.3312.6996.3312 1.0864 0 .5186-.2071 1.016-.5756 1.3827s-.8683.5727-1.3895.5727Zm8.8103 8.4722c-.3887 0-.7686-.1147-1.0917-.3295-.3232-.2149-.5751-.5203-.7238-.8776-.1487-.3573-.1876-.7505-.1118-1.1298.0758-.3793.263-.7278.5378-1.0012.2748-.2735.6249-.4597 1.0061-.5352.3812-.0755.7763-.0367 1.1354.1113.359.148.6659.3986.8819.7202.2159.3216.3311.6996.3311 1.0864 0 .5186-.207 1.016-.5755 1.3827-.3685.3667-.8684.5727-1.3895.5727Z"
                        fill="#0C0C0D"/>
                </svg>
            </a>
            <span class="o-badge"><?php echo sprintf(__('V%s', 'wp-statistics'), WP_STATISTICS_VERSION) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
        </div>
        <div class="c-about__row c-about__links">
            <div>
                <a href="https://wp-statistics.com/documentation/" target="_blank"><?php esc_html_e('Documentation', 'wp-statistics'); ?>
                    <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.48192 2.53455L5.13816 6.87831L4.19405 5.93421L8.61018 1.51808L8.7809 1.34737H8.53947H5.74474V0.1H10.9V5.25526H9.65263V2.60526V2.36384L9.48192 2.53455ZM7.33684 9.55263V6.26511L8.58421 5.01774V10.9H0.1V2.41579H6.27174L5.02437 3.66316H1.44737H1.34737V3.76316V9.55263V9.65263H1.44737H7.23684H7.33684V9.55263Z" fill="#404BF2" stroke="white" stroke-width="0.2"/>
                    </svg>
                </a> <span class="o-separator">|</span>
                <a href="https://wp-statistics.com/add-ons/" target="_blank"><?php esc_html_e('Add-Ons', 'wp-statistics'); ?>
                    <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.48192 2.53455L5.13816 6.87831L4.19405 5.93421L8.61018 1.51808L8.7809 1.34737H8.53947H5.74474V0.1H10.9V5.25526H9.65263V2.60526V2.36384L9.48192 2.53455ZM7.33684 9.55263V6.26511L8.58421 5.01774V10.9H0.1V2.41579H6.27174L5.02437 3.66316H1.44737H1.34737V3.76316V9.55263V9.65263H1.44737H7.23684H7.33684V9.55263Z" fill="#404BF2" stroke="white" stroke-width="0.2"/>
                    </svg>
                </a> <span class="o-separator">|</span>
                <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank"><?php esc_html_e('Rate', 'wp-statistics'); ?>
                    <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.48192 2.53455L5.13816 6.87831L4.19405 5.93421L8.61018 1.51808L8.7809 1.34737H8.53947H5.74474V0.1H10.9V5.25526H9.65263V2.60526V2.36384L9.48192 2.53455ZM7.33684 9.55263V6.26511L8.58421 5.01774V10.9H0.1V2.41579H6.27174L5.02437 3.66316H1.44737H1.34737V3.76316V9.55263V9.65263H1.44737H7.23684H7.33684V9.55263Z" fill="#404BF2" stroke="white" stroke-width="0.2"/>
                    </svg>
                </a>
                <div class="c-about__veronalabs">
                    <a href="https://veronalabs.com/?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress" target="_blank" title="<?php esc_html_e('Power by VeronaLabs', 'wp-statistics'); ?>">
                        <svg width="83" height="14" viewBox="0 0 83 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.7738 11.2408L17.8744 2.15479H19.4971L21.6627 9.31976L23.8283 2.15479H25.4271L22.5337 11.2408H20.7738Z" fill="#56585A"/>
                            <path
                                d="M28.583 11.3601C28.1654 11.3601 27.7677 11.2805 27.3899 11.1214C27.016 10.9624 26.6899 10.7416 26.4115 10.4592C26.1331 10.1768 25.9103 9.83083 25.7433 9.42118C25.5802 9.01152 25.4987 8.56806 25.4987 8.0908C25.4987 7.14422 25.793 6.36667 26.3816 5.75816C26.9742 5.14566 27.71 4.83942 28.589 4.83942C29.4123 4.83942 30.0904 5.11981 30.6233 5.6806C31.1603 6.24139 31.4287 6.98513 31.4287 7.91182C31.4287 8.08284 31.4168 8.2479 31.3929 8.40699H26.9484C26.9921 8.93596 27.1751 9.34959 27.4972 9.64788C27.8234 9.94617 28.1972 10.0953 28.6188 10.0953C28.9808 10.0953 29.291 10.0019 29.5495 9.81492C29.808 9.62402 29.9949 9.36947 30.1103 9.0513L31.3392 9.5047C31.1802 10.0575 30.85 10.505 30.3489 10.847C29.8518 11.1891 29.2631 11.3601 28.583 11.3601ZM26.9782 7.38683H30.0089C29.9532 6.93342 29.7901 6.58343 29.5197 6.33684C29.2532 6.09025 28.937 5.96696 28.5711 5.96696C28.1853 5.96696 27.8433 6.09224 27.545 6.34281C27.2467 6.5894 27.0578 6.9374 26.9782 7.38683Z"
                                fill="#56585A"/>
                            <path d="M32.7591 11.2408V4.99453H34.1372V6.1519H34.1492C34.3003 5.75418 34.523 5.44992 34.8173 5.23913C35.1156 5.02436 35.4656 4.91697 35.8673 4.91697C36.102 4.91697 36.271 4.95078 36.3744 5.01839V6.49195C36.2193 6.44025 36.0363 6.4144 35.8256 6.4144C35.3006 6.4144 34.9068 6.58542 34.6443 6.92746C34.3818 7.2695 34.2506 7.74478 34.2506 8.35329V11.2408H32.7591Z" fill="#56585A"/>
                            <path
                                d="M41.5587 10.9007C41.0814 11.207 40.5405 11.3601 39.936 11.3601C39.3315 11.3601 38.7906 11.207 38.3133 10.9007C37.836 10.5905 37.4741 10.1888 37.2275 9.69561C36.9809 9.20243 36.8576 8.66749 36.8576 8.0908C36.8576 7.66126 36.9312 7.2516 37.0784 6.86183C37.2255 6.46809 37.4284 6.12207 37.6869 5.82378C37.9494 5.52151 38.2755 5.28288 38.6653 5.10788C39.0551 4.9289 39.4786 4.83942 39.936 4.83942C40.5445 4.83942 41.0854 4.99254 41.5587 5.29879C42.036 5.60106 42.3979 5.99878 42.6445 6.49195C42.8951 6.98115 43.0203 7.5141 43.0203 8.0908C43.0203 8.66749 42.8951 9.20243 42.6445 9.69561C42.3979 10.1888 42.036 10.5905 41.5587 10.9007ZM38.367 8.0908C38.367 8.63568 38.5102 9.09505 38.7965 9.4689C39.0869 9.84276 39.4667 10.0297 39.936 10.0297C40.4053 10.0297 40.7851 9.84276 41.0755 9.4689C41.3658 9.09505 41.511 8.63568 41.511 8.0908C41.511 7.55387 41.3658 7.10047 41.0755 6.73059C40.7851 6.35673 40.4053 6.1698 39.936 6.1698C39.4667 6.1698 39.0869 6.35673 38.7965 6.73059C38.5102 7.10047 38.367 7.55387 38.367 8.0908Z"
                                fill="#56585A"/>
                            <path d="M44.3149 11.2408V4.99453H45.6871V5.82975H45.699C45.9058 5.50759 46.1643 5.26299 46.4746 5.09595C46.7888 4.92493 47.1348 4.83942 47.5126 4.83942C48.141 4.83942 48.664 5.04623 49.0816 5.45986C49.4992 5.8735 49.708 6.4661 49.708 7.23768V11.2408H48.2166V7.57773C48.2166 7.14819 48.1152 6.81212 47.9123 6.56951C47.7135 6.3269 47.4351 6.20559 47.0771 6.20559C46.743 6.20559 46.4547 6.32292 46.2121 6.55758C45.9734 6.78826 45.8382 7.09848 45.8064 7.48825V11.2408H44.3149Z" fill="#56585A"/>
                            <path
                                d="M51.0504 7.06467C51.0742 6.72263 51.1677 6.40843 51.3308 6.12207C51.4938 5.83571 51.7006 5.60106 51.9512 5.4181C52.2057 5.23515 52.4901 5.09396 52.8043 4.99453C53.1185 4.89112 53.4407 4.83942 53.7708 4.83942C54.4986 4.83942 55.0912 5.03032 55.5486 5.41214C56.006 5.78997 56.2347 6.34082 56.2347 7.06467V11.2408H54.8506V10.2564H54.8208C54.6657 10.5666 54.423 10.8271 54.0929 11.0379C53.7628 11.2447 53.3691 11.3481 52.9117 11.3481C52.3589 11.3481 51.8955 11.1871 51.5217 10.8649C51.1518 10.5388 50.9668 10.1053 50.9668 9.56436C50.9668 9.103 51.1339 8.70727 51.468 8.37716C51.806 8.04705 52.2614 7.83427 52.8341 7.73881L53.9796 7.54194C54.2818 7.49024 54.5006 7.4087 54.6358 7.29734C54.771 7.18598 54.8387 7.02291 54.8387 6.80814C54.8387 6.59337 54.7432 6.40644 54.5523 6.24735C54.3614 6.08827 54.1049 6.00872 53.7827 6.00872C53.4009 6.00872 53.0768 6.13003 52.8103 6.37264C52.5438 6.61525 52.4046 6.92149 52.3927 7.29137L51.0504 7.06467ZM52.4822 9.4689C52.4822 9.69958 52.5677 9.88254 52.7387 10.0178C52.9097 10.153 53.1384 10.2206 53.4248 10.2206C53.5481 10.2206 53.6713 10.2027 53.7946 10.1669C53.9219 10.1311 54.0492 10.0715 54.1765 9.98793C54.3037 9.90441 54.4151 9.80299 54.5105 9.68367C54.61 9.56436 54.6895 9.41123 54.7492 9.2243C54.8088 9.0334 54.8387 8.82459 54.8387 8.59789V8.2996C54.6676 8.35926 54.3753 8.42687 53.9617 8.50244L53.4248 8.59789C53.1106 8.65755 52.8739 8.76295 52.7148 8.91408C52.5597 9.06522 52.4822 9.25016 52.4822 9.4689Z"
                                fill="#56585A"/>
                            <path d="M58.1974 11.2408V2.15479H59.7068V9.81492H62.9701V11.2408H58.1974Z" fill="#56585A"/>
                            <path
                                d="M63.8888 7.06467C63.9127 6.72263 64.0062 6.40843 64.1692 6.12207C64.3323 5.83571 64.5391 5.60106 64.7897 5.4181C65.0442 5.23515 65.3286 5.09396 65.6428 4.99453C65.957 4.89112 66.2791 4.83942 66.6092 4.83942C67.3371 4.83942 67.9297 5.03032 68.3871 5.41214C68.8444 5.78997 69.0731 6.34082 69.0731 7.06467V11.2408H67.6891V10.2564H67.6592C67.5041 10.5666 67.2615 10.8271 66.9314 11.0379C66.6013 11.2447 66.2075 11.3481 65.7502 11.3481C65.1973 11.3481 64.734 11.1871 64.3601 10.8649C63.9902 10.5388 63.8053 10.1053 63.8053 9.56436C63.8053 9.103 63.9724 8.70727 64.3064 8.37716C64.6445 8.04705 65.0999 7.83427 65.6726 7.73881L66.8181 7.54194C67.1203 7.49024 67.3391 7.4087 67.4743 7.29734C67.6095 7.18598 67.6771 7.02291 67.6771 6.80814C67.6771 6.59337 67.5817 6.40644 67.3908 6.24735C67.1999 6.08827 66.9433 6.00872 66.6212 6.00872C66.2394 6.00872 65.9152 6.13003 65.6488 6.37264C65.3823 6.61525 65.2431 6.92149 65.2311 7.29137L63.8888 7.06467ZM65.3206 9.4689C65.3206 9.69958 65.4061 9.88254 65.5772 10.0178C65.7482 10.153 65.9769 10.2206 66.2632 10.2206C66.3865 10.2206 66.5098 10.2027 66.6331 10.1669C66.7604 10.1311 66.8877 10.0715 67.0149 9.98793C67.1422 9.90441 67.2536 9.80299 67.349 9.68367C67.4484 9.56436 67.528 9.41123 67.5876 9.2243C67.6473 9.0334 67.6771 8.82459 67.6771 8.59789V8.2996C67.5061 8.35926 67.2138 8.42687 66.8002 8.50244L66.2632 8.59789C65.949 8.65755 65.7124 8.76295 65.5533 8.91408C65.3982 9.06522 65.3206 9.25016 65.3206 9.4689Z"
                                fill="#56585A"/>
                            <path d="M70.7674 11.2408V2.01758H72.2589V5.78202H72.2768C72.758 5.15362 73.3805 4.83942 74.1441 4.83942C74.9037 4.83942 75.5361 5.13174 76.0412 5.71639C76.5503 6.29707 76.8049 7.08456 76.8049 8.07886C76.8049 8.57999 76.7353 9.03738 76.5961 9.45101C76.4569 9.86464 76.2659 10.2087 76.0233 10.4831C75.7847 10.7535 75.5023 10.9643 75.1762 11.1155C74.854 11.2626 74.51 11.3362 74.1441 11.3362C73.3168 11.3362 72.6586 10.9683 72.1694 10.2325H72.1396V11.2408H70.7674ZM72.2589 8.0908C72.2589 8.65556 72.3961 9.12289 72.6705 9.49277C72.945 9.86265 73.3188 10.0476 73.7921 10.0476C74.2574 10.0476 74.6234 9.86265 74.8898 9.49277C75.1563 9.12289 75.2895 8.65556 75.2895 8.0908C75.2895 7.53399 75.1543 7.07263 74.8839 6.70672C74.6174 6.33684 74.2535 6.1519 73.7921 6.1519C73.3188 6.1519 72.945 6.33485 72.6705 6.70076C72.3961 7.06666 72.2589 7.53001 72.2589 8.0908Z" fill="#56585A"/>
                            <path
                                d="M80.3486 11.3601C79.6088 11.3601 78.9864 11.1592 78.4813 10.7575C77.9761 10.3558 77.7077 9.86861 77.6759 9.29589L79.0182 9.10499C79.0739 9.45896 79.229 9.73936 79.4835 9.94617C79.7381 10.153 80.0423 10.2564 80.3963 10.2564C80.7065 10.2564 80.9531 10.1808 81.1361 10.0297C81.319 9.87856 81.4105 9.68964 81.4105 9.46294C81.4105 9.04533 81.142 8.78681 80.6051 8.68738L79.5611 8.47858C79.0202 8.37517 78.5986 8.1783 78.2963 7.88796C77.998 7.59364 77.8489 7.21581 77.8489 6.75445C77.8489 6.21752 78.0915 5.76412 78.5767 5.39424C79.0659 5.02436 79.6545 4.83942 80.3426 4.83942C81.0068 4.83942 81.5775 5.01839 82.0548 5.37634C82.536 5.73429 82.8025 6.20957 82.8542 6.80218L81.5596 7.03484C81.5238 6.70871 81.3886 6.44224 81.154 6.23542C80.9233 6.02463 80.6389 5.91923 80.3008 5.91923C79.9986 5.91923 79.756 5.9948 79.573 6.14593C79.3901 6.29707 79.2986 6.47604 79.2986 6.68286C79.2986 6.87774 79.3622 7.03286 79.4895 7.14819C79.6168 7.26353 79.8256 7.34706 80.1159 7.39876L81.2613 7.60756C81.7704 7.69904 82.1681 7.88796 82.4545 8.17432C82.7448 8.4567 82.89 8.8405 82.89 9.32572C82.89 9.91038 82.6593 10.3956 82.198 10.7814C81.7366 11.1672 81.1201 11.3601 80.3486 11.3601Z"
                                fill="#56585A"/>
                            <path d="M13.4231 13.6842H0.249015C0.0797554 13.6842 -0.0424878 13.5092 0.0139322 13.344L3.13584 6.02571C3.19226 5.85077 3.40853 5.79245 3.54018 5.9188L6.53043 9.90354C6.69029 10.059 6.94418 10.059 7.11344 9.90354L10.1507 5.9188C10.2824 5.79245 10.4986 5.85077 10.5551 6.02571L13.677 13.3537C13.7146 13.5092 13.6017 13.6842 13.4231 13.6842Z" fill="#56585A"/>
                            <path d="M7.18868 7.80427C6.99121 7.99864 6.6809 7.99864 6.48343 7.80427L3.48377 3.80008C3.41794 3.73205 3.38033 3.64458 3.38033 3.54739V0.43735C3.38033 0.194378 3.5684 0 3.80348 0H9.86862C10.1037 0 10.3012 0.194378 10.3012 0.447069V3.54739C10.3012 3.64458 10.2636 3.73205 10.1977 3.80008L7.18868 7.80427Z" fill="#56585A"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <?php if (!is_plugin_active('wp-statistics-customization/wp-statistics-customization.php')) { ?>
            <div class="c-about__row c-about__footer">
                <a href="https://wp-statistics.com/product/wp-statistics-customization?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress" target="_blank"><?php esc_html_e('Disable or customize widget', 'wp-statistics'); ?>
                    <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.48192 2.53455L5.13816 6.87831L4.19405 5.93421L8.61018 1.51808L8.7809 1.34737H8.53947H5.74474V0.1H10.9V5.25526H9.65263V2.60526V2.36384L9.48192 2.53455ZM7.33684 9.55263V6.26511L8.58421 5.01774V10.9H0.1V2.41579H6.27174L5.02437 3.66316H1.44737H1.34737V3.76316V9.55263V9.65263H1.44737H7.23684H7.33684V9.55263Z" fill="#404BF2" stroke="white" stroke-width="0.2"/>
                    </svg>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
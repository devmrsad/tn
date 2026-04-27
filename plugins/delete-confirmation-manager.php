<?php
/**
 * Plugin Name:  Delete Confirmation Manager
 * Description:  Manages custom delete confirmation messages for specific GravityView View IDs.
 * Version:      1.0
 * Author:       Mohammadreza
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

add_action( 'wp_footer', function() {
    // Only run on pages that might contain a GravityView delete link
    if ( ! class_exists( 'GravityView_Plugin' ) ) {
        return;
    }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Find all links that have 'action=delete' in their href (GravityView delete pattern)
            var deleteLinks = document.querySelectorAll('a[href*="action=delete"]');

            deleteLinks.forEach(function (link) {
                // Extract view_id from the URL to allow different messages per view
                var href = link.getAttribute('href');
                var viewId = null;
                var match = href.match(/[?&]view_id=(\d+)/);
                if (match) {
                    viewId = parseInt(match[1]);
                }

                // Define custom messages
                var customMessage = 'آیا از حذف این مورد کاملاً اطمینان دارید؟'; // default
                if (viewId === 122) {
                    customMessage = 'با حذف این کلاس، تمامی دانش آموزان آن نیز پاک می‌شوند و برنامه انتقال دانش آموزان از یا به این کلاس از بین می‌روند. آیا مطمئن هستید؟';
                } else if (viewId === 112) {
                    customMessage = 'با حذف دانش آموز، کلیه اطلاعات وی از سیستم پاک می‌شود. ادامه می‌دهید؟';
                }

                // Replace the entire onclick attribute with a new confirm that uses our message
                link.setAttribute('onclick', "return window.confirm('" + customMessage.replace(/'/g, "\\'") + "');");
            });
        });
    </script>
    <?php
}, 100 );
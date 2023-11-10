function hide_go_pro_menu_item_script() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var goProMenuItem = document.querySelector('.code-snippets-upgrade-button');

            if (goProMenuItem) {
                goProMenuItem.parentNode.parentNode.removeChild(goProMenuItem.parentNode);
            }
        });
    </script>
<style>
	.code-snippets-upgrade-button {
    display: none !important;
}
</style>
    <?php
}

add_action('admin_footer', 'hide_go_pro_menu_item_script');

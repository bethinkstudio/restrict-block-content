<pre>
    <?php var_dump( $block ); ?>
</pre>



<?php

if ( function_exists( 'rcp_user_has_access' ) && ! empty( $block['attrs']['membership_tier'] ) ) {
    // Always reject.
    if ( ! is_user_logged_in() ) {
        return apply_filters( 'bethink_restrict_block_content_deny', '', $block, $attributes );
    }

    $hide         = true;

    $access_level = (int) ( isset( $block['attrs']['membership_tier'] ) ?? 0 );
    if ( rcp_user_has_access( get_current_user_id(), $access_level ) ) {
        $hide = false;
    }

    if ( $hide ) {
        return apply_filters( 'bethink_restrict_block_content_deny', '', $block, $attributes );
    }
}

echo $content;

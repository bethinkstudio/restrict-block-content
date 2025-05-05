<?php

if ( function_exists( 'rcp_user_has_access' ) && ! empty( $block->parsed_block['attrs']['membership_tier'] ) ) {
    if ( ! is_user_logged_in() ) {
        do_action( 'bethink_restrict_block_content_deny', $block, $attributes );
        return;
    }

    $access_level = (int) ( isset( $block->parsed_block['attrs']['membership_tier'] ) ?? 0 );
    if ( ! rcp_user_has_access( get_current_user_id(), $access_level ) ) {
        do_action( 'bethink_restrict_block_content_deny', $block, $attributes );
        return;
    }
}

echo $content;

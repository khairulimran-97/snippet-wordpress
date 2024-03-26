<?php

function add_custom_swiper_shortcode() {
    // Retrieve the option value from the database
    $gallery_option = get_option('options_image-gallery');

    // Enqueue Swiper JavaScript
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js', array(), null, true);

    // Construct the image URLs from the option values
    $image_urls = '';
    if (!empty($gallery_option)) {
        foreach ($gallery_option as $image_id) {
            $image_src = wp_get_attachment_image_src($image_id, 'full');
            if ($image_src) {
                $image_urls .= '<swiper-slide><img src="' . $image_src[0] . '" /></swiper-slide>';
            }
        }
    }

    // Inline CSS styles
    $inline_css = '
        <style>
            .swiper-container {
                width: 100%;
                height: 100%;
            }

            .swiper-slide {
                text-align: center;
                font-size: 18px;
                background: #fff;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .swiper-slide img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .mySwiper {
                height: 80%;
                width: 100%;
            }

            .mySwiper2 {
                height: 20%;
                box-sizing: border-box;
                padding: 10px 0;
            }

            .mySwiper2 .swiper-slide {
                width: 25%;
                height: 100%;
                opacity: 0.4;
                transition: opacity 0.3s ease-in-out;
            }

            .mySwiper2 > swiper-slide.swiper-slide-visible.swiper-slide-thumb-active {
                opacity: 1!important;
            }

            .mySwiper2 > swiper-slide.swiper-slide-visible {
                opacity: 0.5!important;
            }
            .breakdance img{
            border-radius:8px!important;
            }
        </style>';

    // Return shortcode content with inline CSS styles
    return '
    <div>
        ' . $inline_css . '
        <swiper-container style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="mySwiper" thumbs-swiper=".mySwiper2" loop="true" space-between="10" navigation="true" autoplay="true" loop="true" speed="500" delay="1000">
            ' . $image_urls . '
        </swiper-container>
        <swiper-container class="mySwiper2" loop="true" space-between="10" slides-per-view="4" free-mode="true" watch-slides-progress="true">
            ' . $image_urls . '
        </swiper-container>
    </div>';
}
add_shortcode('custom_swiper', 'add_custom_swiper_shortcode');

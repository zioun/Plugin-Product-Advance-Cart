
<?php

if (! defined('ABSPATH')) {
    exit;
}

class Product_Cart_Loop_Widget extends \Elementor\Widget_Base
{

    public function get_name(): string
    {
        return 'product-cart-loop';
    }

    public function get_title(): string
    {
        return 'Product Loop';
    }

    public function get_icon(): string
    {
        return 'eicon-products';
    }

    public function get_categories(): array
    {
        return array('general');
    }

    public function get_style_depends(): array
    {
        return array('product-cart-loop');
    }

    public function get_script_depends(): array
    {
        return array('product-cart-loop');
    }

    protected function register_controls(): void
    {

        $this->start_controls_section(
            'products_section',
            array(
                'label' => 'WooCommerce Products',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'product_count',
            array(
                'label'   => 'Number of Products',
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min'     => 1,
                'max'     => 100,
            )
        );

        $this->add_control(
            'columns',
            array(
                'label'   => 'Columns',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'options' => array(
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns',
                ),
            )
        );

        $categories = array('' => 'All Categories');

        if (taxonomy_exists('product_cat')) {
            $terms = get_terms(
                array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                )
            );

            if (! is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[(string) $term->term_id] = $term->name;
                }
            }
        }

        $this->add_control(
            'product_category',
            array(
                'label'   => 'Product Category',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $categories,
            )
        );

        $this->add_control(
            'orderby',
            array(
                'label'   => 'Order By',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => array(
                    'date'       => 'Latest Products',
                    'title'      => 'Product Name',
                    'menu_order' => 'Menu Order',
                    'rand'       => 'Random',
                    'price'      => 'Price',
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'   => 'Order',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => array(
                    'ASC'  => 'Ascending',
                    'DESC' => 'Descending',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            array(
                'label' => 'Card Style',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_background',
            array(
                'label'     => 'Card Background',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .pc-product-card' =>
                    'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_radius',
            array(
                'label'      => 'Card Border Radius',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 40,
                    ),
                ),
                'default' => array(
                    'size' => 10,
                    'unit' => 'px',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .pc-product-card' =>
                    'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render(): void
    {

        if (! class_exists('WooCommerce')) {
            echo '<p>Please install and activate WooCommerce.</p>';
            return;
        }

        $settings = $this->get_settings_for_display();

        $count = max(
            1,
            min(100, absint($settings['product_count'] ?? 8))
        );

        $columns = max(
            1,
            min(6, absint($settings['columns'] ?? 4))
        );

        $orderby = $settings['orderby'] ?? 'date';

        if (
            ! in_array(
                $orderby,
                array('date', 'title', 'menu_order', 'rand', 'price'),
                true
            )
        ) {
            $orderby = 'date';
        }

        $order = ($settings['order'] ?? 'DESC') === 'ASC'
            ? 'ASC'
            : 'DESC';

        $args = array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => $count,
            'ignore_sticky_posts' => true,
            'orderby'             => $orderby === 'price'
                ? 'meta_value_num'
                : $orderby,
            'order'               => $order,
            'meta_query'          => array(
                array(
                    'key'     => '_stock_status',
                    'value'   => 'instock',
                    'compare' => '=',
                ),
            ),
        );

        if ($orderby === 'price') {
            $args['meta_key'] = '_price';
        }

        $category_id = absint(
            $settings['product_category'] ?? 0
        );

        if ($category_id) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                ),
            );
        }

        $query = new \WP_Query($args);

        if (! $query->have_posts()) {
            echo '<p>No products found.</p>';
            wp_reset_postdata();
            return;
        }

        echo '<div class="pc-product-grid" style="--pc-columns:' .
            esc_attr((string) $columns) . ';">';

        $had_product = array_key_exists('product', $GLOBALS);
        $old_product = $GLOBALS['product'] ?? null;

        while ($query->have_posts()) {
            $query->the_post();

            $product = wc_get_product(get_the_ID());

            if (! $product || ! $product->is_visible()) {
                continue;
            }

            $GLOBALS['product'] = $product;

            $image_ids = array();

            if ($product->get_image_id()) {
                $image_ids[] = $product->get_image_id();
            }

            $image_ids = array_merge(
                $image_ids,
                $product->get_gallery_image_ids()
            );

            $image_ids = array_values(
                array_unique(
                    array_filter(
                        array_map('absint', $image_ids)
                    )
                )
            );

            echo '<article class="pc-product-card">';

            echo '<div class="pc-product-gallery">';

            if ($image_ids) {

                echo '<div class="pc-gallery-viewport">';
                echo '<div class="pc-gallery-track">';

                foreach ($image_ids as $index => $image_id) {
                    $full_url = wp_get_attachment_image_url(
                        $image_id,
                        'full'
                    );

                    if (! $full_url) {
                        continue;
                    }

                    $alt = get_post_meta(
                        $image_id,
                        '_wp_attachment_image_alt',
                        true
                    );

                    echo '<button type="button" class="pc-gallery-slide"';
                    echo ' data-full-src="' . esc_url($full_url) . '"';
                    echo ' aria-label="' . esc_attr(
                        sprintf(
                            'View image %1$d of %2$d for %3$s',
                            $index + 1,
                            count($image_ids),
                            $product->get_name()
                        )
                    ) . '">';

                    echo wp_get_attachment_image(
                        $image_id,
                        'woocommerce_thumbnail',
                        false,
                        array(
                            'class'    => 'pc-gallery-image',
                            'alt'      => $alt ?: $product->get_name(),
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                        )
                    );

                    echo '</button>';
                }

                echo '</div></div>';

                if (count($image_ids) > 1) {
                    echo '<button type="button" class="pc-gallery-arrow pc-gallery-prev" aria-label="Previous image">&#10094;</button>';
                    echo '<button type="button" class="pc-gallery-arrow pc-gallery-next" aria-label="Next image">&#10095;</button>';

                    echo '<div class="pc-gallery-counter" aria-live="polite">';
                    echo '<span class="pc-gallery-current">1</span> / ';
                    echo '<span class="pc-gallery-total">' .
                        esc_html((string) count($image_ids)) .
                        '</span>';
                    echo '</div>';
                }
            } else {
                echo '<div class="pc-gallery-empty">No product image available</div>';
            }

            echo '</div>';

            echo '<div class="pc-product-content">';

            echo '<h3 class="pc-product-name">';
            echo esc_html($product->get_name());
            echo '</h3>';

            echo '<div class="pc-product-price">';
            echo wp_kses_post($product->get_price_html());
            echo '</div>';

            echo '<div class="pc-product-actions">';
            woocommerce_template_loop_add_to_cart();
            echo '</div>';

            echo '</div></article>';
        }

        echo '</div>';

        wp_reset_postdata();

        if ($had_product) {
            $GLOBALS['product'] = $old_product;
        } else {
            unset($GLOBALS['product']);
        }
    }
}

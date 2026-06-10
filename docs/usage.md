# Administrator guide

A step-by-step walkthrough for using Fancy Product Page.

## Concept

A "fancy product page" is an ordinary WordPress **Page** (or Post) that you associate with a WooCommerce **product**. Once associated:

- links to the product across your site point at your page,
- search engines still see a product (structured data is injected),
- customers can still add the product to the cart from your page.

The product continues to exist in WooCommerce as normal — you are only replacing its **front-end presentation**.

## 1. Build the page

Create a normal Page (or Post) and design it however you like — the block editor or any page builder works, since it is just standard WordPress content. Publish it.

To add a buy button, use the shortcode (see [shortcode.md](shortcode.md)):

```text
[add_to_cart_btn id="123"]
```

or by SKU:

```text
[add_to_cart_btn sku="WIDGET-BLUE" qty="1"]
```

## 2. Associate the page with the product

1. Edit the WooCommerce **product**.
2. Find the **Fancy Product Page** meta box.
3. In the dropdown, choose the page you published. The default option, *Standard WooCommerce product page*, leaves the product using WooCommerce's normal template.
4. **Update** the product.

That's it. Links generated for the product (menus, related products, shop loop) now resolve to your page, and the page emits product structured data for SEO.

## 3. Verify

- Visit the shop or a place that links to the product — the link should land on your page.
- View the page source and confirm a `application/ld+json` block describing the product is present.
- Use the add-to-cart button on the page and confirm the product reaches the cart.

## Choosing which content types are allowed

By default you can pick **Pages** and **Posts**. Developers can broaden or narrow this with the `fancy_product_page_post_types` filter — see [hooks.md](hooks.md).

## Notes and current limitations

- The original `/product/...` URL still resolves to WooCommerce's template. An optional 301 redirect to the fancy page exists in the code but is **not enabled** in this release.
- The plugin has no settings page; configuration is per-product via the meta box.
